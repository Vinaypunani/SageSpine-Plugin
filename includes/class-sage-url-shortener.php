<?php

if (!defined('ABSPATH')) {
    exit;
}

class Sage_URL_Shortener {

    const API_NAMESPACE = 'url-shortener/v1';
    const SHORT_PREFIX = 'contact'; // The URL segment triggers the redirect
    const TABLE_NAME = 'sage_short_urls';

    public function __construct() {
        // Register the API endpoint
        add_action('rest_api_init', [$this, 'register_routes']);

        // Listen for the redirect at the earliest possible hook
        add_action('init', [$this, 'handle_redirect'], 1);

        // Auto-heal table if missing (Check actual table presence)
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            self::activate();
        }
    }

    /**
     * Create the database table on plugin activation.
     */
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            original_url text NOT NULL,
            short_code varchar(10) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            clicks int(11) DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY short_code (short_code)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Register the REST API route for encoding URLs.
     */
    public function register_routes() {
        register_rest_route(self::API_NAMESPACE, '/encode', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_encode_request'],
            'permission_callback' => '__return_true', // Public endpoint
            'args' => [
                'url' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return filter_var($param, FILTER_VALIDATE_URL);
                    }
                ]
            ]
        ]);
    }

    /**
     * Handle the encode API request.
     */
    public function handle_encode_request($request) {
        $params = $request->get_json_params();
        $long_url = isset($params['url']) ? trim($params['url']) : '';

        if (empty($long_url) || !filter_var($long_url, FILTER_VALIDATE_URL)) {
             return new WP_Error('invalid_url', 'Invalid URL provided', ['status' => 400]);
        }

        // Security: Prevent loop redirects to ourselves
        $site_url = home_url();
        if (strpos($long_url, $site_url . '/' . self::SHORT_PREFIX . '/') !== false) {
             return new WP_Error('invalid_url', 'Cannot shorten an already shortened URL from this domain.', ['status' => 400]);
        }

        $short_code = $this->shorten_url($long_url);
        
        if (is_wp_error($short_code)) {
            return $short_code;
        }

        $short_url = home_url('/' . self::SHORT_PREFIX . '/' . $short_code);

        return rest_ensure_response([
            'original_url' => $long_url,
            'short_code' => $short_code,
            'short_url' => $short_url
        ]);
    }

    /**
     * Shorten URL and store in DB.
     */
    /**
     * Shorten URL and store in DB using a random slug.
     */
    private function shorten_url($url) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        // Check if URL already exists
        $existing = $wpdb->get_row($wpdb->prepare("SELECT short_code FROM $table_name WHERE original_url = %s LIMIT 1", $url));
        if ($existing && !empty($existing->short_code)) {
            return $existing->short_code;
        }

        $max_retries = 5;
        $attempt = 0;
        
        do {
            $slug = $this->generate_random_slug();
            
            // Try to insert directly. 
            // If it fails due to UNIQUE constraint, $wpdb->insert returns false.
            // We use the suppressor @ to avoid PHP warnings if WPDB is set to show errors.
            $result = @$wpdb->insert($table_name, [
                'original_url' => $url,
                'short_code' => $slug,
                'created_at' => current_time('mysql')
            ]);

            if ($result !== false) {
                // Success!
                return $slug;
            }

            // Check if failure was due to duplicate short_code
            if (strpos($wpdb->last_error, 'Duplicate entry') !== false && strpos($wpdb->last_error, 'short_code') !== false) {
                // Collision detected, retry...
                $attempt++;
                continue;
            } else {
                // Some other DB error happened
                return new WP_Error('db_error', 'Database error: ' . $wpdb->last_error, ['status' => 500]);
            }

        } while ($attempt < $max_retries);

        return new WP_Error('collision_error', 'Failed to generate unique code after multiple attempts.', ['status' => 500]);
    }

    private function generate_random_slug($length = 6) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $str = '';
        $max_index = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $max_index)];
        }
        return $str;
    }

    /**
     * Inspect the request path to see if it's a short URL, and redirect if so.
     */
    public function handle_redirect() {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, '/');
        
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH), '/');
        if ($site_path) {
            if (strpos($path, $site_path) === 0) {
                $path = substr($path, strlen($site_path));
                $path = trim($path, '/');
            }
        }
        
        $parts = explode('/', $path);

        if (count($parts) >= 2 && $parts[0] === self::SHORT_PREFIX) {
            $code = $parts[1];
            
            if (empty($code)) {
                return;
            }

            $original_url = $this->get_original_url($code);

            if ($original_url && filter_var($original_url, FILTER_VALIDATE_URL)) {
                if (!$this->is_safe_protocol($original_url)) {
                    wp_die('Invalid protocol.', 'Error', ['response' => 400]);
                }

                wp_redirect($original_url, 301);
                exit; // Stop further WP execution
            } else {
                 wp_die('Invalid link.', 'Error', ['response' => 404]);
            }
        }
    }

    private function get_original_url($code) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        // 1. Primary Lookup: By Short Code from DB
        $row = $wpdb->get_row($wpdb->prepare("SELECT original_url, id FROM $table_name WHERE short_code = %s", $code));
        
        if ($row) {
             $wpdb->query($wpdb->prepare("UPDATE $table_name SET clicks = clicks + 1 WHERE id = %d", $row->id));
             return $row->original_url;
        }

        // 2. Legacy Fallback: Decode ID
        // Note: Base62 decode might return a number even for a non-numeric string if not careful, 
        // but our strict base62_decode implementation returns a number. 
        // If we want to support old ID-based links that somehow didn't have short_code set, we can keep this.
        // However, since we now prioritize short_code, and the old code SAVED short_code, this is likely redundant 
        // unless the short_code column was NULL for some reason.
        $id = $this->base62_decode($code);
        if ($id) {
            $row_by_id = $wpdb->get_row($wpdb->prepare("SELECT original_url, clicks FROM $table_name WHERE id = %d", $id));
            if ($row_by_id) {
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET clicks = clicks + 1 WHERE id = %d", $id));
                return $row_by_id->original_url;
            }
        }

        // 3. Fallback to Legacy Stateless
        return $this->decode_legacy($code);
    }

    /**
     * Base62 Encoding for ID
     */
    private function base62_encode($num) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = 62;
        $str = '';
        do {
            $str = $chars[$num % $base] . $str;
            $num = intval(($num - ($num % $base)) / $base); // integer division
        } while ($num > 0);
        return $str;
    }

    private function base62_decode($str) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = 62;
        $len = strlen($str);
        $num = 0;
        
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($chars, $str[$i]);
            if ($pos === false) return false;
            $num = $num * $base + $pos;
        }
        return $num;
    }

    // --- Legacy Stateless Logic (Renamed) ---

    // Dictionary for compression
    private $dictionary = [
        'https://forms.zohopublic.com/sagespinepainandnervecenter1/form/SageSpineNewPatientInfoFormCRM/formperma/' => "\x10",
        'nRceJZvS4wJ27DvdTsNiAQTJ5LRlN6kGD7e-895M5lY' => "\x11",
        '?em=' => "\x12",
        '&sa=' => "\x13",
        '&dob=' => "\x14",
        '&ct=' => "\x15",
        '&st=' => "\x16",
        '&zip=' => "\x17",
        '&ininfo=' => "\x18",
        '&pp=' => "\x19",
        '&rp=' => "\x1A",
        '&lang=' => "\x1B",
        '&sex=' => "\x1C",
        '&ec=' => "\x1D",
        '&rc=' => "\x1E",
        'Not%20Hispanic%20or%20Latino%20%28White%29' => "\x1F", 
        'Not Hispanic or Latino (White)' => "\x07",
        'English' => "\x7F",
        'Medicare' => "\x01", 
        'White' => "\x02",
        'Male' => "\x03",
        'Female' => "\x04",
        'self' => "\x06",
        'Test%20' => "\x05",
    ];

    const ZOHO_BASE_URL = 'https://forms.zohopublic.com/sagespinepainandnervecenter1/form/SageSpineNewPatientInfoFormCRM/formperma/nRceJZvS4wJ27DvdTsNiAQTJ5LRlN6kGD7e-895M5lY';

    private function decode_legacy($string) {
        // Check Strategy 1: strict schema (Prefix 'Z')
        if (isset($string[0]) && $string[0] === 'Z') {
            $payload = substr($string, 1);
            // Decode Base64
            $remainder = strlen($payload) % 4;
            if ($remainder) $payload .= str_repeat('=', 4 - $remainder);
            $payload = strtr($payload, '-_', '+/');
            $compressed = base64_decode($payload);
            
            $binary = @gzinflate($compressed);
            if ($binary !== false) {
                 return $this->unpack_zoho($binary);
            }
            return false;
        }

        // Strategy 2: Dictionary
        $remainder = strlen($string) % 4;
        if ($remainder) {
            $string .= str_repeat('=', 4 - $remainder);
        }
        $string = strtr($string, '-_', '+/');
        $decoded = base64_decode($string);
        
        $inflated = @gzinflate($decoded);
        if ($inflated === false) {
            return $decoded; // Might be just plain base64?
        }
        
        $reverse_dict = array_flip($this->dictionary);
        return str_replace(array_keys($reverse_dict), array_values($reverse_dict), $inflated);
    }

    private function unpack_zoho($binary) {
        $offset = 0;
        $ver = ord($binary[$offset++]); // Version
        
        if ($ver !== 1) return false;

        $fields = ['em', 'sa', 'ct', 'st', 'zip'];
        $q = [];
        
        foreach ($fields as $f) {
            $pos = strpos($binary, "\0", $offset);
            $q[$f] = substr($binary, $offset, $pos - $offset);
            $offset = $pos + 1;
        }

        // DOB
        $y = ord($binary[$offset++]) + 1900;
        $m = ord($binary[$offset++]);
        $d = ord($binary[$offset++]);
        
        if ($m > 0) {
            $dt = DateTime::createFromFormat('Y-n-j', "$y-$m-$d");
            $q['dob'] = $dt ? $dt->format('M d, Y') : ''; 
        } else {
            $q['dob'] = '';
        }

        // Phone
        $phone = '';
        for ($i=0; $i<5; $i++) {
            $byte = ord($binary[$offset++]);
            $phone .= ($byte >> 4) . ($byte & 0x0F);
        }
        $q['pp'] = $phone;

        // Enums
        $enums = [
            'ininfo' => ['Medicare', 'Commercial', 'Worker Comp', 'Personal Injury', 'Self Pay'],
            'rp' => ['self', 'other'],
            'lang' => ['English', 'Spanish'],
            'sex' => ['Male', 'Female'],
            'ec' => ['Not Hispanic or Latino (White)', 'Hispanic', 'Latino'],
            'rc' => ['White', 'Black', 'Asian', 'Other']
        ];
        
        foreach ($enums as $key => $values) {
            $idx = ord($binary[$offset++]);
            $q[$key] = ($idx !== 255 && isset($values[$idx])) ? $values[$idx] : '';
        }

        return self::ZOHO_BASE_URL . '?' . http_build_query($q);
    }

    private function is_safe_protocol($url) {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return in_array(strtolower($scheme), ['http', 'https']);
    }
}
