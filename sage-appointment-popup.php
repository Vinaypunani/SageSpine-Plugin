<?php
/**
 * Plugin Name: Sage Spine Appointment Popup
 * Description: A portable popup form to look up appointments via Zoho CRM.
 * Version: 1.1
 * Author: Sage Spine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Sage_Appointment_Popup {
    
    private $zoho_config = [];

    public function __construct() {
        // Init URL Shortener
        require_once plugin_dir_path(__FILE__) . 'includes/class-sage-url-shortener.php';
        new Sage_URL_Shortener();
        
        // Register Activation Hook
        register_activation_hook(__FILE__, ['Sage_URL_Shortener', 'activate']);

        // Load configuration from DB
        $this->load_config();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_modal_html']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        
        // Admin Settings
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Shortcodes
        add_shortcode('sage_booking_design', [$this, 'render_booking_design']);
        add_shortcode('sage_book_appointment', [$this, 'render_book_appointment']);
        
        // Sample Page Routes
        add_action('init', [$this, 'register_rewrite_rules']);
        add_filter('query_vars', [$this, 'register_query_vars']);
        add_action('template_redirect', [$this, 'handle_semple_page_render']);
    }

    public function register_rewrite_rules() {
        add_rewrite_rule('^Semple/([^/]+)/?', 'index.php?semple_contact_id=$matches[1]', 'top');
    }

    public function register_query_vars($vars) {
        $vars[] = 'semple_contact_id';
        return $vars;
    }

    public function handle_semple_page_render() {
        $contact_id = get_query_var('semple_contact_id');
        if ($contact_id) {
            $this->render_semple_page($contact_id);
            exit;
        }
    }

    private function load_config() {
        $this->zoho_config = [
            'clientId' => get_option('sage_zoho_client_id', '1000.3WWAF5HMKH367DXV46S9W9YPJ897QP'),
            'clientSecret' => get_option('sage_zoho_client_secret', 'f301aa35e47e732aa1af69a1869cc0e08840d68556'),
            'refreshToken' => get_option('sage_zoho_refresh_token', '1000.3987be8bfd1692d6baacd915f4429fe0.4911ea29cd3e04a9d15af8cd850c6a27'),
            'apiDomain' => 'https://www.zohoapis.com',
            'authDomain' => 'https://accounts.zoho.com'
        ];

        $this->zoho_booking_config = [
            'clientId' => get_option('sage_booking_client_id', ''),
            'clientSecret' => get_option('sage_booking_client_secret', ''),
            'refreshToken' => get_option('sage_booking_refresh_token', ''),
            'apiDomain' => 'https://www.zohoapis.com',
            'authDomain' => 'https://accounts.zoho.com'
        ];
    }

    public function add_admin_menu() {
        add_menu_page(
            'Sage Spine Settings',
            'Sage Spine',
            'manage_options',
            'sage-spine-settings',
            [$this, 'render_settings_page'],
            'dashicons-calendar-alt',
            60
        );
    }

    public function register_settings() {
        // CRM Settings Group
        register_setting('sage_spine_options', 'sage_zoho_client_id');
        register_setting('sage_spine_options', 'sage_zoho_client_secret');
        register_setting('sage_spine_options', 'sage_zoho_refresh_token');
        register_setting('sage_spine_options', 'sage_booking_url');
        
        // Booking API Settings Group (New Group)
        // Booking API Settings Group (New Group)
        register_setting('sage_booking_options', 'sage_booking_client_id');
        register_setting('sage_booking_options', 'sage_clinic_location'); // Clinic Location Setting
        register_setting('sage_booking_options', 'sage_booking_client_secret');
        register_setting('sage_booking_options', 'sage_booking_refresh_token');
    }

    public function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'crm_settings';
        $option_group = ($active_tab == 'booking_settings') ? 'sage_booking_options' : 'sage_spine_options';
        ?>
        <div class="wrap">
            <h1>Sage Spine Settings</h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=sage-spine-settings&tab=crm_settings" class="nav-tab <?php echo $active_tab == 'crm_settings' ? 'nav-tab-active' : ''; ?>">CRM Settings</a>
                <a href="?page=sage-spine-settings&tab=booking_settings" class="nav-tab <?php echo $active_tab == 'booking_settings' ? 'nav-tab-active' : ''; ?>">Booking Settings</a>
                <a href="?page=sage-spine-settings&tab=api_docs" class="nav-tab <?php echo $active_tab == 'api_docs' ? 'nav-tab-active' : ''; ?>">API Docs</a>
            </h2>

            <?php if ($active_tab == 'api_docs'): ?>
                <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
                    <h2>Stateless URL Shortener API</h2>
                    <p>This plugin includes a stateless URL shortener that encodes the entire URL into the path so no database storage is required.</p>
                    
                    <h3>Endpoint</h3>
                    <p><code>POST <?php echo esc_url(rest_url('url-shortener/v1/encode')); ?></code></p>
                    
                    <h3>Request Format</h3>
                    <pre style="background: #f0f0f1; padding: 15px; border-radius: 4px;">{
    "url": "https://example.com/login?param1=value1"
}</pre>
                    
                    <h3>Response Format</h3>
                    <pre style="background: #f0f0f1; padding: 15px; border-radius: 4px;">{
    "original_url": "https://example.com/login?param1=value1",
    "short_code": "...",
    "short_url": "<?php echo esc_url(home_url('/s/')); ?>..."
}</pre>
                </div>
            <?php else: ?>
                <form method="post" action="options.php">
                    <?php settings_fields($option_group); ?>
                    <?php do_settings_sections($option_group); ?>
    
                    <?php if ($active_tab == 'crm_settings'): ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Zoho CRM Client ID</th>
                                <td><input type="text" name="sage_zoho_client_id" value="<?php echo esc_attr(get_option('sage_zoho_client_id', '1000.3WWAF5HMKH367DXV46S9W9YPJ897QP')); ?>" class="regular-text" /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Zoho CRM Client Secret</th>
                                <td><input type="password" name="sage_zoho_client_secret" value="<?php echo esc_attr(get_option('sage_zoho_client_secret')); ?>" class="regular-text" /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Zoho CRM Refresh Token</th>
                                <td><textarea name="sage_zoho_refresh_token" class="large-text" rows="3"><?php echo esc_textarea(get_option('sage_zoho_refresh_token', '1000.3987be8bfd1692d6baacd915f4429fe0.4911ea29cd3e04a9d15af8cd850c6a27')); ?></textarea></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Booking Page URL (Legacy)</th>
                                <td>
                                    <input type="text" name="sage_booking_url" value="<?php echo esc_attr(get_option('sage_booking_url', 'https://aclary-sagespine.zohobookings.com/#/sagespinepainandnervecenter')); ?>" class="regular-text" />
                                    <p class="description">The URL to open when "Book an Appointment" is clicked.</p>
                                </td>
                            </tr>
                        </table>
                    <?php elseif ($active_tab == 'booking_settings'): ?>
                        <table class="form-table">
                             <tr valign="top">
                                <th scope="row">Booking Client ID</th>
                                <td><input type="text" name="sage_booking_client_id" value="<?php echo esc_attr(get_option('sage_booking_client_id')); ?>" class="regular-text" /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Booking Client Secret</th>
                                <td><input type="password" name="sage_booking_client_secret" value="<?php echo esc_attr(get_option('sage_booking_client_secret')); ?>" class="regular-text" /></td>
                            </tr>
                            <tr valign="top">
                                <td><textarea name="sage_booking_refresh_token" class="large-text" rows="3"><?php echo esc_textarea(get_option('sage_booking_refresh_token')); ?></textarea></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Clinic Location (for Map)</th>
                                <td>
                                    <input type="text" name="sage_clinic_location" value="<?php echo esc_attr(get_option('sage_clinic_location', 'Sage Spine and Nerve Center')); ?>" class="regular-text" />
                                    <p class="description">Enter the full address or business name for Google Maps directions.</p>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>
    
                    <?php submit_button(); ?>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    public function enqueue_assets() {
        wp_enqueue_style('sage-popup-style', plugins_url('assets/css/style.css', __FILE__));
        wp_enqueue_script('sage-popup-script', plugins_url('assets/js/script.js', __FILE__), [], '1.3', true);

        $booking_url = get_option('sage_booking_url', 'https://aclary-sagespine.zohobookings.com/#/sagespinepainandnervecenter');
        $location = get_option('sage_clinic_location', 'Sage Spine and Nerve Center');

        wp_localize_script('sage-popup-script', 'sageSpineVars', [
            'api_url' => rest_url('sagespine/v1/search'),
            'otp_send_url' => rest_url('sagespine/v1/otp/send'),
            'otp_verify_url' => rest_url('sagespine/v1/otp/verify'),
            'token_send_url' => rest_url('sagespine/v1/token/send'),
            'token_verify_url' => rest_url('sagespine/v1/token/verify'),
            'nonce' => wp_create_nonce('wp_rest'),
            'nonce' => wp_create_nonce('wp_rest'),
            'booking_url' => $booking_url,
            'location' => $location, // Pass to JS
            'home_url' => home_url('/')
        ]);
    }

    public function render_modal_html() {
        ?>
        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>
        
        <div id="sage-popup-root" class="sage-isolate-scope">
            <div class="sage-modal-overlay" id="sageAppointmentModal">
                <!-- Modal Container: flex layout for split view -->
                <div class="sage-modal" style="padding: 0 !important; width: 800px !important; max-width: 90% !important; display: flex !important; overflow: hidden !important; border-radius: 12px !important;">
                    <span class="sage-modal-close" style="z-index: 10; color: #333 !important;">&times;</span>
                    
                    <!-- Split View Container (Always Visible) -->
                    <div id="sageSplitView" class="sage-responsive-split" style="display: flex !important; width: 100% !important; height: 100% !important;">
                        
                        <!-- Left Side (Green Brand Color) - Static -->
                        <div class="sage-split-left" style="flex: 1; background: #2E8B57 !important; color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; position: relative; text-align: center;">
                            <!-- Icon Circle -->
                            <div style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                                 <i data-lucide="calendar" style="width: 28px; height: 28px; stroke-width: 1.5;"></i>
                            </div>
                            
                            <h2 style="color: white !important; font-size: 28px !important; font-weight: 500 !important; line-height: 1.3 !important; margin-bottom: 20px !important; border: none !important;">We look forward<br>to seeing you.</h2>
                            
                            <!-- Background decoration (optional/implied) -->
                        </div>

                        <!-- Right Side (Dynamic Content) -->
                        <div class="sage-split-right" style="flex: 1.2; background: #fff; padding: 50px 40px; display: flex; flex-direction: column; justify-content: center; text-align: left; position: relative;">
                            
                            <!-- View 1: Initial Actions -->
                            <div id="sageInitialView" style="display: block;">
                                <div style="margin-bottom: 50px; text-align: center;">
                                    <img src="<?php echo plugins_url('assets/images/sage_spine_logo.png', __FILE__); ?>" alt="Sage Spine Logo" style="max-width: 65%; height: auto;">
                                </div>

                                
                                <div style="display: flex; flex-direction: column; gap: 15px;">
                                    <!-- Book an Appointment -> External -> sageBtnChoiceBook (Primary Green) -->
                                    <button id="sageBtnChoiceBook" class="sage-btn-submit" style="padding: 0px; width: 100% !important; background: #4f46e5 !important; background-color: #2E8B57 !important; color: white !important; border: none !important; border-radius: 10px !important; font-size: 15px !important;  cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-weight: 500 !important;">
                                        <i data-lucide="calendar-plus" style="margin-right: 12px; width: 20px;"></i>
                                        Book an Appointment
                                    </button>
                                    
                                    <!-- Reschedule Appointment -> Lookup -> sageBtnChoiceSchedule (Secondary Outline) -->
                                    <button id="sageBtnChoiceSchedule" class="sage-btn-submit" style="padding: 0px; width: 100% !important; background: #fff !important; color: #4b5563 !important; border: 1px solid #e5e7eb !important; border-radius: 10px !important; font-size: 15px !important;  cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-weight: 500 !important;">
                                        <i data-lucide="clock" style="margin-right: 12px; width: 20px;"></i>
                                        Reschedule Appointment
                                    </button>
                                </div>
                            </div>

                            <!-- View 2: Lookup Form (Initially Hidden) -->
                            <div id="sageLookupView" style="display: none; padding-top: 20px; text-align: center; flex-direction: column; align-items: center; justify-content: center;">
                                 <!-- Back Button for internal navigation (optional/hidden) -->
                                
                                <h2 style="font-size: 24px !important; font-weight: 700 !important; color: #1f2937 !important; margin-bottom: 25px !important; padding-bottom: 15px !important; border-bottom: 2px solid #1f2937 !important; display: inline-block !important;">Reschedule Appointment</h2>
                                
    <form id="sageAppointmentForm" style="width: 100%; margin: 0 auto;">
        <div class="sage-form-group" style="position: relative; margin-bottom: 20px;">
            <i data-lucide="user" class="sage-input-icon icon-user"></i>
            <input type="text" id="sageFirstName" class="sage-form-input" required placeholder=" " autocomplete="off">
            <label for="sageFirstName" class="sage-form-label">First Name</label>
        </div>
        <div class="sage-form-group" style="position: relative; margin-bottom: 20px;">
                <i data-lucide="user" class="sage-input-icon icon-user"></i>
            <input type="text" id="sageLastName" class="sage-form-input" required placeholder=" " autocomplete="off">
            <label for="sageLastName" class="sage-form-label">Last Name</label>
        </div>
        <div class="sage-form-group" style="position: relative; margin-bottom: 40px !important;">
                <i data-lucide="calendar" class="sage-input-icon icon-calendar"></i>
            <input type="date" id="sageDob" class="sage-form-input" required placeholder="dd-mm-yyyy">
            <label for="sageDob" class="sage-form-label">Date of Birth</label>
        </div>
        <button type="submit" class="sage-btn-submit" style="width: 100% !important; background: #2E8B57 !important; color: white !important; border: none !important; border-radius: 50px !important; font-size: 16px !important; font-weight: 600 !important;  cursor: pointer !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;">Lookup</button>
    </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Load FontAwesome and Initialize Lucide -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        </script>
        <?php
    }

    public function register_rest_routes() {
        register_rest_route('sagespine/v1', '/search', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_search_request'],
            'permission_callback' => '__return_true' // Publicly accessible for now
        ]);
        
        // Booking API Routes
        register_rest_route('sagespine/v1', '/appointment', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_get_appointment'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('sagespine/v1', '/services', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_get_services'],
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route('sagespine/v1', '/book', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_book_appointment'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('sagespine/v1', '/staffs', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_get_staffs'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('sagespine/v1', '/slots', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_get_slots'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('sagespine/v1', '/reschedule', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_reschedule_appointment'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('sagespine/v1', '/cancel', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_cancel_appointment'],
            'permission_callback' => '__return_true'
        ]);

        // Debug Route
        register_rest_route('sagespine/v1', '/debug', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_debug_request'],
            'permission_callback' => '__return_true'
        ]);

        // OTP Routes
        register_rest_route('sagespine/v1', '/otp/send', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_send_otp_request'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('sagespine/v1', '/otp/verify', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_verify_otp_request'],
            'permission_callback' => '__return_true'
        ]);

        // Secure Token Routes
        register_rest_route('sagespine/v1', '/token/send', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_send_token_request'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('sagespine/v1', '/token/verify', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_verify_token_request'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function handle_search_request($request) {
        nocache_headers();
        $params = $request->get_json_params();
        $firstName = $params['firstName'] ?? '';
        $lastName = $params['lastName'] ?? '';
        $dob = $params['dob'] ?? '';

        if (!$firstName || !$lastName || !$dob) {
            return new WP_Error('missing_params', 'Missing required fields', ['status' => 400]);
        }

        return $this->search_contact($firstName, $lastName, $dob);
    }
    
    public function handle_get_appointment($request) {
        nocache_headers(); // Force WP to not cache this response

        $booking_id = $request->get_param('booking_id');
        if (!$booking_id) return new WP_Error('missing_param', 'Missing booking_id', ['status' => 400]);

        $token = $this->get_booking_access_token();
        if (!$token) return new WP_Error('auth_error', 'Could not auth with Zoho Bookings', ['status' => 500]);

        // Add server-side timestamp to force fresh from Zoho
        $url = $this->zoho_booking_config['apiDomain'] . "/bookings/v1/json/getappointment?booking_id=" . $booking_id . "&_z=" . time();
        
        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => 'Zoho-oauthtoken ' . $token]
        ]);

        if (is_wp_error($response)) return $response;
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }

    public function handle_get_services($request) {
        $token = $this->get_booking_access_token();
        if (!$token || is_wp_error($token)) {
            error_log('Sage Booking: Auth Error in get_services - ' . (is_wp_error($token) ? $token->get_error_message() : 'No token'));
            return new WP_Error('auth_error', 'Could not auth with Zoho Bookings', ['status' => 500]);
        }
        
        $url = $this->zoho_booking_config['apiDomain'] . "/bookings/v1/json/services";
        
        // Log the request for debugging
        error_log('Sage Booking: Requesting Services from ' . $url);
        
        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => 'Zoho-oauthtoken ' . $token]
        ]);
        
        if (is_wp_error($response)) {
             error_log('Sage Booking: API Request Failed - ' . $response->get_error_message());
             return $response;
        }
        
        $raw_body = wp_remote_retrieve_body($response);
        error_log('Sage Booking: Raw API Response: ' . substr($raw_body, 0, 500) . '...'); // Log first 500 chars

        $body = json_decode($raw_body, true);
        
        // If decode fails, return raw to see what happened (or error)
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Sage Booking: JSON Decode Error - ' . json_last_error_msg());
            return ['error' => 'json_decode_error', 'raw_response' => $raw_body];
        }

        return $body;
    }

    public function handle_get_staffs($request) {
        $token = $this->get_booking_access_token();
        if (!$token || is_wp_error($token)) {
            return new WP_Error('auth_error', 'Could not auth with Zoho Bookings', ['status' => 500]);
        }
        
        $url = $this->zoho_booking_config['apiDomain'] . "/bookings/v1/json/staffs";
        
        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => 'Zoho-oauthtoken ' . $token]
        ]);
        
        if (is_wp_error($response)) return $response;
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }

    public function handle_book_appointment($request) {
        // Use get_body_params() to handle both JSON and FormData
        $params = $request->get_body_params();
        
        // If empty, try JSON params as fallback
        if (empty($params)) {
            $params = $request->get_json_params();
        }
        
        $service_id = $params['service_id'] ?? '';
        $staff_id = $params['staff_id'] ?? '';
        $from_time = $params['from_time'] ?? ''; // Format: dd-MMM-yyyy HH:mm:ss
        $customer_details_raw = $params['customer_details'] ?? [];
        $additional_fields_raw = $params['additional_fields'] ?? null;
        
        // customer_details and additional_fields might be JSON strings from FormData
        if (is_string($customer_details_raw)) {
            $customer_details = json_decode($customer_details_raw, true);
        } else {
            $customer_details = $customer_details_raw;
        }
        
        $additional_fields = null;
        if ($additional_fields_raw) {
            if (is_string($additional_fields_raw)) {
                $additional_fields = json_decode($additional_fields_raw, true);
            } else {
                $additional_fields = $additional_fields_raw;
            }
        }
        
        if (!$service_id || !$staff_id || !$from_time || empty($customer_details)) {
             return new WP_Error('missing_params', 'Missing required booking parameters', ['status' => 400]);
        }
        
        $token = $this->get_booking_access_token();
        if (!$token) return new WP_Error('auth_error', 'Could not auth with Zoho Bookings', ['status' => 500]);
        
        // Correct endpoint for creating appointment is /appointment (POST)
        $url = $this->zoho_booking_config['apiDomain'] . "/bookings/v1/json/appointment";
        
        $body_args = [
            'service_id' => $service_id,
            'staff_id' => $staff_id,
            'from_time' => $from_time,
            'customer_details' => json_encode($customer_details)
        ];
        
        // Add additional_fields if provided
        if ($additional_fields) {
            $body_args['additional_fields'] = json_encode($additional_fields);
        }
        
        $response = wp_remote_post($url, [
            'headers' => ['Authorization' => 'Zoho-oauthtoken ' . $token],
            'body' => $body_args
        ]);
        
        if (is_wp_error($response)) return $response;
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }

    public function handle_get_slots($request) {
        $service_id = $request->get_param('service_id');
        $staff_id = $request->get_param('staff_id');
        $selected_date = $request->get_param('selected_date'); // Format: dd-MMM-yyyy

        if (!$service_id || !$staff_id || !$selected_date) {
            return new WP_Error('missing_params', 'Missing service_id, staff_id or selected_date', ['status' => 400]);
        }
        
        $token = $this->get_booking_access_token();
         if (!$token) return new WP_Error('auth_error', 'Could not auth with Zoho Bookings', ['status' => 500]);

        $url = $this->zoho_booking_config['apiDomain'] . "/bookings/v1/json/availableslots";
        $url = add_query_arg([
            'service_id' => $service_id,
            'staff_id' => $staff_id,
            'selected_date' => $selected_date
        ], $url);

        error_log('Sage Booking: Requesting Slots from ' . $url);

        $response = wp_remote_get($url, [
             'headers' => ['Authorization' => 'Zoho-oauthtoken ' . $token]
        ]);

        if (is_wp_error($response)) {
             error_log('Sage Booking: Slots API Failed - ' . $response->get_error_message());
             return $response;
        }

        $raw_body = wp_remote_retrieve_body($response);
        error_log('Sage Booking: Slots Raw Response: ' . substr($raw_body, 0, 500));

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }

    public function handle_reschedule_appointment($request) {
        $booking_id = $request->get_param('booking_id');
        $staff_id = $request->get_param('staff_id');
        $start_time = $request->get_param('start_time'); // Format: yyyy-MM-dd HH:mm:ss
        
        if (!$booking_id || !$staff_id || !$start_time) {
             return new WP_Error('missing_params', 'Missing booking_id, staff_id or start_time', ['status' => 400]);
        }

        $token = $this->get_booking_access_token();
        if (is_wp_error($token)) return $token;

        $url = $this->zoho_booking_config['apiDomain'] . "/bookings/v1/json/rescheduleappointment";

        $response = wp_remote_post($url, [
            'headers' => ['Authorization' => 'Zoho-oauthtoken ' . $token],
            'body' => [
                'booking_id' => $booking_id,
                'staff_id' => $staff_id,
                'start_time' => $start_time
            ]
        ]);

        if (is_wp_error($response)) return $response;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }

    public function handle_cancel_appointment($request) {
        $booking_id = $request->get_param('booking_id');
        // $reason = $request->get_param('reason'); // Captured but not used by API yet

        if (!$booking_id) {
             return new WP_Error('missing_params', 'Missing booking_id', ['status' => 400]);
        }

        $token = $this->get_booking_access_token();
        if (is_wp_error($token)) return $token;

        $url = $this->zoho_booking_config['apiDomain'] . "/bookings/v1/json/updateappointment";

        // Using form-data style body for updateappointment as per snippet
        $response = wp_remote_post($url, [
            'headers' => ['Authorization' => 'Zoho-oauthtoken ' . $token],
            'body' => [
                'booking_id' => $booking_id,
                'action' => 'cancel' 
            ]
        ]);

        if (is_wp_error($response)) return $response;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }

    public function handle_debug_request() {
        // Force reload config to ensure fresh options
        $this->load_config();
        
        $status = [
            'config' => [
                'clientId_exists' => !empty($this->zoho_booking_config['clientId']),
                'clientId_val' => substr($this->zoho_booking_config['clientId'], 0, 5) . '...',
                'clientSecret_exists' => !empty($this->zoho_booking_config['clientSecret']),
                'refreshToken_exists' => !empty($this->zoho_booking_config['refreshToken']),
                'authDomain' => $this->zoho_booking_config['authDomain']
            ],
            'token_test' => []
        ];
        
        $token = $this->get_booking_access_token();
        if (is_wp_error($token)) {
            $status['token_test']['success'] = false;
            $status['token_test']['error_code'] = $token->get_error_code();
            $status['token_test']['error_message'] = $token->get_error_message();
            $status['token_test']['error_data'] = $token->get_error_data();
        } else {
            $status['token_test']['success'] = true;
            $status['token_test']['token_preview'] = substr($token, 0, 10) . '...';
        }
        
        return $status;
    }

    public function handle_send_otp_request($request) {
        $params = $request->get_json_params();
        $contactId = $params['contact_id'] ?? '';

        if (!$contactId) {
            return new WP_Error('missing_params', 'Missing contact ID', ['status' => 400]);
        }

        // Get Contact to get Email
        $contact = $this->get_contact_by_id($contactId);
        if (is_wp_error($contact) || empty($contact['Email'])) {
             return new WP_Error('contact_error', 'Contact not found or no email', ['status' => 404]);
        }

        $email = $contact['Email'];
        $otp = rand(100000, 999999);
        
        // Save OTP in transient (10 mins)
        set_transient('sage_otp_' . $contactId, $otp, 10 * 60);

        // Send Email
        $subject = 'Your Verification Code - Sage Spine';
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
                .wrapper { padding: 40px 20px; }
                .container { max-width: 600px; width: 100%; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); box-sizing: border-box; }
                .header { text-align: center; border-bottom: 2px solid #2E8B57; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { color: #2E8B57; margin: 0; font-size: 24px; line-height: 1.2; }
                .content { text-align: center; color: #555; font-size: 16px; line-height: 1.6; }
                .otp-box { background: #f0fdf4; color: #2E8B57; font-size: 32px; font-weight: bold; padding: 15px 30px; border-radius: 8px; display: inline-block; margin: 20px 0; letter-spacing: 5px; border: 1px dashed #2E8B57; word-break: break-all; }
                .footer { margin-top: 30px; font-size: 12px; color: #999; text-align: center; }
                
                @media only screen and (max-width: 600px) {
                    .wrapper { padding: 20px 10px !important; }
                    .container { padding: 25px 15px !important; width: 100% !important; }
                    .header h1 { font-size: 20px !important; }
                    .otp-box { font-size: 24px !important; padding: 12px 20px !important; letter-spacing: 3px !important; }
                    .content { font-size: 14px !important; }
                }
            </style>
        </head>
        <body>
            <div class="wrapper">
                <div class="container">
                    <div class="header">
                        <h1>Sage Spine & Nerve Center</h1>
                    </div>
                    <div class="content">
                        <p>Hello,</p>
                        <p>We received a request to access your appointment details. Use the verification code below to confirm your identity.</p>
                        
                        <div class="otp-box">' . $otp . '</div>
                        
                        <p>This code will expire in 10 minutes.</p>
                        <p>If you did not request this, please ignore this email.</p>
                    </div>
                    <div class="footer">
                        &copy; ' . date("Y") . ' Sage Spine & Nerve Center. All rights reserved.
                    </div>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        // Log OTP for debugging (essential for local dev)
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_log.txt', date('[Y-m-d H:i:s] ') . "OTP Generated for $email (ID: $contactId): $otp\n", FILE_APPEND);

        // Add filters for From Address and Name
        add_filter('wp_mail_from', [$this, 'custom_wp_mail_from']);
        add_filter('wp_mail_from_name', [$this, 'custom_wp_mail_from_name']);

        $sent = wp_mail($email, $subject, $message, $headers);

        // Remove filters to avoid conflicts
        remove_filter('wp_mail_from', [$this, 'custom_wp_mail_from']);
        remove_filter('wp_mail_from_name', [$this, 'custom_wp_mail_from_name']);

        if (!$sent) {
             // For Development/Local: Log failure but return success + debug message so flow continues
             file_put_contents(plugin_dir_path(__FILE__) . 'debug_log.txt', date('[Y-m-d H:i:s] ') . "wp_mail failed. Assuming local environment. Proceeding with OTP check in logs.\n", FILE_APPEND);
             return ['success' => true, 'message' => 'OTP sent (simulated). Check debug log.', 'debug' => true];
        }

        return ['success' => true, 'message' => 'OTP sent successfully'];
    }

    public function handle_verify_otp_request($request) {
        $params = $request->get_json_params();
        $contactId = $params['contact_id'] ?? '';
        $otp = $params['otp'] ?? '';

        if (!$contactId || !$otp) {
            return new WP_Error('missing_params', 'Missing parameters', ['status' => 400]);
        }

        $storedOtp = get_transient('sage_otp_' . $contactId);

        if (!$storedOtp || $storedOtp != $otp) {
            return new WP_Error('invalid_otp', 'Invalid or expired OTP', ['status' => 400]);
        }

        // OTP Verified - Fetch Events
        delete_transient('sage_otp_' . $contactId);

        $accessToken = $this->get_access_token();
        $contact = $this->get_contact_by_id($contactId);
        
        if (is_wp_error($contact)) {
             return new WP_Error('contact_error', 'Error fetching contact details', ['status' => 500]);
        }

        $contact['related_events'] = $this->get_events_for_contact($contactId, $accessToken);

        return ['success' => true, 'data' => $contact];
    }

    public function handle_send_token_request($request) {
        $params = $request->get_json_params();
        $contactId = $params['contact_id'] ?? '';

        if (!$contactId) {
            return new WP_Error('missing_params', 'Missing contact ID', ['status' => 400]);
        }

        // Get Contact to get Email
        $contact = $this->get_contact_by_id($contactId);
        if (is_wp_error($contact) || empty($contact['Email'])) {
             return new WP_Error('contact_error', 'Contact not found or no email', ['status' => 404]);
        }

        $email = $contact['Email'];
        
        // Generate Secure Token
        $token = bin2hex(random_bytes(16)); // 32 chars
        
        // Save Token in transient (15 mins)
        set_transient('sage_token_' . $token, $contactId, 15 * 60);

        // Generate Link
        // We append sage_token to the referer or home_url
        $referer = $request->get_header('referer');
        if (!$referer) $referer = home_url('/');
        
        // Ensure we strip existing parameters if we want a clean slate, or just append
        // Ideally we want to land on the page that has the plugin.
        // For simplicity, let's use the referer which should be the page the user is on.
        
        $link = add_query_arg('sage_token', $token, $referer);

        // Send Email
        $subject = 'Your Appointment Access Link - Sage Spine';
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
                .wrapper { padding: 40px 20px; width: 100%; box-sizing: border-box; }
                .container { max-width: 600px; width: 100%; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); box-sizing: border-box; }
                .header { text-align: center; border-bottom: 2px solid #2E8B57; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { color: #2E8B57; margin: 0; font-size: 24px; line-height: 1.2; }
                .content { text-align: center; color: #555; font-size: 16px; line-height: 1.6; }
                .btn { display: inline-block; background-color: #2E8B57; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: bold; margin-top: 20px; margin-bottom: 20px; }
                .footer { margin-top: 30px; font-size: 12px; color: #999; text-align: center; }

                /* Responsiveness */
                @media screen and (max-width: 600px) {
                    .wrapper { padding: 20px 10px; }
                    .container { padding: 20px; width: 100% !important; }
                    .header h1 { font-size: 20px; }
                    .content { font-size: 14px; }
                    .btn { display: block; width: 100%; box-sizing: border-box; text-align: center; }
                }
            </style>
        </head>
        <body>
            <div class="wrapper">
                <div class="container">
                    <div class="header">
                        <h1>Sage Spine & Nerve Center</h1>
                    </div>
                    <div class="content">
                        <p>Hello,</p>
                        <p>Click the button below to view your upcoming and past appointments.</p>
                        
                        <a href="' . esc_url($link) . '" class="btn" style="color: #ffffff !important;">View My Appointments</a>
                        
                        <p>Or copy and paste this link into your browser:</p>
                        <p style="font-size: 12px; color: #999; word-break: break-all;">' . esc_url($link) . '</p>
                        
                        <p>This link will expire in 15 minutes.</p>
                    </div>
                    <div class="footer">
                        &copy; ' . date("Y") . ' Sage Spine & Nerve Center. All rights reserved.
                    </div>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        // Log Token for debugging
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_log.txt', date('[Y-m-d H:i:s] ') . "Token Generated for $email (ID: $contactId): $token\nLink: $link\n", FILE_APPEND);

        // Add filters for From Address and Name
        add_filter('wp_mail_from', [$this, 'custom_wp_mail_from']);
        add_filter('wp_mail_from_name', [$this, 'custom_wp_mail_from_name']);

        $sent = wp_mail($email, $subject, $message, $headers);

        // Remove filters
        remove_filter('wp_mail_from', [$this, 'custom_wp_mail_from']);
        remove_filter('wp_mail_from_name', [$this, 'custom_wp_mail_from_name']);

        if (!$sent) {
             file_put_contents(plugin_dir_path(__FILE__) . 'debug_log.txt', date('[Y-m-d H:i:s] ') . "wp_mail failed (Token flow). Assuming local environment.\n", FILE_APPEND);
             return ['success' => true, 'message' => 'Link sent (simulated). Check debug log.', 'debug' => true];
        }

        return ['success' => true, 'message' => 'Link sent successfully'];
    }

    public function handle_verify_token_request($request) {
        $params = $request->get_json_params();
        $token = $params['token'] ?? '';

        if (!$token) {
            return new WP_Error('missing_params', 'Missing token', ['status' => 400]);
        }

        $contactId = get_transient('sage_token_' . $token);

        if (!$contactId) {
            return new WP_Error('invalid_token', 'Invalid or expired link', ['status' => 400]);
        }

        // Token Verified - Fetch Events
        // delete_transient('sage_token_' . $token); // Keep token valid until expiry (15 mins)

        $accessToken = $this->get_access_token();
        $contact = $this->get_contact_by_id($contactId);
        
        if (is_wp_error($contact)) {
             return new WP_Error('contact_error', 'Error fetching contact details', ['status' => 500]);
        }

        $contact['related_events'] = $this->get_events_for_contact($contactId, $accessToken);

        return ['success' => true, 'data' => $contact];
    }

    private function mask_email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        $parts = explode('@', $email);
        $local = $parts[0];
        $domain = $parts[1];
        
        if (strlen($local) <= 1) {
            return '*' . '@' . $domain;
        }

        $first = $local[0];
        return $first . '***@' . $domain;
    }

    private function get_booking_access_token() {
        // Check for cached token first
        $cached_token = get_transient('sage_booking_access_token');
        if ($cached_token) {
            return $cached_token;
        }

        if (empty($this->zoho_booking_config['clientId']) || empty($this->zoho_booking_config['clientSecret']) || empty($this->zoho_booking_config['refreshToken'])) {
            return new WP_Error('config_missing', 'Zoho Booking credentials are not configured in settings.', ['status' => 500]);
        }

        $url = $this->zoho_booking_config['authDomain'] . "/oauth/v2/token";
        
        // Optimize: Use POST args properly for wp_remote_post
        $body = [
            'refresh_token' => $this->zoho_booking_config['refreshToken'],
            'client_id' => $this->zoho_booking_config['clientId'],
            'client_secret' => $this->zoho_booking_config['clientSecret'],
            'grant_type' => 'refresh_token'
        ];

        $response = wp_remote_post($url, ['body' => $body]);
        
        if (is_wp_error($response)) {
            return new WP_Error('request_failed', 'Auth Request Failed: ' . $response->get_error_message(), ['status' => 500]);
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($data['access_token'])) {
            // Cache the token for 55 minutes (3300 seconds)
            set_transient('sage_booking_access_token', $data['access_token'], 3300);
            return $data['access_token'];
        }
        
        $error_msg = isset($data['error']) ? $data['error'] : 'Unknown Auth Error';
        return new WP_Error('zoho_auth_error', 'Zoho Auth Failed: ' . $error_msg . ' - ' . ($data['error_description'] ?? ''), ['status' => 500, 'error_data' => $data]);
    }

    private function get_access_token() {
        // Check for cached token first
        $cached_token = get_transient('sage_crm_access_token');
        if ($cached_token) {
            return $cached_token;
        }

        $url = $this->zoho_config['authDomain'] . "/oauth/v2/token";
        
        $body = [
            'refresh_token' => $this->zoho_config['refreshToken'],
            'client_id' => $this->zoho_config['clientId'],
            'client_secret' => $this->zoho_config['clientSecret'],
            'grant_type' => 'refresh_token'
        ];

        $response = wp_remote_post($url, [
            'body' => $body
        ]);
        
        if (is_wp_error($response)) {
            error_log('Zoho Auth Error: ' . $response->get_error_message());
            return null;
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (isset($data['access_token'])) {
            // Cache the token for 55 minutes (3300 seconds)
            set_transient('sage_crm_access_token', $data['access_token'], 3300);
            return $data['access_token'];
        }
        
        error_log('Zoho Auth Error Body: ' . $response_body);
        return null;
    }

    private function get_events_for_contact($contactId, $accessToken) {
        $url = $this->zoho_config['apiDomain'] . "/crm/v2/Contacts/" . $contactId . "/Invited_Events";
        
        // Add cache buster
        $url = add_query_arg('_z', time(), $url);
        
        $args = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
             error_log('Error fetching events: ' . $response->get_error_message());
             return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['Invited_Events'])) {
            return $data['Invited_Events'];
        }
        
        if (isset($data['data'])) {
            return $data['data'];
        }

        return [];
    }

    private function search_contact($firstName, $lastName, $dob) {
        $accessToken = $this->get_access_token();
        if (!$accessToken) {
            return new WP_Error('auth_error', 'Failed to generate access token', ['status' => 500]);
        }

        // Format: ((Last_Name:equals:Value)and(First_Name:equals:Value)and(Date_of_Birth:equals:Value))
        // Important: Zoho requires correct encoding of the parenthesis and criteria.
        $criteria = "((Last_Name:equals:" . $lastName . ")and(First_Name:equals:" . $firstName . ")and(Date_of_Birth:equals:" . $dob . "))";
        
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_log.txt', date('[Y-m-d H:i:s] ') . "Search params: First=$firstName, Last=$lastName, DOB=$dob\nCriteria: $criteria\n", FILE_APPEND);
        
        // Use rawurlencode but wait, Zoho sometimes accepts specific format. 
        // Best approach for Zoho CRM API v2 search criteria:
        // criteria=((Key:Condition:Value))
        
        $url = $this->zoho_config['apiDomain'] . "/crm/v2/Contacts/search";
        $url = add_query_arg('criteria', $criteria, $url);

        // Add cache buster
        $url = add_query_arg('_z', time(), $url);
        
        $args = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', $response->get_error_message(), ['status' => 500]);
        }
        
        $code = wp_remote_retrieve_response_code($response);
        if ($code === 204) {
             return ['data' => [], 'message' => 'No content found'];
        }

        $body = wp_remote_retrieve_body($response);
        
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_log.txt', date('[Y-m-d H:i:s] ') . "Response: " . substr($body, 0, 1000) . "\n\n", FILE_APPEND);
        
        $responseKey = json_decode($body, true);

        // Check if Zoho returned an API error inside the 200/other response
        if (isset($responseKey['code']) && $responseKey['code'] === 'INVALID_TOKEN') {
             return new WP_Error('zoho_api_error', 'Invalid Token', ['status' => 500]);
        }

        if (isset($responseKey['data']) && is_array($responseKey['data'])) {
            // Modify by reference to add events
            foreach ($responseKey['data'] as &$contact) {
                // Mask email and REMOVE real email
                if (isset($contact['Email'])) {
                    $contact['Masked_Email'] = $this->mask_email($contact['Email']);
                    unset($contact['Email']); 
                }
                
                // Do NOT fetch related events here. 
                // $contactId = $contact['id'];
                // $contact['related_events'] = $this->get_events_for_contact($contactId, $accessToken);
            }
        }
        
        return $responseKey;
    }

    public function render_booking_design() {
        require_once plugin_dir_path(__FILE__) . 'includes/shortcode-booking-design.php';
        return render_sage_booking_design();
    }

    public function render_book_appointment() {
        require_once plugin_dir_path(__FILE__) . 'includes/shortcode-book-appointment.php';
        return render_sage_book_appointment();
    }

    private function get_contact_by_id($contactId) {
        $accessToken = $this->get_access_token();
        if (!$accessToken) return new WP_Error('auth_error', 'No Access Token');

        $url = $this->zoho_config['apiDomain'] . "/crm/v2/Contacts/" . $contactId;
        $url = add_query_arg('_z', time(), $url); // Cache Busting

        $args = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Cache-Control' => 'no-cache, no-store, must-revalidate'
            ]
        ];

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) return $response;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return isset($data['data'][0]) ? $data['data'][0] : null;
    }

    private function render_semple_page($contactId) {
        nocache_headers();
        
        // Fetch Contact
        $contact = $this->get_contact_by_id($contactId);
        
        // Fetch Events (existing function)
        $events = [];
        $accessToken = $this->get_access_token();
        if ($accessToken) {
             $events = $this->get_events_for_contact($contactId, $accessToken);
        }

        echo '<!DOCTYPE html><html><head><title>Sage Spine Contact Test</title></head><body style="font-family: sans-serif; padding: 20px;">';
        
        if (!$contact) {
            echo '<h1 style="color: red;">Contact Not Found</h1>';
            echo '<p>ID: ' . esc_html($contactId) . '</p>';
        } else {
            echo '<h1>Contact Details</h1>';
            echo '<ul>';
            echo '<li><strong>Name:</strong> ' . esc_html($contact['Full_Name']) . '</li>';
            echo '<li><strong>Email:</strong> ' . esc_html($contact['Email'] ?? 'N/A') . '</li>';
            echo '<li><strong>Phone:</strong> ' . esc_html($contact['Phone'] ?? 'N/A') . '</li>';
            echo '<li><strong>DOB:</strong> ' . esc_html($contact['Date_of_Birth'] ?? 'N/A') . '</li>';
            echo '</ul>';

            echo '<h2>Appointment History (' . count($events) . ')</h2>';
            if (empty($events)) {
                echo '<p>No appointments found.</p>';
            } else {
                echo '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 800px;">';
                echo '<thead><tr><th>Subject</th><th>Start Time</th><th>Status</th><th>Booking ID</th></tr></thead>';
                echo '<tbody>';
                foreach ($events as $event) {
                    $start = $event['Start_DateTime'] ?? $event['Created_Time'] ?? 'N/A';
                    $bookingId = $event['booking_id'] ?? $event['zohobookingstest__BookingId'] ?? $event['Id'] ?? 'N/A';
                    echo '<tr>';
                    echo '<td>' . esc_html($event['Event_Title'] ?? $event['Subject'] ?? 'Untitled') . '</td>';
                    echo '<td>' . esc_html($start) . '</td>';
                    echo '<td>' . esc_html($event['Check_In_Status'] ?? $event['Status'] ?? 'N/A') . '</td>';
                    echo '<td>' . esc_html($bookingId) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        }
        
        echo '</body></html>';
    }
    public function custom_wp_mail_from($original_email_address) {
        return 'schedule@sagespine.com';
    }

    public function custom_wp_mail_from_name($original_email_from) {
        return 'Sage Spine';
    }
}

new Sage_Appointment_Popup();
