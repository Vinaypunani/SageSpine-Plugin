<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function render_sage_book_appointment() {
    ob_start();
    $api_nonce = wp_create_nonce('wp_rest');
    $api_base = rest_url('sagespine/v1');
    ?>
    <!-- Reuse Tailwind and Scripts from Design -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            important: '#sage-book-app',
            corePlugins: { preflight: false },
             theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        const WP_API_NONCE = "<?php echo $api_nonce; ?>";
        const WP_MEDIA_ENDPOINT = "<?php echo esc_url_raw(rest_url('sagespine/v1/upload')); ?>";
    </script>
    <style>
        /* Scoped Reset to protect against Theme Styles */
        #sage-book-app {
            all: initial; /* Reset everything inheritance */
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: block;
            width: 100%;
            box-sizing: border-box;
            line-height: 1.5;
            color: #1f2937; /* Gray-800 */
        }
        #sage-book-app * {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: #e5e7eb; /* Gray-200 */
        }
        /* Re-apply some basics that 'all: initial' wanes */
        #sage-book-app h1, #sage-book-app h2, #sage-book-app h3, 
        #sage-book-app h4, #sage-book-app h5, #sage-book-app h6 {
            display: block;
            font-weight: bold;
            margin: 0;
        }
        #sage-book-app p { display: block; margin: 0; }

        #sage-book-app button { cursor: pointer; line-height: 1; }
        #sage-book-app input, #sage-book-app select, #sage-book-app textarea {
            display: block;
            font-family: inherit;
        }
        
        /* Fix for conflicting hidden classes */
        #sage-book-app .hidden { display: none !important; }
        
        /* Layout Fixes */
        #sage-book-app .flex { display: flex; }
        #sage-book-app .grid { display: grid; }
        
        /* Custom Thin Scrollbar */
        #sage-book-app .custom-scrollbar::-webkit-scrollbar {
            height: 4px; /* Thin scrollbar for x-axis */
            width: 4px;  /* For y-axis if ever needed */
        }
        #sage-book-app .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        #sage-book-app .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #d1d5db; /* Gray-300 */
            border-radius: 20px;
        }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <div id="sage-book-app" class="sage-booking-container w-full max-w-5xl mx-auto bg-white min-h-screen relative p-4">
        
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="fixed inset-0 bg-white z-50 flex items-center justify-center hidden">
            <div class="animate-spin rounded-full h-16 w-16 border-4 border-gray-200 border-t-emerald-500"></div>
        </div>

        <!-- Steps Header -->
        <div class="mb-8">
            <h1 id="booking-main-title" class="text-3xl font-bold text-center text-emerald-600 mb-2">Enter Appointment Details</h1>
             <!-- Progress Steps can go here if needed -->
        </div>

        <div class="flex flex-col md:flex-row gap-8">
            
            <!-- Left Sidebar: Steps/Summary -->
            <div id="appointment-sidebar" class="w-full md:w-1/3 space-y-4">
                 <!-- Step 1 Trigger -->
                <div id="step-1-trigger" class="p-4 border rounded-lg cursor-pointer transition-colors border-emerald-500 bg-emerald-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i data-lucide="user-plus" class="w-5 h-5 text-emerald-600"></i>
                            <span class="font-medium text-emerald-700" id="summary-service-name">Consultation</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                </div>

                <!-- Step 2 Trigger -->
                 <div id="step-2-trigger" class="p-4 border rounded-lg cursor-pointer transition-colors border-gray-100 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                            <span class="font-medium text-gray-600" id="summary-date-time">Date, Time & Consultant</span>
                        </div>
                         <i data-lucide="chevron-right" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                </div>

                <!-- Step 3 Trigger -->
                <div id="step-3-trigger" class="p-4 border rounded-lg cursor-pointer transition-colors border-gray-100 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                            <span class="font-medium text-gray-600">Your Info</span>
                        </div>
                         <i data-lucide="chevron-right" class="w-4 h-4 text-emerald-600 hidden"></i>
                    </div>
                </div>
            </div>

            <!-- Right Content Area -->
            <div id="appointment-main-content" class="w-full md:w-2/3 min-h-[400px]">
                
                <!-- STEP 1: Select Service/Doctor -->
                <div id="view-step-1" class="step-view">
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-1">Select Provider</h2>
                        <p class="text-gray-500">Choose a healthcare professional for your visit.</p>
                    </div>

                    <div id="services-list" class="space-y-4">
                        <!-- Services injected here -->
                        <div id="services-skeleton" class="space-y-0">
                            <!-- Skeleton Card 1 -->
                            <div class="group flex items-center p-4 bg-white border-b border-gray-100 animate-pulse">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="h-12 w-12 bg-gray-200 rounded-sm"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="h-5 bg-gray-200 rounded w-48 mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-full mb-1"></div>
                                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                                </div>
                                <div class="ml-4 flex items-center gap-2">
                                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                                    <div class="h-3 w-3 bg-gray-200 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end items-center gap-4">
                        <button id="btn-step1-continue" onclick="if(state.selectedService) setStep(2);" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-2.5 rounded-lg font-semibold shadow-lg shadow-emerald-600/20 transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer" disabled>
                            Continue to Step 2
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Date & Time -->
                <div id="view-step-2" class="step-view hidden">
                    <div class="mb-4">
                        <h2 class="text-xl font-semibold mb-2 text-gray-900">Step 2: Choose Date and Time</h2>
                        <p class="text-gray-500">Your appointment will be booked with <span class="font-bold text-gray-800" id="selected-doctor-name">Doctor</span></p>
                    </div>

                    <div class="flex items-center justify-between mb-4 relative z-20">
                        <div class="relative">
                            <button id="month-trigger-btn" class="flex items-center gap-2 hover:bg-gray-50 px-3 py-2 rounded-lg transition-colors group">
                                <h3 class="text-lg font-bold text-gray-800 group-hover:text-emerald-600 transition-colors" id="current-month-label">September, 2024</h3>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-colors"></i>
                            </button>

                            <!-- Custom Month Picker Overlay (Moved inside relative container) -->
                            <div id="custom-month-picker" class="absolute top-full left-0 mt-2 z-30 bg-white border border-gray-200 rounded-xl shadow-xl p-4 w-72 hidden">
                                <!-- Year Navigation -->
                                <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2">
                                    <button id="picker-prev-year" class="p-1 hover:bg-gray-100 rounded-full text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                                    </button>
                                    <span id="picker-year-label" class="font-bold text-gray-800 text-lg">2024</span>
                                    <button id="picker-next-year" class="p-1 hover:bg-gray-100 rounded-full text-gray-500 hover:text-emerald-600 transition-colors">
                                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                <!-- Months Grid -->
                                <div class="grid grid-cols-3 gap-2" id="month-picker-grid">
                                    <!-- Months injected here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative">
                            <button id="btn-month-picker-icon" class="p-2 hover:bg-gray-100 rounded-full transition-colors border border-gray-100 shadow-sm">
                                <i data-lucide="calendar" class="w-5 h-5 text-gray-600"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Days Scroller -->
                    <div class="relative flex items-center gap-4 mb-8">
                        <button id="prev-week-btn" class="w-10 h-10 flex-shrink-0 border border-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-50 transition-colors">
                            <i data-lucide="chevron-left" class="w-5 h-5 text-gray-600"></i>
                        </button>

                        <div class="relative flex-grow overflow-hidden rounded-xl">
                            <div class="flex gap-3 overflow-x-auto custom-scrollbar pb-2 pt-1 px-1 snap-x" id="calendar-days-track">
                                <!-- Days injected -->
                            </div>
                            <!-- Loading Overlay -->
                            <div id="calendar-loading" class="absolute inset-0 bg-white/80 flex items-center justify-center z-10 hidden backdrop-blur-[1px] transition-all duration-300">
                                 <div class="flex flex-col items-center gap-2">
                                     <i data-lucide="loader-2" class="w-6 h-6 text-emerald-600 animate-spin"></i>
                                     <span class="text-xs font-medium text-emerald-600">Loading...</span>
                                 </div>
                            </div>
                        </div>

                        <button id="next-week-btn" class="w-10 h-10 flex-shrink-0 border border-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-50 transition-colors">
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-600"></i>
                        </button>
                    </div>

                    <!-- Slots -->
                    <div id="slots-container" class="space-y-8 min-h-[200px]">
                        <div class="text-center text-gray-500 py-10">Select a date to view availability</div>
                    </div>

                    <!-- Navigation Actions -->
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between items-center pt-6 border-t border-gray-100">
                        <button onclick="setStep(1)" class="w-full sm:w-auto px-8 py-3 rounded-xl border border-slate-300 font-semibold text-slate-600 hover:bg-slate-50 transition-all flex items-center justify-center">
                            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                            Back
                        </button>
                        <button id="btn-step2-continue" onclick="if(state.selectedSlot) setStep(3);" class="w-full sm:w-auto px-10 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer" disabled>
                            Continue to Personal Info
                        </button>
                    </div>
                </div>

                <!-- STEP 3: User Details -->
                <div id="view-step-3" class="step-view hidden">
                    
                    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200">
                        <h2 class="text-xl font-bold mb-6 flex items-center text-gray-900">
                            <i data-lucide="clipboard-list" class="w-6 h-6 mr-2 text-emerald-600"></i>
                            Please enter your details
                        </h2>
                        
                        <form id="booking-form" class="space-y-6">
                            <!-- Personal Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="first_name">First Name *</label>
                                    <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="first_name" name="first_name" placeholder="John" required type="text"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="last_name">Last Name *</label>
                                    <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="last_name" name="last_name" placeholder="Doe" required type="text"/>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="email">Email Address *</label>
                                    <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="email" name="email" placeholder="john.doe@example.com" required type="email"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="phone">Mobile Phone Number *</label>
                                    <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="phone" name="phone" placeholder="(555) 000-0000" required type="tel"/>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1" for="dob">Date of Birth *</label>
                                <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="dob" name="dob" required type="date"/>
                            </div>

                            <!-- Address -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="address_street">Street Address *</label>
                                    <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="address_street" name="address_street" placeholder="123 Main St" required type="text"/>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1" for="address_city">City *</label>
                                        <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="address_city" name="address_city" placeholder="City" required type="text"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1" for="address_state">State *</label>
                                        <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="address_state" name="address_state" placeholder="State" required type="text"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1" for="address_zip">ZIP Code *</label>
                                        <input class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="address_zip" name="address_zip" placeholder="ZIP Code" required type="text"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Insurance -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="insurance_info">Insurance Provider *</label>
                                    <select class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" id="insurance_info" name="insurance_info" required>
                                        <option disabled selected value="">Select Insurance</option>
                                        <option value="Medicare">Medicare</option>
                                        <option value="Medicare with Advantage Plan">Medicare with Advantage Plan</option>
                                        <option value="Worker's Compensation">Worker's Compensation</option>
                                        <option value="Auto/Motor Vehicle">Auto/Motor Vehicle</option>
                                        <option value="Blue Cross">Blue Cross</option>
                                        <option value="Ucare">Ucare</option>
                                        <option value="HealthPartners">HealthPartners</option>
                                        <option value="Medica">Medica</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Insurance Card Image *</label>
                                    <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-8 transition-colors hover:border-emerald-500 group bg-slate-50" id="drop-zone">
                                        <input accept="image/*,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="insurance-upload" name="insurance_file" type="file" required/>
                                        <div class="text-center">
                                            <div class="bg-emerald-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-200 transition-colors">
                                                <i data-lucide="upload-cloud" class="text-emerald-600 w-6 h-6"></i>
                                            </div>
                                            <p class="text-slate-900 font-medium">
                                                <span class="text-emerald-600 hover:underline">Upload a file</span> or drag and drop
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">PNG, JPG, PDF up to 5MB</p>
                                        </div>
                                        <!-- Hidden Input to store the URL after upload -->
                                        <input type="hidden" name="insurance_card_url" id="insurance_card_url">
                                        <!-- Loading/Success State -->
                                        <div id="upload-status" class="absolute inset-0 bg-white/90 flex items-center justify-center hidden rounded-xl">
                                             <p class="text-sm font-medium text-gray-600 flex items-center gap-2">
                                                 <i data-lucide="loader" class="animate-spin w-4 h-4"></i> Uploading...
                                             </p>
                                        </div>
                                    </div>
                                    <p id="file-name-display" class="mt-2 text-sm text-center text-emerald-600 font-medium hidden"></p>
                                </div>
                            </div>
                            
                             <!-- Demographics (Restored from previous step requirements) -->
                             <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Preferred Pharmacy Phone *</label>
                                        <input type="tel" name="pharmacy_phone" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Phone Number">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Referring Provider</label>
                                        <input type="text" name="referring_provider" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Provider Name">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Who is primary provider ?</label>
                                    <input type="text" name="primary_provider" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Primary Provider Name">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                     <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Language *</label>
                                        <select name="language" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                                            <option value="">Select Language</option>
                                            <option value="English">English</option>
                                            <option value="Spanish">Spanish</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Sex *</label>
                                        <select name="sex" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                                            <option value="">Select Sex</option>
                                            <option value="Female">Female</option>
                                            <option value="Male">Male</option>
                                            <option value="Prefer not to answer">Prefer not to answer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ethnicity *</label>
                                        <select name="ethnicity" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                                            <option value="">Select Ethnicity</option>
                                            <option value="Not Hispanic or Latino">Not Hispanic or Latino</option>
                                            <option value="Hispanic or Latino">Hispanic or Latino</option>
                                            <option value="Unknown">Unknown</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Race *</label>
                                        <select name="race" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                                            <option value="">Select Race</option>
                                            <option value="White">White</option>
                                            <option value="Black or African American">Black or African American</option>
                                            <option value="Asian">Asian</option>
                                            <option value="American Indian or Alaska Native">American Indian or Alaska Native</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                             </div>

                            <!-- Buttons -->
                            <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row gap-4 justify-between items-center">
                                <button type="button" onclick="setStep(2)" class="w-full sm:w-auto px-8 py-3 rounded-xl border border-slate-300 font-semibold text-slate-600 hover:bg-slate-50 transition-all flex items-center justify-center">
                                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                                    Previous Step
                                </button>
                                <button type="submit" id="btn-submit-booking" class="w-full sm:w-auto px-10 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center">
                                    Confirm Appointment
                                    <i data-lucide="check" class="w-5 h-5 ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <p class="mt-8 text-center text-sm text-slate-500 flex items-center justify-center gap-1">
                        <i data-lucide="lock" class="w-3 h-3"></i>
                        Your data is protected by industry-standard encryption and HIPAA compliance.
                    </p>
                </div>

                <!-- STEP 4: Success -->
                <div id="view-step-success" class="step-view hidden text-center py-12 px-4 celebration-bg min-h-[400px] flex flex-col items-center justify-center">
                    
                    <div class="mb-8 flex justify-center">
                        <div class="relative">
                            <div class="absolute inset-0 bg-emerald-500/20 rounded-full animate-ping opacity-75"></div>
                            <div class="relative w-24 h-24 bg-emerald-500/10 rounded-full flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-12 h-12 text-emerald-600"></i>
                            </div>
                        </div>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight mb-4 text-slate-900">
                        Appointment confirmed with <span id="confirm-doctor-name">Doctor</span>!
                    </h1>
                    
                    <p class="text-slate-500 mb-10 text-lg">
                        We've sent a confirmation email with all the details and a calendar invite.
                    </p>

                    <!-- Appointment Card -->
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-xl shadow-slate-200/50 p-8 mb-10 relative overflow-hidden w-full max-w-xl text-left">
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-emerald-500"></div>
                        
                        <div class="flex flex-col md:flex-row items-center gap-8">
                            <!-- Date Box -->
                            <div class="flex-shrink-0 w-20 h-24 bg-slate-50 border border-slate-200 rounded-lg flex flex-col items-center overflow-hidden">
                                <div class="bg-slate-200 w-full py-1 text-[10px] font-bold uppercase tracking-widest text-slate-500 text-center" id="confirm-month-abbr">
                                    FEB
                                </div>
                                <div class="flex-1 flex items-center justify-center text-3xl font-bold text-slate-800" id="confirm-day-number">
                                    20
                                </div>
                            </div>
                            
                            <!-- Details -->
                            <div class="flex-1 text-center md:text-left space-y-2">
                                <div class="flex items-center justify-center md:justify-start gap-2 text-slate-900 font-semibold text-xl">
                                    <i data-lucide="calendar" class="w-5 h-5 text-slate-400"></i>
                                    <span id="confirm-datetime">20 Feb 2026 | 07:30 AM</span>
                                </div>
                                <div class="flex items-center justify-center md:justify-start gap-2 text-slate-600">
                                    <i data-lucide="user" class="w-5 h-5 text-slate-400"></i>
                                    <span id="confirm-doctor-detail">Doctor Name</span>
                                </div>
                                <div class="flex items-center justify-center md:justify-start gap-2 text-slate-500 text-sm">
                                    <i data-lucide="globe" class="w-5 h-5 text-slate-400"></i>
                                    <span id="confirm-timezone">America/Chicago - CST (-06:00)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full max-w-xl">
                        <button onclick="location.reload()" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-xl font-bold text-lg flex items-center justify-center transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-emerald-600/20">
                            Book another appointment
                            <i data-lucide="arrow-right" class="ml-2 w-5 h-5"></i>
                        </button>
                    </div>
                    
                    <p class="mt-12 text-slate-400 text-sm">
                        Need help? <a class="text-emerald-600 hover:underline font-medium" href="/contact">Contact our support team</a>
                    </p>
                </div>
                
                <style>
                    .celebration-bg {
                        background-image: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
                    }
                </style>

            </div>
        </div>
    </div>

    <script>
        const API_BASE = "<?php echo $api_base; ?>";
        const API_NONCE = "<?php echo $api_nonce; ?>";
        
        let state = {
            step: 1,
            services: [],
            selectedService: null,  // Doctor/Service object
            selectedDate: null,     // Date object
            selectedDateStr: null,  // YYYY-MM-DD
            selectedSlot: null,     // "HH:mm"
            customer: {}
        };
        
        let calendarDate = new Date(); // Current view month

        // Initial Load
        document.addEventListener('DOMContentLoaded', () => {
            // Assuming renderServices and updateContinueButton are defined elsewhere or will be added
            // renderServices(); 
            // updateContinueButton(); 
            
            lucide.createIcons();
            loadServices(); // Original loadServices
            setupCalendarListeners();
            setupMonthPicker(); // New Custom Picker
            
            // Validation / Form Submit
            document.getElementById('booking-form').addEventListener('submit', handleBookingSubmit);
        });

        // --- Custom Month Picker Logic (Year + Month) ---
        function setupMonthPicker() {
            const container = document.getElementById('custom-month-picker');
            const grid = document.getElementById('month-picker-grid');
            const triggerBtn = document.getElementById('month-trigger-btn');
            const iconBtn = document.getElementById('btn-month-picker-icon');
            
            // Year Nav Elements
            const prevYearBtn = document.getElementById('picker-prev-year');
            const nextYearBtn = document.getElementById('picker-next-year');
            const yearLabel = document.getElementById('picker-year-label');

            if (!container || !grid) return;

            let pickerYear = new Date().getFullYear();
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth(); // 0-11

            // Render Function
            function renderPicker() {
                yearLabel.innerText = pickerYear;
                
                // Update navigation state
                if (pickerYear <= currentYear) {
                    prevYearBtn.disabled = true;
                    prevYearBtn.classList.add('opacity-30', 'cursor-not-allowed');
                } else {
                    prevYearBtn.disabled = false;
                    prevYearBtn.classList.remove('opacity-30', 'cursor-not-allowed');
                }

                grid.innerHTML = '';
                
                for (let i = 0; i < 12; i++) {
                    const date = new Date(pickerYear, i, 1);
                    const mStr = date.toLocaleString('default', { month: 'short' });
                    
                    const btn = document.createElement('button');
                    btn.className = "p-2 rounded-lg text-sm border font-medium transition-all ";
                    
                    // Logic to disable past months
                    const isPast = (pickerYear === currentYear && i < currentMonth) || (pickerYear < currentYear);
                    
                    if (isPast) {
                        btn.className += "border-transparent text-gray-300 cursor-not-allowed bg-gray-50";
                        btn.disabled = true;
                    } else {
                        btn.className += "border-slate-100 text-gray-700 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 bg-white";
                        btn.onclick = () => {
                            changeMonth(date);
                            container.classList.add('hidden');
                        };
                    }
                    
                    btn.innerText = mStr;
                    grid.appendChild(btn);
                }
            }

            // Events
            prevYearBtn.onclick = (e) => {
                e.stopPropagation();
                if (pickerYear > currentYear) {
                    pickerYear--;
                    renderPicker();
                }
            };

            nextYearBtn.onclick = (e) => {
                e.stopPropagation();
                pickerYear++;
                renderPicker();
            };

            // Toggle Logic
            const togglePicker = (e) => {
                e.stopPropagation();
                
                // Reset to current selection year or current year on open?
                // Let's reset to current view year if available, else current year
                if (state.weekStartDate) {
                    pickerYear = state.weekStartDate.getFullYear();
                } else {
                     pickerYear = currentYear;
                }
                
                // Don't allow going back past current
                if(pickerYear < currentYear) pickerYear = currentYear;

                renderPicker();
                container.classList.toggle('hidden');
            };

            if(triggerBtn) triggerBtn.onclick = togglePicker;
            if(iconBtn) iconBtn.onclick = togglePicker;

            // Close on click outside
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target) && e.target !== triggerBtn && !triggerBtn.contains(e.target) && e.target !== iconBtn && !iconBtn.contains(e.target)) {
                    container.classList.add('hidden');
                }
            });
            
            // Initial render
            lucide.createIcons(); 
        }

        async function changeMonth(date) {
            // Update state to start of that week or month? 
            // The existing logic relies on `state.weekStartDate`. 
            // Let's set `weekStartDate` to the 1st of that month, or today if it's current month.
            
            const today = new Date();
            let newStart = new Date(date.getFullYear(), date.getMonth(), 1);
            
            // If selecting current month, don't go back in time, start from today
            if (newStart.getMonth() === today.getMonth() && newStart.getFullYear() === today.getFullYear()) {
                newStart = new Date(today);
            }
            
            state.weekStartDate = newStart;
            
            // Updated to use loadWeekData for consistent loading state
            await loadWeekData();
            
            // Update Label
             const monthLabel = document.getElementById('current-month-label');
             if(monthLabel) {
                 monthLabel.innerText = newStart.toLocaleString('default', { month: 'long', year: 'numeric' });
             }
        }
        // ---------------------------------

        function showLoading(show) {
             const el = document.getElementById('loading-overlay');
             if(show) el.classList.remove('hidden');
             else el.classList.add('hidden');
        }

        // --- Step Management ---
        function setStep(step) {
            // Prevent skipping ahead
            if (step > state.step && step > state.maxStep) return;
            
            state.step = step;
            if (step > state.maxStep) state.maxStep = step;

            document.querySelectorAll('.step-view').forEach(el => el.classList.add('hidden'));
            
            if (step === 4) {
                const successView = document.getElementById('view-step-success');
                if (successView) {
                    successView.classList.remove('hidden');
                }
                
                // Full screen layout for success
                document.getElementById('appointment-sidebar').classList.add('hidden');
                document.getElementById('appointment-main-content').classList.remove('md:w-2/3');
                
                // Hide Main Title on Success Page
                const mainTitle = document.getElementById('booking-main-title');
                if(mainTitle) mainTitle.classList.add('hidden');
                
            } else {
                const stepView = document.getElementById(`view-step-${step}`);
                if (stepView) {
                    stepView.classList.remove('hidden');
                }
                
                // Normal layout
                document.getElementById('appointment-sidebar').classList.remove('hidden');
                document.getElementById('appointment-main-content').classList.add('md:w-2/3');
                
                // Show Main Title on other pages
                const mainTitle = document.getElementById('booking-main-title');
                if(mainTitle) mainTitle.classList.remove('hidden');
            }

            updateSidebar();
        }

        function updateSidebar() {
            const steps = [1, 2, 3];
            steps.forEach(s => {
                const el = document.getElementById(`step-${s}-trigger`);
                // Select the chevron-right icon (it's the last child in our structure)
                const icon = el.querySelector('i:last-child'); 
                const text = el.querySelector('span');
                
                // Reset click handler
                el.onclick = () => {
                     // Allow going back, or forward if already visited
                     if (s < state.step || s <= state.maxStep) {
                         setStep(s);
                     }
                };
                
                if (s === state.step) {
                    // Active Step
                    el.className = "p-4 border rounded-lg cursor-pointer transition-colors border-emerald-500 bg-emerald-50";
                    text.classList.remove('text-gray-600'); 
                    text.classList.add('text-emerald-700');
                    if(icon) {
                        // Show active arrow (or keep hidden if design prefers no arrow on active)
                        // Step 1 has arrow on active in screenshot? No, typically active doesn't point right.
                        // But user said "like first step". In screenshot 1, first step has arrow.
                        // Let's make sure it's visible and green.
                        icon.classList.remove('hidden', 'text-gray-400');
                        icon.classList.add('text-emerald-600');
                    }
                } else if (s < state.step) {
                    // Completed Step
                    el.className = "p-4 border rounded-lg cursor-pointer transition-colors border-green-100 bg-white hover:bg-green-50";
                    text.classList.remove('text-gray-600', 'text-emerald-700');
                    text.classList.add('text-gray-800');
                    if(icon) {
                        // Completed steps MUST show the arrow as per user request
                        icon.classList.remove('hidden', 'text-gray-400');
                        icon.classList.add('text-emerald-600');
                    }
                } else {
                    // Future Step
                     el.className = "p-4 border rounded-lg cursor-not-allowed transition-colors border-gray-100 bg-gray-50 opacity-60";
                     text.classList.remove('text-emerald-700', 'text-gray-800');
                     text.classList.add('text-gray-400');
                     if(icon) {
                        // Future steps usually hide arrow or show gray
                        icon.classList.remove('text-emerald-600');
                        icon.classList.add('text-gray-400', 'hidden'); // Hide on future
                     }
                     el.onclick = null;
                }
            });

            // Update Text Summary
            if (state.selectedService) {
                document.getElementById('summary-service-name').innerText = state.selectedService.name;
                // Update doctor name in booking message
                const doctorNameEl = document.getElementById('selected-doctor-name');
                if (doctorNameEl) {
                    doctorNameEl.innerText = state.selectedService.name;
                }
            }
            
            if (state.selectedDateStr) {
                 const d = new Date(state.selectedDateStr);
                 const day = d.getDate().toString().padStart(2, '0');
                 const m = d.toLocaleString('default', { month: 'short' });
                 const y = d.getFullYear();
                 let summary = `${day} ${m} ${y}`;
                 if(state.selectedSlot) summary += ` ${state.selectedSlot}`;
                 document.getElementById('summary-date-time').innerText = summary;
            }
        }

        function formatTimeDisplay(time) {
            // Ensure nice formatting "09:00 AM"
            if (time.match(/[AP]M/i)) return time;
            
            const [h, m] = time.split(':');
            const date = new Date();
            date.setHours(parseInt(h));
            date.setMinutes(parseInt(m));
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function selectSlot(time) {
            state.selectedSlot = time;
            
            // Update Summary
            if (state.selectedDateStr) {
                const d = new Date(state.selectedDateStr);
                const day = d.getDate().toString().padStart(2, '0');
                const m = d.toLocaleString('default', { month: 'short' });
                const y = d.getFullYear();
                
                const summary = `${day} ${m} ${y} ${time}`;
                const summaryEl = document.getElementById('summary-date-time');
                if(summaryEl) summaryEl.innerText = summary;
            }
            
            // Re-render only slots to update selection (using cached data)
            if(state.selectedDateStr && state.slotsCache[state.selectedDateStr]) {
                 renderSlots(state.slotsCache[state.selectedDateStr].slots);
            }
            
            // Enable Continue Button
            const btn = document.getElementById('btn-step2-continue');
            if(btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        // --- API: Staffs (for images) ---
        async function loadStaffs() {
            try {
                const res = await axios.get(`${API_BASE}/staffs`);
                let data = res.data;
                // Handle double-encoded
                if (typeof data === 'string') {
                     try { data = JSON.parse(data); } catch(e){}
                }
                
                let staffs = [];
                if(data && data.response && data.response.returnvalue && data.response.returnvalue.data){
                    staffs = data.response.returnvalue.data;
                } else if(Array.isArray(data)){
                    staffs = data;
                }
                return staffs;
            } catch (e) {
                console.error("Failed to load staffs", e);
                return [];
            }
        }

        // --- API: Services ---
        async function loadServices() {
            try {
                // Load Staffs first to map images
                const staffs = await loadStaffs();
                const staffMap = {};
                staffs.forEach(s => {
                    staffMap[s.id] = s.photo || ''; // Map ID to Photo URL
                });

                const res = await axios.get(`${API_BASE}/services`);
                let data = res.data;
                
                if (typeof data === 'string') {
                    try { data = JSON.parse(data); } catch (e) {}
                }
                
                let services = [];
                if (data && data.response && data.response.returnvalue) {
                     const val = data.response.returnvalue;
                     if (val.data && Array.isArray(val.data)) {
                         services = val.data;
                     } else if (Array.isArray(val)) {
                         services = val;
                     } else if (val.services && Array.isArray(val.services)) {
                         services = val.services;
                     } else {
                         services = Object.values(val);
                     }
                } else if (Array.isArray(data)) {
                    services = data;
                }
                
                // Merge Images
                services = services.map(svc => {
                    let photoUrl = '';
                    if (svc.assigned_staffs && svc.assigned_staffs.length > 0) {
                        const staffId = svc.assigned_staffs[0];
                        photoUrl = staffMap[staffId];
                    }
                    return { ...svc, photo: photoUrl };
                });
                
                state.services = services; // Store in state for re-rendering
                
                renderServices(services);
            } catch (e) {
                console.error("Failed to load services", e);
                document.getElementById('services-list').innerHTML = `<div class="text-red-500">Failed to load services. Please refresh.</div>`;
            }
        }

        function renderServices(list) {
            const container = document.getElementById('services-list');
            const skeleton = document.getElementById('services-skeleton');
            
            // Hide skeleton
            if (skeleton) skeleton.remove();
            
            container.innerHTML = '';

            if (list.length === 0) {
                 container.innerHTML = `<div class="text-gray-500">No services found.</div>`;
                 return;
            }

            list.forEach(svc => {
                const name = svc.name || "Doctor";
                const avatarUrl = svc.photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=10b981&color=fff`;
                const isSelected = state.selectedService && state.selectedService.id === svc.id;
                
                // Classes for Selected vs Unselected
                const containerClasses = isSelected 
                    ? "rounded-xl border-2 border-emerald-600 bg-emerald-50/50" 
                    : "rounded-xl border border-gray-200 hover:border-emerald-600/50 bg-white hover:bg-gray-50";

                const imageClasses = isSelected
                    ? "border-2 border-emerald-600"
                    : "grayscale group-hover:grayscale-0 transition-all";

                const radioHtml = isSelected
                    ? `<div class="w-6 h-6 rounded-full border-2 border-emerald-600 flex items-center justify-center"><div class="w-2.5 h-2.5 bg-emerald-600 rounded-full"></div></div>`
                    : `<div class="w-6 h-6 rounded-full border-2 border-gray-300 group-hover:border-emerald-600 transition-all"></div>`;

                const el = document.createElement('div');
                // Removed 'border-b' logic, added full card styling
                el.className = `relative flex flex-col md:flex-row items-center md:items-start gap-5 p-6 transition-all cursor-pointer group ${containerClasses}`;
                el.id = `service-card-${svc.id}`;
                el.onclick = () => selectService(svc);
                
                el.innerHTML = `
                    <div class="relative">
                         <img class="w-20 h-20 rounded-full object-cover ${imageClasses}" src="${avatarUrl}" alt="${name}">
                    </div>
                    
                    <div class="flex-1 text-center md:text-left">
                         <h3 class="text-lg font-bold text-gray-900">${name}</h3>
                         <p class="text-emerald-600 font-medium text-sm mb-2">${svc.description ? svc.description.split('Log in')[0] : 'Interventional Pain Medicine'}</p> 
                         
                         <div class="flex flex-col gap-1 text-sm text-gray-600">
                             <!-- Address removed -->
                         </div>
                    </div>
                    
                    <div class="flex flex-row md:flex-col items-center justify-between gap-4 w-full md:w-auto md:h-full">
                         <div class="text-sm font-semibold text-gray-700 bg-white px-3 py-1 rounded-full border border-gray-200">
                             ${svc.duration || '15 mins'}
                         </div>
                         ${radioHtml}
                    </div>
                `;
                container.appendChild(el);
            });
            lucide.createIcons();
            
            // Update Continue Button State
            updateContinueButton();
        }
        
        function updateContinueButton() {
            const btn = document.getElementById('btn-step1-continue');
            if(btn) {
                if(state.selectedService) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        async function selectService(svc) {
            state.selectedService = svc;
            
            // Re-render to show selection state
            if (state.services) {
                renderServices(state.services);
            }
            
            // Update booking message with doctor name
            const doctorNameEl = document.getElementById('selected-doctor-name');
            if (doctorNameEl) {
                doctorNameEl.textContent = svc.name || 'Doctor';
            }
            
            // Update summary
            document.getElementById('summary-service-name').textContent = svc.name || 'Service';
            
            // Enable Continue Button
            updateContinueButton();
            
            // Pre-load calendar data for smooth transition
            let calendarDate = new Date(); 
            await loadWeekData();
        }

        // --- Calendar Logic (Weekly Strip) ---
        function setupCalendarListeners() {
            // Initialize Week Start (Monday of current week)
            const today = new Date();
            const day = today.getDay(); // 0=Sun, 1=Mon
            const diff = today.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
            const monday = new Date(today.setDate(diff));
            state.weekStartDate = monday;
            
            // Month selector dropdown listener
            const monthSelector = document.getElementById('month-selector');
            if (monthSelector) {
                monthSelector.addEventListener('change', async (e) => {
                    const [year, month] = e.target.value.split('-').map(Number);
                    // Get first day of selected month
                    const firstDay = new Date(year, month - 1, 1);
                    
                    // Find the Monday of the week containing the first day
                    const dayOfWeek = firstDay.getDay(); // 0=Sun, 1=Mon, etc.
                    const diff = firstDay.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                    const monday = new Date(year, month - 1, diff);
                    
                    state.weekStartDate = monday;
                    await loadWeekData();
                });
            }
            
            // Week navigation arrows
            const prevWeekBtn = document.getElementById('prev-week-btn');
            const nextWeekBtn = document.getElementById('next-week-btn');
            
            if (prevWeekBtn) {
                prevWeekBtn.onclick = () => changeWeek(-1);
            }
            if (nextWeekBtn) {
                nextWeekBtn.onclick = () => changeWeek(1);
            }
            
            // Refresh icons for new buttons
            lucide.createIcons();
        }

        async function changeWeek(direction) {
            const isMobile = window.innerWidth < 768;
            const daysToScroll = isMobile ? 5 : 7;
            
            const newDate = new Date(state.weekStartDate);
            newDate.setDate(newDate.getDate() + (direction * daysToScroll));
            
            state.weekStartDate = newDate;
            await loadWeekData();
        }
        
        
        
        async function loadWeekData() {
            const loadingEl = document.getElementById('calendar-loading');
            const trackEl = document.getElementById('calendar-days-track');
            const slotsContainer = document.getElementById('slots-container');
            
            // Show loading
            if(loadingEl) loadingEl.classList.remove('hidden');

            if(trackEl) trackEl.innerHTML = ''; // Clear the track completely
            
            // Hide slots until a date is selected
            if (slotsContainer) slotsContainer.innerHTML = '<div class="text-center text-gray-500 py-10">Select a date to view availability</div>';
            
            // Update month selector to reflect current week's month
            const monthSelector = document.getElementById('month-selector');
            const monthLabel = document.getElementById('current-month-label');
            
            if (state.weekStartDate) {
                const year = state.weekStartDate.getFullYear();
                const month = String(state.weekStartDate.getMonth() + 1).padStart(2, '0');
                const monthName = state.weekStartDate.toLocaleString('default', { month: 'long' });
                
                if(monthSelector) monthSelector.value = `${year}-${month}`;
                if(monthLabel) monthLabel.textContent = `${monthName}, ${year}`;
            }
            
            // Start both the data fetch and minimum display timer
            const minDisplayTime = new Promise(resolve => setTimeout(resolve, 500)); // Increased to 500ms for visibility
            
            // Render calendar structure first (without data)
            renderCalendarDays();
            
            // Fetch availability data
            const dataFetch = fetchWeekAvailability();
            
            // Wait for both to complete
            await Promise.all([dataFetch, minDisplayTime]);
            
            // Hide loading state and show slots
            if(loadingEl) loadingEl.classList.add('hidden');
            if(slotsContainer) slotsContainer.classList.remove('hidden');
        }

        // --- Helper: Local Date String (YYYY-MM-DD) ---
        function getLocalDateString(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function renderCalendarDays() {
            const container = document.getElementById('calendar-days-track');
            
            const start = new Date(state.weekStartDate);
            const end = new Date(state.weekStartDate);
            end.setDate(end.getDate() + 6);

            container.innerHTML = '';
            
            const todayStr = getLocalDateString(new Date());

            // Responsive Day Count
            const isMobile = window.innerWidth < 768;
            const daysToShow = isMobile ? 5 : 7;

            // Updated Container Classes for new design - Added padding to prevent shadow clipping
            container.className = `flex gap-3 overflow-x-auto custom-scrollbar flex-grow pb-2 pt-1 px-1 snap-x`;

            for (let i = 0; i < daysToShow; i++) {
                const date = new Date(state.weekStartDate);
                date.setDate(date.getDate() + i);
                
                const dateStr = getLocalDateString(date);
                const dayName = date.toLocaleString('default', { weekday: 'short' }).toUpperCase(); // MON, TUE
                const dayNum = date.getDate();
                
                const btn = document.createElement('div'); // Div now, clickable
                btn.id = `day-btn-${dateStr}`;
                
                // Base Classes
                // Default: flex-1 min-w-[70px] p-3 rounded-xl border text-center transition-all cursor-pointer select-none 
                let baseClass = "flex-1 min-w-[70px] p-3 rounded-xl border text-center transition-all cursor-pointer select-none box-border ";
                
                // Past Day Check
                const isPast = dateStr < todayStr;
                
                // Check if we have cached data for this day
                const cachedData = state.slotsCache[dateStr];
                const hasNoSlots = cachedData && 
                                   cachedData.serviceId === state.selectedService?.id && 
                                   (!Array.isArray(cachedData.slots) || cachedData.slots.length === 0);
                
                if (isPast) {
                    baseClass += "border-gray-100 bg-gray-50 opacity-40 cursor-not-allowed";
                } else if (hasNoSlots) {
                    // Day has no available slots
                    baseClass += "border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed";
                    btn.title = "No slots available";
                } else if (state.selectedDateStr === dateStr) {
                    // Selected: border-emerald-500 bg-emerald-50 text-emerald-700
                    // Added ring and adjusted shadow to account for "cutting" issue, ensure space
                    baseClass += "border-emerald-600 bg-emerald-50 shadow-md ring-1 ring-emerald-600/20 transform scale-100"; // Removed scale-105 to reduce overflow risk
                } else {
                    // Unselected
                    baseClass += "border-gray-200 bg-white hover:border-emerald-500/50 hover:bg-gray-50";
                }
                
                btn.className = baseClass;
                
                // Text Colors
                const dayNumClass = (state.selectedDateStr === dateStr && !isPast && !hasNoSlots) ? 'text-emerald-700' : 'text-gray-900';
                const dayNameClass = (state.selectedDateStr === dateStr && !isPast && !hasNoSlots) ? 'text-emerald-600' : 'text-gray-500';

                btn.innerHTML = `
                    <p class="text-2xl font-bold ${dayNumClass}">${dayNum}</p>
                    <p class="text-xs font-semibold ${dayNameClass} uppercase">${dayName}</p>
                `;
                
                if (!isPast && !hasNoSlots) {
                    btn.onclick = () => selectDate(date);
                }
                
                container.appendChild(btn);
            }
        }

        // Cache for slots to avoid re-fetching
        if (!state.slotsCache) state.slotsCache = {};

        async function fetchWeekAvailability() {
            if (!state.selectedService) return;
            
            const daysToFetch = [];
            const todayStr = getLocalDateString(new Date());

            for (let i = 0; i < 7; i++) {
                const date = new Date(state.weekStartDate);
                date.setDate(date.getDate() + i);
                const dateStr = getLocalDateString(date);
                
                // Skip past days
                if (dateStr < todayStr) continue;
                
                // Skip if already cached
                if (state.slotsCache[dateStr] && state.slotsCache[dateStr].serviceId === state.selectedService.id) {
                    updateDayAvailability(dateStr, state.slotsCache[dateStr].slots);
                    continue;
                }
                
                daysToFetch.push(fetchSlotsForDate(dateStr));
            }
            
            await Promise.all(daysToFetch);
        }

        async function fetchSlotsForDate(dateStr) {
             try {
                // Formatting
                const [y, m, d] = dateStr.split('-').map(Number);
                const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                const apiDate = `${String(d).padStart(2, '0')}-${months[m-1]}-${y}`;
                
                // Staff Logic
                let staffId = state.selectedService.staff_id;
                if (!staffId && state.selectedService.assigned_staffs && state.selectedService.assigned_staffs[0]) {
                    staffId = state.selectedService.assigned_staffs[0];
                }

                const res = await axios.get(`${API_BASE}/slots`, {
                    params: {
                        service_id: state.selectedService.id, 
                        staff_id: staffId,     
                        selected_date: apiDate
                    }
                });
                
                let data = res.data;
                 // Handle Double Encode
                 if (typeof data === 'string') { try { data = JSON.parse(data); } catch(e){} }

                let slots = [];
                if (data.response && data.response.returnvalue) {
                     const val = data.response.returnvalue;
                     if (val.data) slots = val.data;
                     else if (Array.isArray(val)) slots = val;
                     else if (typeof val === 'object' && !val.message && !val.status) {
                         slots = Object.keys(val).filter(k => k.includes(':'));
                     }
                } else if (Array.isArray(data)) {
                    slots = data;
                }
                
                // Cache it
                state.slotsCache[dateStr] = {
                    serviceId: state.selectedService.id,
                    slots: slots
                };
                
                updateDayAvailability(dateStr, slots);
                
             } catch (e) {
                 console.error("Error fetching slots for " + dateStr, e);
             } finally {
                 const loader = document.getElementById(`loading-${dateStr}`);
                 if (loader) loader.classList.add('hidden');
             }
        }

        function updateDayAvailability(dateStr, slots) {
            const btn = document.getElementById(`day-btn-${dateStr}`);
            if (!btn) return;
            
            if (!Array.isArray(slots) || slots.length === 0) {
                // Disable if no slots
                if(state.selectedDateStr !== dateStr) {
                    btn.classList.add('opacity-50', 'bg-gray-50', 'cursor-not-allowed', 'line-through-decoration');
                    btn.classList.remove('hover:border-emerald-400', 'hover:text-emerald-600', 'cursor-pointer', 'bg-white');
                    btn.onclick = null;
                    btn.title = "No slots available";
                }
            }
            
            if (state.selectedDateStr === dateStr) {
                renderSlots(slots);
            }
        }

        function selectDate(date) {
            const dateStr = getLocalDateString(date);
            
            state.selectedDate = date;
            state.selectedDateStr = dateStr;
            
            renderCalendarDays(); 
            
            if(state.slotsCache[dateStr] && state.slotsCache[dateStr].serviceId === state.selectedService.id) {
                renderSlots(state.slotsCache[dateStr].slots);
            } else {
                fetchSlotsForDate(dateStr); 
            }
        }



        function renderSlots(slots) {
            const container = document.getElementById('slots-container');
            container.innerHTML = '';
            
            // Handle different slot formats (array of strings or object keys)
            let slotList = [];
            if (Array.isArray(slots)) {
                 slotList = slots;
            } else if (typeof slots === 'object') {
                 // Sort keys if object
                 slotList = Object.keys(slots).sort();
            }

            if (slotList.length === 0) {
                 container.innerHTML = `<div class="text-center text-gray-500 py-10">No availability for this date.</div>`;
                 return;
            }

            // Split into Morning / Afternoon
            const morningSlots = [];
            const afternoonSlots = [];

            slotList.forEach(time => {
                // Parse "09:00" or "09:00 AM"
                let hours = 0;
                let isPM = false;

                if (time.match(/PM/i)) isPM = true;
                if (time.match(/AM/i)) isPM = false;
                
                const timeParts = time.replace(/[AP]M/i, '').trim().split(':');
                if(timeParts.length >= 1) {
                    hours = parseInt(timeParts[0]);
                }
                
                // Convert to 24h for comparison
                let hour24 = hours;
                if (isPM && hours < 12) hour24 += 12;
                if (!isPM && hours === 12) hour24 = 0;

                // Logic: Morning < 12:00, Afternoon >= 12:00
                const isMorning = hour24 < 12;
                
                if (isMorning) morningSlots.push(time);
                else afternoonSlots.push(time);
            });

            // Helper to create slot button
            const createSlotBtn = (time) => {
                const isSelected = state.selectedSlot === time;
                const btn = document.createElement('button');
                // Base classes
                let classes = "py-3 px-4 rounded-xl border text-sm font-medium transition-all w-full ";
                
                if (isSelected) {
                    classes += "border-2 border-emerald-600 bg-emerald-600 text-white shadow-lg shadow-emerald-200";
                } else {
                    classes += "border-slate-200 bg-white hover:border-emerald-600 hover:text-emerald-600 text-gray-700";
                }
                
                btn.className = classes;
                btn.innerText = formatTimeDisplay(time);
                btn.onclick = () => selectSlot(time);
                return btn;
            };

            // Render Morning Section
            if (morningSlots.length > 0) {
                 const section = document.createElement('section');
                 section.className = "mb-6";
                 section.innerHTML = `
                    <h4 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i> Morning
                    </h4>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3"></div>
                 `;
                 const list = section.querySelector('.grid');
                 morningSlots.forEach(t => list.appendChild(createSlotBtn(t)));
                 container.appendChild(section);
            }

            // Render Afternoon Section
            if (afternoonSlots.length > 0) {
                 const section = document.createElement('section');
                 section.innerHTML = `
                    <h4 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i data-lucide="sunset" class="w-4 h-4 text-indigo-500"></i> Afternoon
                    </h4>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3"></div>
                 `;
                 const list = section.querySelector('.grid');
                 afternoonSlots.forEach(t => list.appendChild(createSlotBtn(t)));
                 container.appendChild(section);
            }
            
            // If no valid parsed slots but we had raw data? (Edge case fallback)
            if (container.children.length === 0 && slotList.length > 0) {
                 const section = document.createElement('div');
                 section.className = "grid grid-cols-2 sm:grid-cols-4 gap-3";
                 slotList.forEach(t => section.appendChild(createSlotBtn(t)));
                 container.appendChild(section);
            }

            lucide.createIcons();
        }

        // --- Step 3: Booking ---
        async function handleBookingSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            state.customer = data;
            
            if (!confirm(`Confirm booking with ${state.selectedService.name} on ${state.selectedDateStr} at ${state.selectedSlot}?`)) return;

            showLoading(true);

            try {
                // Convert Time to what API expects (yyyy-MM-dd HH:mm:ss)
                // Need to parse selectedSlot (e.g. "10:00 AM") to 24h
                // Simple Helper
                const timeStr = state.selectedSlot;
                // ... parsing logic ... assuming we send raw string combined with date for now or handled by backend
                // Backend expects "yyyy-MM-dd HH:mm:ss"
                
                // Quick Parse 12h to 24h
                 const [time, modifier] = timeStr.split(' ');
                 let [hours, minutes] = time.split(':');
                 if (hours === '12') hours = '00';
                 if (modifier === 'PM' || modifier === 'pm') hours = parseInt(hours, 10) + 12;
                 
                 const formattedTime = `${state.selectedDateStr} ${hours}:${minutes}:00`;

                // Get staff ID from assigned_staffs array
                let staffId = state.selectedService.staff_id;
                if (!staffId && state.selectedService.assigned_staffs && state.selectedService.assigned_staffs.length > 0) {
                    staffId = state.selectedService.assigned_staffs[0];
                }
                
                // Format DOB to match API expectations (DD-MMM-YYYY format)
                let formattedDOB = data.dob;
                if (data.dob) {
                    const dobDate = new Date(data.dob);
                    const day = String(dobDate.getDate()).padStart(2, '0');
                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    const month = monthNames[dobDate.getMonth()];
                    const year = dobDate.getFullYear();
                    formattedDOB = `${day}-${month}-${year}`;
                }
                
                console.log("Booking Data:", data);
                console.log("Formatted DOB:", formattedDOB);
                
                // Create FormData instead of JSON payload
                const formData = new FormData();
                formData.append('service_id', state.selectedService.service_id || state.selectedService.id);
                formData.append('staff_id', staffId);
                formData.append('from_time', formattedTime);
                
                // customer_details as JSON string
                const customerDetails = {
                    name: `${data.first_name} ${data.last_name}`,
                    email: data.email,
                    phone_number: data.phone
                };
                formData.append('customer_details', JSON.stringify(customerDetails));
                
                // additional_fields as JSON string
                // Map new optional fields + DOB
                const additionalFields = {
                    "Date Of Birth": formattedDOB,
                    "Street Address": data.address_street || "",
                    "City": data.address_city || "",
                    "State": data.address_state || "",
                    "ZIP Code": data.address_zip || "",
                    "Insurance Info": data.insurance_info || "",
                    "Insurance Card": data.insurance_card_url || "",
                    "What is your preferred pharmacy phone number?": data.pharmacy_phone || "",
                    "Referring Provider": data.referring_provider || "",
                    "Who is primary provider ?": data.primary_provider || "",
                    "Language": data.language || "",
                    "Sex": data.sex || "",
                    "Ethnicity": data.ethnicity || "",
                    "Race": data.race || ""
                };
                formData.append('additional_fields', JSON.stringify(additionalFields));
                
                console.log("Booking FormData:");
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
                
                showLoading(true); // Show loading indicator
                
                const res = await axios.post(`${API_BASE}/book`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                const result = res.data;

                console.log("Booking Response:", result);

                if (result.response && result.response.status === 'success') {
                    // Populate success screen with booking details
                    const bookingInfo = result.response.returnvalue;
                    
                    // Update doctor name
                    const doctorName = bookingInfo.staff_name || state.selectedService.name || 'Doctor';
                    document.getElementById('confirm-doctor-name').textContent = doctorName;
                    document.getElementById('confirm-doctor-detail').textContent = doctorName;
                    
                    // Format and display date/time
                    const bookingDate = new Date(state.selectedDateStr + ' ' + state.selectedSlot);
                    const options = { day: '2-digit', month: 'short', year: 'numeric' };
                    const formattedDate = bookingDate.toLocaleDateString('en-GB', options);
                    const formattedTime = state.selectedSlot;
                    document.getElementById('confirm-datetime').textContent = `${formattedDate} | ${formattedTime}`;
                    
                    // New: Populate Split Date Box (Month Abbr and Day Number)
                    const monthAbbr = bookingDate.toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
                    const dayNum = bookingDate.getDate();
                    
                    const monthEl = document.getElementById('confirm-month-abbr');
                    const dayEl = document.getElementById('confirm-day-number');
                    if(monthEl) monthEl.textContent = monthAbbr;
                    if(dayEl) dayEl.textContent = dayNum;

                    // Update timezone (hardcoded as per request)
                    document.getElementById('confirm-timezone').textContent = "America/Chicago - CST (-06:00)";
                    
                    setStep(4); // Show success screen
                } else {
                     const errorMessage = result.response?.returnvalue?.message || result.message || "Unknown error";
                     alert("Booking Failed: " + errorMessage);
                     console.error(result);
                }

            } catch(e) {
                console.error("Booking Error", e);
                alert("An error occurred while booking. Please try again.");
            } finally {
                showLoading(false);
            }
        }


        // --- Optional Fields Logic ---
        function initOptionalFields() {
             // Accordion Toggle Removed

            // File Upload Logic
            const fileInput = document.getElementById('insurance-upload');
            const fileNameDisplay = document.getElementById('file-name-display');
            const hiddenUrlInput = document.getElementById('insurance_card_url');
            const uploadStatus = document.getElementById('upload-status');
            const dropZone = document.getElementById('drop-zone');

            if (fileInput) {
                fileInput.addEventListener('change', async (e) => {
                    if (e.target.files.length > 0) {
                        await handleFileUpload(e.target.files[0]);
                    }
                });
            }
            
            // Drag and Drop
            if (dropZone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => dropZone.classList.add('bg-gray-50', 'border-emerald-500'));
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => dropZone.classList.remove('bg-gray-50', 'border-emerald-500'));
                });

                dropZone.addEventListener('drop', async (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    if (files.length > 0) {
                        fileInput.files = files; // Update input files
                        await handleFileUpload(files[0]);
                    }
                });
            }

            async function handleFileUpload(file) {
                // Validation (Max 1MB)
                const MAX_SIZE = 1 * 1024 * 1024; // 1MB
                if (file.size > MAX_SIZE) {
                    alert("File is too large. Max size is 1MB.");
                    fileInput.value = ''; // Reset input
                    return;
                }

                // Show UI
                fileNameDisplay.textContent = "Uploading...";
                fileNameDisplay.classList.remove('hidden');
                if(uploadStatus) uploadStatus.classList.remove('hidden');

                try {
                    // Generate Random Filename
                    const timestamp = Date.now();
                    const randomStr = Math.random().toString(36).substring(2, 8);
                    const extension = file.name.split('.').pop();
                    const newFileName = `insurance_${timestamp}_${randomStr}.${extension}`;

                    // Upload to WP Media Library
                    const formData = new FormData();
                    // Pass the file with the new name
                    formData.append('file', file, newFileName);

                    // Note: WP REST API for Media usually requires Content-Disposition header if sending raw binary, 
                    // but using FormData is often easier if supported or using 'file' param.
                    // Let's try standard FormData upload which WP supports.
                    
                    const res = await axios.post(WP_MEDIA_ENDPOINT, formData, {
                        headers: {
                            'X-WP-Nonce': WP_API_NONCE,
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    if (res.status === 201 || res.status === 200) {
                        const fileUrl = res.data.source_url;
                        if(hiddenUrlInput) hiddenUrlInput.value = fileUrl;
                         // Success UI
                        fileNameDisplay.innerHTML = `<span class="text-emerald-600 font-medium">Uploaded: ${file.name}</span>`;
                        fileNameDisplay.classList.remove('hidden');
                    } else {
                        throw new Error('Upload failed');
                    }

                } catch (e) {
                    console.error("File Upload Error", e);
                    alert("Failed to upload image. Please try again.");
                    fileNameDisplay.textContent = "Upload failed.";
                } finally {
                    if(uploadStatus) uploadStatus.classList.add('hidden');
                }
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', initOptionalFields);

    </script>

    <?php
    return ob_get_clean();
}
