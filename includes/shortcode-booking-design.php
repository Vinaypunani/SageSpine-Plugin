<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function render_sage_booking_design() {
    ob_start();
    // Pass necessary variables to JS
    $api_nonce = wp_create_nonce('wp_rest');
    $api_base = rest_url('sagespine/v1');
    ?>
    <!-- Load Tailwind CSS -->
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            important: true,
            corePlugins: {
                preflight: false,
            }
        }
    </script>
    <!-- Load Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Axios for easier HTTP requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Inline style removed: usage moved to style.css -->

    <div id="sage-booking-root" class="sage-isolate-scope">
    <div id="sage-booking-app" class="sage-booking-container w-full max-w-4xl mx-auto bg-white min-h-screen relative">
        
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="fixed inset-0 bg-white/80 z-50 flex items-center justify-center hidden">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
        </div>

        <!-- Error Message -->
        <div id="sage-booking-error" class="hidden p-10 text-center flex flex-col items-center justify-center min-h-[50vh]">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Booking Not Found</h3>
            <p class="text-gray-500 max-w-sm mb-6">We couldn't find the appointment details. The link might be expired or invalid.</p>
            <button onclick="window.location.href='<?php echo home_url('/'); ?>'" class="px-6 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800 transition-colors">
                Go Home
            </button>
        </div>

        <div id="sage-booking-content">
            <!-- 1. Top Card: Appointment Details -->
            <div class="mb-8">
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="p-5 border-b border-gray-100 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                             <img src="https://ui-avatars.com/api/?name=User&background=random" id="staff-avatar" alt="Staff" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-900 truncate" id="staff-name-main">Loading...</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" id="display-status">--</span>
                            </div>
                            <p class="text-sm text-gray-500 truncate" id="display-service">--</p>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="p-5 grid grid-cols-2 gap-y-4 gap-x-4 text-sm">
                        <!-- Row 1 -->
                        <div>
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-0.5">Booking ID</span>
                            <span class="block text-gray-900 font-medium" id="display-booking-id">--</span>
                        </div>
                        <div>
                             <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-0.5">Date & Time</span>
                            <span class="block text-gray-900 font-medium" id="display-datetime">--</span>
                        </div>

                        <!-- Row 2 -->
                        <div>
                             <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-0.5">Consultant</span>
                            <span class="block text-gray-900 font-medium" id="display-consultant">--</span>
                        </div>
                        <div>
                             <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-0.5">Booked On</span>
                            <span class="block text-gray-900 font-medium" id="display-booked-on">--</span>
                        </div>
                    </div>
                    
                    <!-- Contact Section -->
                    <div class="bg-gray-50 p-4 border-t border-gray-100">
                        <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-2">Patient Details</span>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-6 text-sm text-gray-700">
                            <span class="font-medium text-gray-900" id="display-customer-name">--</span>
                            <div class="flex items-center gap-4 mt-1 sm:mt-0 text-gray-500">
                                <span class="flex items-center gap-1.5" id="contact-phone-wrapper">
                                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                    <span id="display-customer-phone">--</span>
                                </span>
                                <span class="flex items-center gap-1.5" id="contact-email-wrapper">
                                    <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                                    <span id="display-customer-email">--</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reschedule To</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Date Input (Custom) -->
                    <div class="relative group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <div class="relative cursor-pointer" id="custom-date-trigger">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <input type="text" readonly id="display-date-input" class="pl-10 block w-full border-gray-300 rounded-md py-2.5 px-3 text-gray-900 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 border cursor-pointer" placeholder="Select Date">
                        </div>

                        <!-- Custom Calendar Dropdown -->
                        <div id="custom-calendar-dropdown" class="absolute top-full left-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 w-[320px] p-4 hidden z-50">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4">
                                <button id="prev-month-btn" class="p-1 hover:bg-gray-100 rounded-full text-gray-600" style="line-height: 1px !important; ">
                                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                                </button>
                                <span class="text-gray-900 font-medium text-base" id="calendar-current-month">--</span>
                                <button id="next-month-btn" class="p-1 hover:bg-gray-100 rounded-full text-gray-600" style="line-height: 1px !important;">
                                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                </button>
                            </div>
                            <!-- Days Header -->
                            <div class="grid grid-cols-7 mb-2 text-center">
                                <span class="text-xs text-gray-500 font-medium py-1">Mo</span>
                                <span class="text-xs text-gray-500 font-medium py-1">Tu</span>
                                <span class="text-xs text-gray-500 font-medium py-1">We</span>
                                <span class="text-xs text-gray-500 font-medium py-1">Th</span>
                                <span class="text-xs text-gray-500 font-medium py-1">Fr</span>
                                <span class="text-xs text-gray-500 font-medium py-1">Sa</span>
                                <span class="text-xs text-gray-500 font-medium py-1">Su</span>
                            </div>
                            <!-- Grid -->
                            <div class="grid grid-cols-7 gap-1 text-center" id="calendar-grid">
                                <!-- Days generated by JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Timezone Input -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time zone</label>
                        <div class="relative">
                            <select class="!h-[51px] block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md border text-gray-700">
                                 <option>Asia/Kolkata - IST (+05:30)</option>
                                 <option>America/New_York - EST (-05:00)</option>
                                 <option>UTC (+00:00)</option>
                            </select>
                             <!-- <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                            </div> -->
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-900 mb-4">Slot Availability</h3>
                 <!-- Divider -->
                <hr class="border-gray-100 mb-8">

                <div id="slots-container" class="space-y-8 min-h-[200px]">
                     <div class="text-center text-gray-500 py-10">Select a date above to view slots</div>
                </div>

                <!-- Action Buttons -->
                <div id="action-buttons-container" class="entry-content mt-8 border-t border-gray-200 pt-6">
                    <div class="flex h-16 gap-4">
                         <!-- Reschedule Button -->
                         <button id="confirm-reschedule-btn" class="flex-1 bg-[#2E8B57] text-white font-medium text-lg hover:bg-[#25734a] transition-colors rounded-md disabled:opacity-50 disabled:cursor-not-allowed">
                             Reschedule
                         </button>
                         <!-- Book Another Button -->
                         <a href="<?php echo home_url('/appointment-booking/'); ?>" class="flex-1 flex items-center justify-center bg-gray-100 text-gray-700 font-medium text-lg hover:bg-gray-200 transition-colors rounded-md text-center no-underline" style="padding: 0 34px !important;">
                             Book Another
                         </a>
                    </div>
                </div>

            </div>
    </div>
        </div>

    </div>
    
    <script>
        const SAGE_API_BASE = "<?php echo $api_base; ?>";
        const SAGE_NONCE = "<?php echo $api_nonce; ?>";
        const HOME_URL = '<?php echo home_url('/'); ?>';
        
        let currentState = {
            bookingId: null,
            staffId: null,
            serviceId: null,
            selectedDateStr: null, // YYYY-MM-DD
            selectedSlot: null,
            appointmentDetails: null
        };
        
        // Calendar State
        let calendarState = {
            currentDate: new Date(), // For navigation
            selectedDate: new Date() // Currently selected
        };

        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Helper: Date to YYYY-MM-DD
        function toISODate(date) {
            const offset = date.getTimezoneOffset(); 
            date = new Date(date.getTime() - (offset*60*1000));
            return date.toISOString().split('T')[0];
        }

        // Helper: Format for API: dd-MMM-yyyy
        function formatDateForApi(isoDateStr) {
            const d = new Date(isoDateStr);
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const day = String(d.getUTCDate()).padStart(2, '0');
            const month = months[d.getUTCMonth()];
            const year = d.getUTCFullYear();
            return `${day}-${month}-${year}`;
        }
        
        function formatDisplayDate(date) {
             const d = new Date(date);
             const day = d.getDate();
             const month = d.toLocaleString('default', { month: 'short' });
             const year = d.getFullYear();
             return `${day} ${month} ${year}`;
        }

        function showLoading(show) {
            const el = document.getElementById('loading-overlay');
            if(show) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }

        // --- Custom Calendar Logic ---
        function initCalendar() {
            const trigger = document.getElementById('custom-date-trigger');
            const dropdown = document.getElementById('custom-calendar-dropdown');
            const prevBtn = document.getElementById('prev-month-btn');
            const nextBtn = document.getElementById('next-month-btn');

            trigger.addEventListener('click', (e) => {
                dropdown.classList.toggle('hidden');
                e.stopPropagation();
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if(!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            prevBtn.addEventListener('click', () => {
                calendarState.currentDate.setMonth(calendarState.currentDate.getMonth() - 1);
                renderCalendar();
            });

            nextBtn.addEventListener('click', () => {
                calendarState.currentDate.setMonth(calendarState.currentDate.getMonth() + 1);
                renderCalendar();
            });

            renderCalendar();
        }

        function renderCalendar() {
            const grid = document.getElementById('calendar-grid');
            const header = document.getElementById('calendar-current-month');
            const now = new Date();
            
            const year = calendarState.currentDate.getFullYear();
            const month = calendarState.currentDate.getMonth();
            
            header.innerText = calendarState.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
            grid.innerHTML = '';

            const firstDay = new Date(year, month, 1).getDay(); // 0 = Sun
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            // Adjust for Monday start (standard in provided image: Mo Tu We...)
            // Native getDay(): 0(Sun), 1(Mon)...
            // We want 0(Mon)... 6(Sun)
            let startDay = firstDay === 0 ? 6 : firstDay - 1;

            // Empty slots for previous month
            for(let i=0; i<startDay; i++) {
                const emptyCell = document.createElement('div');
                grid.appendChild(emptyCell);
            }

            // Days
            for(let day=1; day<=daysInMonth; day++) {
                const dayDate = new Date(year, month, day);
                const dayStr = toISODate(dayDate);
                const cell = document.createElement('div');
                cell.className = "text-center py-2 text-sm rounded-md cursor-pointer hover:bg-gray-100 text-gray-700 transition-colors";
                cell.innerText = day;
                
                // Active State (Green)
                if(currentState.selectedDateStr === dayStr) {
                    cell.className = "text-center py-2 text-sm rounded-md cursor-pointer bg-[#2E8B57] text-white font-medium shadow-md";
                }
                
                // Styling for today (if needed)
                if(dayStr === toISODate(now)) {
                    cell.classList.add('font-bold');
                }

                cell.addEventListener('click', () => {
                    selectDate(dayDate);
                });

                grid.appendChild(cell);
            }
        }

        function selectDate(date) {
            currentState.selectedDateStr = toISODate(date);
            document.getElementById('display-date-input').value = formatDisplayDate(date);
            document.getElementById('custom-calendar-dropdown').classList.add('hidden');
            
            // Force rerender to show active state
            calendarState.selectedDate = date;
            renderCalendar(); 
            
            // Trigger slots fetch
            currentState.selectedSlot = null;
            fetchAvailableSlots(currentState.selectedDateStr);
        }

        function showErrorState(message) {
             document.getElementById('sage-booking-content').classList.add('hidden');
             document.getElementById('sage-booking-error').classList.remove('hidden');
             if(message) {
                 // Optionally update error text
             }
        }

        async function fetchAppointmentDetails(bookingId) {
            showLoading(true);
            try {
                // Add timestamp to prevent caching
                const response = await axios.get(`${SAGE_API_BASE}/appointment?booking_id=${bookingId}&_t=${new Date().getTime()}`);
                console.log("Appointment API Response:", response.data); // Debug log

                const data = response.data.response; 

                // Check for returnvalue AND ensure it has essential data (e.g., start_time or staff_id)
                // Zoho sometimes returns "success" status but empty data if ID is invalid but format is correct.
                if (data && data.returnvalue && typeof data.returnvalue === 'object' && (data.returnvalue.staff_id || data.returnvalue.start_time)) {
                    const booking = data.returnvalue;
                    currentState.appointmentDetails = booking;
                    currentState.staffId = booking.staff_id;
                    currentState.serviceId = booking.service_id;
                    
                    // Update Top Card
                    document.getElementById('staff-name-main').innerText = booking.staff_name || 'Staff Member';
                    document.getElementById('display-booking-id').innerText = bookingId;
                    document.getElementById('display-consultant').innerText = booking.staff_name || '--';
                    
                    // Format Date & Time: dd-MMM-yyyy HH:mm:ss -> dd-MMM-yyyy hh:mm a
                    const formatDateTime = (str) => {
                        if (!str) return '--';
                        const d = new Date(str);
                        if (isNaN(d.getTime())) return str; // Fallback if parse fails
                        
                        const datePart = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).replace(/ /g, '-');
                        const timePart = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                        return `${datePart} ${timePart}`;
                    };

                    document.getElementById('display-datetime').innerText = formatDateTime(booking.start_time);
                    document.getElementById('staff-avatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(booking.staff_name || 'User')}&background=random`;
                    
                    // New Fields
                    const statusEl = document.getElementById('display-status');
                    statusEl.innerText = booking.status || '--';
                    
                    // Reset classes - BOLDER styling
                    statusEl.className = "px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wide shadow-sm"; 

                    const status = (booking.status || '').toLowerCase();
                    if (status === 'upcoming') {
                        statusEl.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
                    } else if (status === 'completed') {
                         statusEl.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
                    } else if (status.includes('cancel')) {
                         statusEl.classList.add('bg-red-100', 'text-red-700', 'border-red-200');
                    } else {
                         statusEl.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
                    }
                    
                    // Hide Buttons if Cancelled or Completed
                    const actionBtns = document.getElementById('action-buttons-container');
                    if (status.includes('cancel') || status === 'completed' || status === 'noshow') {
                        if(actionBtns) actionBtns.classList.add('hidden');
                    } else {
                        if(actionBtns) actionBtns.classList.remove('hidden');
                    }

                    document.getElementById('display-service').innerText = (booking.service_name || 'Service') + (booking.duration ? ` · ${booking.duration}` : '');
                    document.getElementById('display-booked-on').innerText = booking.booked_on || '--';
                    
                    document.getElementById('display-customer-name').innerText = booking.customer_name || '--';
                    document.getElementById('display-customer-phone').innerText = booking.customer_contact_no || '';
                    document.getElementById('display-customer-email').innerText = booking.customer_email || '';

                    // Initial Load
                    const today = new Date();
                    selectDate(today);

                } else {
                    console.error("Invalid appointment data for ID:", bookingId);
                    console.error("Full API Data:", data);
                    const msg = (data && data.message) ? data.message : "Invalid Data Structure";
                    
                    const errorDiv = document.getElementById('sage-booking-error');
                    errorDiv.querySelector('p').innerText = `API Error: ${msg}. ID: ${bookingId}`;
                    errorDiv.querySelector('p').innerHTML += `<br/><div class='mt-2 text-xs text-left bg-gray-100 p-2 rounded overflow-auto max-h-32'>${JSON.stringify(data)}</div>`;
                    
                    showErrorState();
                }
            } catch (error) {
                console.error("Error fetching appointment", error);
                showErrorState();
            } finally {
                showLoading(false);
            }
        }

        async function fetchAvailableSlots(isoDateStr) {
            if (!currentState.serviceId || !currentState.staffId || !isoDateStr) return;
            
            showLoading(true);
            const apiDate = formatDateForApi(isoDateStr);
            const container = document.getElementById('slots-container');
            container.innerHTML = `<div class="text-center text-gray-500">Loading availability...</div>`;

            try {
                const response = await axios.get(`${SAGE_API_BASE}/slots`, {
                    params: {
                        service_id: currentState.serviceId,
                        staff_id: currentState.staffId,
                        selected_date: apiDate
                    }
                });
                
                let slots = [];
                if (response.data && response.data.response && response.data.response.returnvalue && response.data.response.returnvalue.data) {
                     slots = response.data.response.returnvalue.data;
                } else if (Array.isArray(response.data)) {
                    slots = response.data;
                }
                
                renderSlots(slots);

            } catch (error) {
                console.error("Error fetching slots", error);
                container.innerHTML = `<div class="text-center text-red-500">Error loading slots.</div>`;
            } finally {
                showLoading(false);
            }
        }

        function renderSlots(slots) {
            const container = document.getElementById('slots-container');
            container.innerHTML = '';

            if (!slots || (Array.isArray(slots) && slots.length === 0)) {
                container.innerHTML = `<div class="text-center text-gray-500">No slots available on this date.</div>`;
                return;
            }

            let slotList = [];
            
            if (Array.isArray(slots)) {
                slotList = slots;
            } else if (typeof slots === 'object') {
                // Check if keys are times (contain ':')
                const keys = Object.keys(slots);
                const firstKey = keys[0];
                if (firstKey && firstKey.includes(':')) {
                    slotList = keys;
                } else {
                    // Assume values are times (e.g. numeric keys)
                    slotList = Object.values(slots);
                }
            }
            
            if (slotList.length === 0) {
                 container.innerHTML = `<div class="text-center text-gray-500">No slots available.</div>`;
                 return;
            }

            const morningSlots = [];
            const afternoonSlots = [];

            slotList.forEach(time => {
                if(typeof time !== 'string') return;
                
                // Formatter Logic
                let displayTime = time;
                let isPm = false;

                const lowerTime = time.toLowerCase();

                if (lowerTime.includes('pm')) {
                    isPm = true;
                    displayTime = time; // Trust existing format
                } else if (lowerTime.includes('am')) {
                    isPm = false;
                    displayTime = time; // Trust existing format
                } else {
                    // Assume 24-hour format if no suffix
                    const parts = time.split(':');
                    if(parts.length >= 2) {
                        const h = parseInt(parts[0]);
                        const m = parts[1];
                        
                        isPm = h >= 12;
                        
                        const displayHour = h % 12 || 12; // 0 -> 12, 13 -> 1
                        const suffix = isPm ? 'pm' : 'am';
                        displayTime = `${String(displayHour).padStart(2,'0')}:${m} ${suffix}`;
                    }
                }
                
                // Allow "12:00 pm" to be Afternoon, "12:00 am" to be Morning
                
                if (isPm) {
                    afternoonSlots.push({ raw: time, display: displayTime });
                } else {
                    morningSlots.push({ raw: time, display: displayTime });
                }
            });

            const createSlotGrid = (title, times) => {
                if (times.length === 0) return '';
                let html = `<div class="mb-8 relative"><div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-gray-100"></div></div><div class="relative flex justify-center"><span class="bg-white px-2 text-sm text-gray-500">${title}</span></div></div>`;
                
                html += `<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 mt-4">`;
                times.forEach(slot => {
                    html += `<button onclick="selectSlot(this, '${slot.raw}')" class="sage-slot-btn py-2 px-1 bg-white border border-gray-600 rounded text-sm font-medium text-gray-600 hover:border-[#2E8B57] hover:text-[#2E8B57] transition-all" style="box-shadow: none !important;" data-time="${slot.raw}">${slot.display}</button>`;
                });
                html += `</div>`;
                return html;
            };

            container.innerHTML += createSlotGrid('Morning', morningSlots);
            container.innerHTML += createSlotGrid('Afternoon', afternoonSlots);
        }

        window.selectSlot = function(el, rawTime) {
            currentState.selectedSlot = rawTime;
            document.querySelectorAll('.sage-slot-btn').forEach(btn => btn.classList.remove('sage-active'));
            el.classList.add('sage-active');
        };

        // Helper to convert 12h/24h time string to "HH:mm:ss"
        function convertTo24Hour(timeStr) {
            if (!timeStr) return '';
            
            // Normalize
            const lower = timeStr.toLowerCase().trim();
            const isPm = lower.includes('pm');
            const isAm = lower.includes('am');
            
            // Remove suffixes
            let cleanTime = lower.replace('pm', '').replace('am', '').trim();
            let [hours, minutes] = cleanTime.split(':');
            
            hours = parseInt(hours, 10);
            
            // 24-hour format assumption if no suffix and hours > 12
            if (!isPm && !isAm) {
                return `${String(hours).padStart(2, '0')}:${minutes}:00`;
            }

            if (isPm && hours < 12) {
                hours += 12;
            } else if (isAm && hours === 12) {
                hours = 0;
            }
            
            return `${String(hours).padStart(2, '0')}:${minutes}:00`;
        }

        // Reschedule Click
        document.getElementById('confirm-reschedule-btn').addEventListener('click', async () => {
            if (!currentState.bookingId) return alert("Booking ID is missing.");
            if (!currentState.selectedDateStr) return alert("Please select a date.");
            if (!currentState.selectedSlot) return alert("Please select a time slot.");

            if(!confirm("Confirm rescheduling to " + currentState.selectedDateStr + " at " + currentState.selectedSlot + "?")) return;

            showLoading(true);
            
            // FIX: Ensure time is in 24-hour format for API
            const time24 = convertTo24Hour(currentState.selectedSlot);
            const startTime = `${currentState.selectedDateStr} ${time24}`;

            try {
                const response = await axios.post(`${SAGE_API_BASE}/reschedule`, {
                    booking_id: currentState.bookingId,
                    staff_id: currentState.staffId,
                    start_time: startTime
                });

                const result = response.data;
                // Zoho API success check might need refinement based on exact response structure
                const isSuccess = 
                    (result?.response?.returnvalue?.status === 'success') || 
                    (result?.response?.status === 'success') || 
                    (result?.status === 'success') ||
                    (result?.response?.returnvalue?.message && result.response.returnvalue.message.toLowerCase().includes('successfully'));

                if (isSuccess) {
                    alert("Rescheduled Successfully!");
                    // Add delay to allow Zoho to process the update
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    console.log("Reschedule result:", result);
                    const msg = result.response?.returnvalue?.message ||  result.message || "Please check details.";
                    alert("Reschedule failed: " + msg + "\nDebug: " + JSON.stringify(result)); 
                }

            } catch (error) {
                 console.error("Error rescheduling", error);
                 alert("An error occurred.");
            } finally {
                showLoading(false);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
             if (window.lucide) lucide.createIcons();
             initCalendar();

             const bookingId = getQueryParam('booking_id');
             if (bookingId) {
                 currentState.bookingId = bookingId;
                 fetchAppointmentDetails(bookingId);
             } else {
                 showErrorState();
             }
        });
    </script>
    <?php
    return ob_get_clean();
}
