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
            important: true,
            corePlugins: { preflight: false }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <div id="sage-book-app" class="sage-booking-container w-full max-w-5xl mx-auto bg-white min-h-screen relative p-4">
        
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="fixed inset-0 bg-white z-50 flex items-center justify-center hidden">
            <div class="animate-spin rounded-full h-16 w-16 border-4 border-gray-200 border-t-emerald-500"></div>
        </div>

        <!-- Steps Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-center text-emerald-600 mb-2">Enter Appointment Details</h1>
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
                         <i data-lucide="check" class="hidden w-4 h-4 text-emerald-600"></i>
                    </div>
                </div>

                <!-- Step 3 Trigger -->
                <div id="step-3-trigger" class="p-4 border rounded-lg cursor-pointer transition-colors border-gray-100 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                            <span class="font-medium text-gray-600">Your Info</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content Area -->
            <div id="appointment-main-content" class="w-full md:w-2/3 min-h-[400px]">
                
                <!-- STEP 1: Select Service/Doctor -->
                <div id="view-step-1" class="step-view">
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
                            
                            <!-- Skeleton Card 2 -->
                            <div class="group flex items-center p-4 bg-white border-b border-gray-100 animate-pulse" style="animation-delay: 0.1s">
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
                            
                            <!-- Skeleton Card 3 -->
                            <div class="group flex items-center p-4 bg-white border-b border-gray-100 animate-pulse" style="animation-delay: 0.2s">
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
                </div>

                <!-- STEP 2: Date & Time -->
                <div id="view-step-2" class="step-view hidden">
                    <!-- Calendar & Slots Reuse -->
                    <div class="flex flex-col gap-6">
                        <!-- Booking Message -->
                        <div class="text-gray-700 text-base mb-2">
                            Your appointment will be booked with <span class="font-semibold" id="selected-doctor-name">Andrew Clary</span>
                        </div>
                        
                        <!-- Controls -->
                         <div class="flex flex-col-reverse md:flex-row items-center justify-between gap-4 w-full">
                            <!-- Month Dropdown -->
                            <div class="relative w-full md:w-auto text-center md:text-left">
                                <input type="month" id="month-selector" class="w-auto pl-3 pr-3 py-2 text-sm border-none focus:outline-none focus:ring-0 bg-transparent text-gray-700 cursor-pointer font-medium text-lg" value="2026-02">
                            </div>
                            
                            <!-- Timezone Selector -->
                            <div class="relative w-full md:w-auto">
                                <select class="w-full appearance-none pl-3 pr-8 py-2 text-sm border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 rounded-md border text-gray-700 bg-white cursor-pointer">
                                     <option>America/Chicago - CST (-06:00)</option>
                                     <option>Asia/Kolkata - IST (+05:30)</option>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Days Scroller -->
                        <div class="relative min-h-[90px]">
                             <div id="calendar-loading" class="absolute inset-0 flex items-center justify-center bg-white z-20 hidden">
                                <div class="flex gap-2">
                                    <div class="min-w-[60px] h-[76px] rounded-lg bg-gray-200 animate-pulse"></div>
                                    <div class="min-w-[60px] h-[76px] rounded-lg bg-gray-200 animate-pulse" style="animation-delay: 0.1s"></div>
                                    <div class="min-w-[60px] h-[76px] rounded-lg bg-gray-200 animate-pulse" style="animation-delay: 0.2s"></div>
                                    <div class="min-w-[60px] h-[76px] rounded-lg bg-gray-200 animate-pulse" style="animation-delay: 0.3s"></div>
                                    <div class="min-w-[60px] h-[76px] rounded-lg bg-gray-200 animate-pulse" style="animation-delay: 0.4s"></div>
                                    <div class="min-w-[60px] h-[76px] rounded-lg bg-gray-200 animate-pulse" style="animation-delay: 0.5s"></div>
                                    <div class="min-w-[60px] h-[76px] rounded-lg bg-gray-200 animate-pulse" style="animation-delay: 0.6s"></div>
                                </div>
                             </div>
                             <button id="prev-week-btn" class="absolute left-2 top-[50%] -translate-y-1/2 z-10 bg-transparent p-2 text-gray-600 hover:text-gray-800 border-none flex items-center"><i data-lucide="chevron-left" class="w-6 h-6"></i></button>
                            <div class="flex gap-3 overflow-x-auto justify-start md:justify-center pb-2 min-h-[64px] px-12 sm:px-16 snap-x no-scrollbar" id="calendar-days-track">
                                <!-- Days injected -->
                            </div>
                             <button id="next-week-btn" class="absolute right-2 top-[50%] -translate-y-1/2 z-10 bg-transparent p-2 text-gray-600 hover:text-gray-800 border-none flex items-center"><i data-lucide="chevron-right" class="w-6 h-6"></i></button>
                        </div>

                        <!-- Slots -->
                        <div id="slots-container" class="min-h-[200px]">
                            <div class="text-center text-gray-500 py-10">Select a date to view availability</div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: User Details -->
                <div id="view-step-3" class="step-view hidden">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Please enter your details</h3>
                    
                    <form id="booking-form" class="space-y-4 max-w-md">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                <input type="text" name="first_name" required class="w-full border rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2" style="border-color: #d1d5db;">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                <input type="text" name="last_name" required class="w-full border rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2" style="border-color: #d1d5db;">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" required class="w-full border rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2" style="border-color: #d1d5db;">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Phone number *</label>
                            <div class="flex">
                                <select class="border bg-gray-50 text-gray-500 rounded-l-md border-r-0 p-2 text-sm w-16" style="border-color: #d1d5db;">
                                    <option>+1</option>
                                    <option>+91</option>
                                </select>
                                <input type="tel" name="phone" required placeholder="Contact Number" class="w-full border rounded-r-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2" style="border-color: #d1d5db;">
                            </div>
                        </div>

                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Of Birth *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="date" name="dob" required class="pl-10 w-full border rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2" style="border-color: #d1d5db;">
                            </div>
                        </div>

                        <div class="pt-4">
                             <button type="submit" id="btn-submit-booking" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                Schedule Appointment
                            </button>
                        </div>
                    </form>
                </div>

                <!-- STEP 4: Success -->
                <div id="view-step-success" class="step-view hidden text-center py-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-8">Appointment confirmed with <span id="confirm-doctor-name">Doctor</span>!</h2>
                    
                    <!-- Appointment Card -->
                    <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg p-6 shadow-sm relative">

                        
                        <div class="flex items-start gap-6">
                            <!-- Calendar Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-20 h-20 border-2 border-gray-200 rounded-lg flex flex-col items-center justify-center relative">
                                    <div class="absolute -top-1 left-3 right-3 h-2 bg-gray-200 rounded-t"></div>
                                    <div class="absolute -top-2 left-5 right-5 h-1 bg-gray-300 rounded-t"></div>
                                    <div class="mt-2">
                                        <i data-lucide="check" class="w-8 h-8 text-emerald-500"></i>
                                    </div>
                                </div>
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mt-2 ml-2"></div>
                            </div>
                            
                            <!-- Appointment Details -->
                            <div class="flex-1 text-left">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1" id="confirm-datetime">09 Feb 2026 | 07:30 pm</h3>
                                <p class="text-sm text-gray-600 mb-1" id="confirm-doctor-detail">Andrew Clary, DO</p>
                                <p class="text-xs text-gray-500" id="confirm-timezone">America/Chicago - CST (-06:00)</p>
                                

                            </div>
                        </div>
                    </div>
                    
                    <!-- Book Another Appointment Button -->
                    <button onclick="location.reload()" class="mt-8 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-all transform hover:-translate-y-0.5">
                        Book another appointment
                        <i data-lucide="arrow-right" class="ml-2 w-5 h-5"></i>
                    </button>
                </div>

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

        // --- Init ---
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            loadServices();
            setupCalendarListeners();
            
            // Validation / Form Submit
            document.getElementById('booking-form').addEventListener('submit', handleBookingSubmit);
        });

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
                
            } else {
                const stepView = document.getElementById(`view-step-${step}`);
                if (stepView) {
                    stepView.classList.remove('hidden');
                }
                
                // Normal layout
                document.getElementById('appointment-sidebar').classList.remove('hidden');
                document.getElementById('appointment-main-content').classList.add('md:w-2/3');
            }

            updateSidebar();
        }

        function updateSidebar() {
            const steps = [1, 2, 3];
            steps.forEach(s => {
                const el = document.getElementById(`step-${s}-trigger`);
                const icon = el.querySelector('i');
                const text = el.querySelector('span');
                
                // Reset click handler
                el.onclick = () => {
                     // Allow going back, or forward if already visited
                     if (s < state.step || s <= state.maxStep) {
                         setStep(s);
                     }
                };
                
                if (s === state.step) {
                    el.className = "p-4 border rounded-lg cursor-pointer transition-colors border-emerald-500 bg-emerald-50";
                    text.classList.remove('text-gray-600'); 
                    text.classList.add('text-emerald-700');
                    if(icon) {
                        icon.classList.remove('text-gray-400', 'text-emerald-600');
                        icon.classList.add('text-emerald-600');
                    }
                } else if (s < state.step) {
                    // Completed
                    el.className = "p-4 border rounded-lg cursor-pointer transition-colors border-green-100 bg-white hover:bg-green-50";
                    text.classList.remove('text-gray-600', 'text-emerald-700');
                    text.classList.add('text-gray-800');
                    if(icon) {
                        icon.classList.remove('text-gray-400', 'text-emerald-600');
                        icon.classList.add('text-emerald-600');
                    }
                } else {
                    // Future
                     el.className = "p-4 border rounded-lg cursor-not-allowed transition-colors border-gray-100 bg-gray-50 opacity-60";
                     text.classList.remove('text-emerald-700', 'text-gray-800');
                     text.classList.add('text-gray-400');
                     if(icon) {
                        icon.classList.remove('text-emerald-600');
                        icon.classList.add('text-gray-400');
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
                
                const el = document.createElement('div');
                el.className = "group flex items-center p-4 bg-white hover:bg-gray-50 cursor-pointer transition-all border-b border-gray-100 last:border-0";
                el.onclick = () => selectService(svc);
                
                el.innerHTML = `
                    <div class="flex-shrink-0 mr-4">
                        <img class="h-12 w-12 rounded-sm object-cover" src="${avatarUrl}" alt="${name}">
                    </div>
                    <div class="flex-1 min-w-0">
                         <h4 class="text-base font-semibold text-gray-800 truncate">${name}</h4>
                         <p class="text-xs text-gray-500 italic mt-0.5">${svc.description ? svc.description.split('Log in')[0] : 'Interventional Pain Medicine'}</p> 
                         <p class="text-xs text-gray-400 mt-1">Sage Spine Pain and Nerve Center</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex items-center gap-4">
                         <span class="text-xs text-gray-500">${svc.duration || '15 mins'}</span>
                         <div class="h-6 w-6 rounded-full border border-gray-300 flex items-center justify-center group-hover:bg-green-500 group-hover:border-green-500 transition-colors">
                            <i data-lucide="check" class="w-4 h-4 text-white opacity-0 group-hover:opacity-100"></i>
                         </div>
                    </div>
                `;
                container.appendChild(el);
            });
            lucide.createIcons();
        }

        async function selectService(svc) {
            state.selectedService = svc;
            state.selectedDate = null;
            state.selectedDateStr = null;
            state.selectedSlot = null;
            
            // Update booking message with doctor name
            const doctorNameEl = document.getElementById('selected-doctor-name');
            if (doctorNameEl) {
                doctorNameEl.textContent = svc.name || 'Doctor';
            }
            
            // Update summary
            document.getElementById('summary-service-name').textContent = svc.name || 'Service';
            
            // Move to Step 2 and load calendar
            setStep(2);
            
            // Initialize calendar for current month with this service
            let calendarDate = new Date(); // Declared with 'let' to avoid global scope if not intended
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
            const newDate = new Date(state.weekStartDate);
            newDate.setDate(newDate.getDate() + (direction * 7));
            
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
            if (slotsContainer) slotsContainer.classList.add('hidden');
            
            // Update month selector to reflect current week's month
            const monthSelector = document.getElementById('month-selector');
            if (monthSelector && state.weekStartDate) {
                const year = state.weekStartDate.getFullYear();
                const month = String(state.weekStartDate.getMonth() + 1).padStart(2, '0');
                monthSelector.value = `${year}-${month}`;
            }
            
            // Start both the data fetch and minimum display timer
            const minDisplayTime = new Promise(resolve => setTimeout(resolve, 400));
            
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
            
            // No need to update month label - it's in the dropdown now
            const start = new Date(state.weekStartDate);
            const end = new Date(state.weekStartDate);
            end.setDate(end.getDate() + 6);

            container.innerHTML = '';
            
            const todayStr = getLocalDateString(new Date());

            // Responsive Day Count: 5 for mobile, 7 for desktop
            const isMobile = window.innerWidth < 768;
            const daysToShow = isMobile ? 5 : 7;

            // Clear track classes and re-apply responsive gap/padding
            // Mobile: px-8 (less padding for arrows), gap-1 (tighter)
            // Desktop: px-16, gap-3
            container.className = `flex ${isMobile ? 'gap-2 px-9' : 'gap-3 px-16'} overflow-x-auto justify-center pb-2 min-h-[64px] snap-x no-scrollbar`;

            for (let i = 0; i < daysToShow; i++) {
                const date = new Date(state.weekStartDate);
                date.setDate(date.getDate() + i);
                
                const dateStr = getLocalDateString(date);
                const dayName = date.toLocaleString('default', { weekday: 'short' }).toUpperCase();
                const dayNum = date.getDate();
                
                const btn = document.createElement('button');
                btn.id = `day-btn-${dateStr}`;
                
                // Base Classes
                // Mobile: w-11 h-14 (smaller)
                // Desktop: w-16 h-16 (square 64px)
                let baseClass = "flex flex-col items-center justify-center rounded-lg transition-all relative ";
                
                if (isMobile) {
                    baseClass += "min-w-[46px] w-[46px] h-14 "; 
                } else {
                    baseClass += "min-w-[64px] w-16 h-16 ";
                }
                
                // Past Day Check
                const isPast = dateStr < todayStr;
                
                // Check if we have cached data for this day
                const cachedData = state.slotsCache[dateStr];
                const hasNoSlots = cachedData && 
                                   cachedData.serviceId === state.selectedService?.id && 
                                   (!Array.isArray(cachedData.slots) || cachedData.slots.length === 0);
                
                if (isPast) {
                    baseClass += "bg-gray-50 text-gray-300 cursor-not-allowed";
                    btn.disabled = true;
                } else if (hasNoSlots) {
                    // Day has no available slots
                    baseClass += "bg-white text-gray-300 cursor-not-allowed opacity-60";
                    btn.disabled = true;
                    btn.title = "No slots available";
                } else if (state.selectedDateStr === dateStr) {
                    // Selected: Green bg, white text. No border needed if bg is strong.
                    baseClass += "bg-emerald-500 text-white shadow-md transform scale-105";
                } else {
                    // Unselected: White bg, dark text, subtle border/shadow only?
                    baseClass += "bg-white text-gray-800 hover:bg-gray-50 cursor-pointer shadow-sm hover:shadow-md";
                }
                
                btn.className = baseClass;
                
                // Text Colors
                const dayNameClass = (state.selectedDateStr === dateStr && !isPast && !hasNoSlots) ? 'text-white/90' : 'text-gray-400';
                const dayNumClass = (state.selectedDateStr === dateStr && !isPast && !hasNoSlots) ? 'text-white' : 'text-gray-800';

                // Responsive Font Sizes
                // Mobile: text-lg number, text-[9px] name
                // Desktop: text-2xl number, text-[10px] name
                const numSize = isMobile ? 'text-lg' : 'text-2xl';
                const nameSize = isMobile ? 'text-[9px]' : 'text-[10px]';

                btn.innerHTML = `
                    <span class="${dayNumClass} ${numSize} font-bold leading-none mb-1">${dayNum}</span>
                    <span class="${dayNameClass} ${nameSize} uppercase font-medium tracking-wider">${dayName}</span>
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
            
            if (!slots || slots.length === 0) {
                 container.innerHTML = `<div class="text-center text-gray-500 py-6">No availability for this date.</div>`;
                 return;
            }
            
            // Normalize slots to array of time strings
            let slotList = [];
            if (Array.isArray(slots)) slotList = slots;
            else if (typeof slots === 'object') slotList = Object.keys(slots);

            // Group slots by time period
            const periods = { Morning: [], Afternoon: [], Evening: [], Night: [] };
            
            slotList.forEach(time => {
                // Parse time to determine period
                let hour = 0;
                
                // Handle different time formats
                if (time.includes(':')) {
                    const timeParts = time.split(':');
                    hour = parseInt(timeParts[0]);
                    
                    // If PM/AM format
                    if (time.toLowerCase().includes('pm') && hour !== 12) {
                        hour += 12;
                    } else if (time.toLowerCase().includes('am') && hour === 12) {
                        hour = 0;
                    }
                }
                
                // Categorize by time period
                if (hour >= 5 && hour < 12) {
                    periods.Morning.push(time);
                } else if (hour >= 12 && hour < 17) {
                    periods.Afternoon.push(time);
                } else if (hour >= 17 && hour < 21) {
                    periods.Evening.push(time);
                } else {
                    periods.Night.push(time);
                }
            });
            
            // Render each period with slots
            Object.keys(periods).forEach(periodName => {
                const periodSlots = periods[periodName];
                if (periodSlots.length === 0) return; // Skip empty periods
                
                // Period header
                const header = document.createElement('div');
                header.className = 'text-center text-gray-700 font-medium text-sm mb-4 mt-6 first:mt-0';
                header.textContent = periodName;
                container.appendChild(header);
                
                // Slots grid
                const grid = document.createElement('div');
                // Mobile: grid-cols-3 (Matches image)
                // Desktop (md+): grid-cols-4
                grid.className = 'grid grid-cols-3 md:grid-cols-4 gap-3';
                
                periodSlots.forEach(time => {
                    const btn = document.createElement('button');
                    btn.className = 'py-3 px-4 border border-black rounded text-sm text-gray-700 bg-white hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors';
                    btn.innerText = time;
                    btn.onclick = () => {
                        state.selectedSlot = time;
                        setStep(3);
                    };
                    grid.appendChild(btn);
                });
                
                container.appendChild(grid);
            });
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
                const additionalFields = {
                    "Date Of Birth": formattedDOB
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

    </script>

    <?php
    return ob_get_clean();
}
