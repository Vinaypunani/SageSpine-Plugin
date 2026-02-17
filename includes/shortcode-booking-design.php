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
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <style>
        /* Scoped Styles for Sage Booking App */
        #sage-booking-app {
            font-family: 'Inter', sans-serif;
            color: #111827;
            background-color: #F9FAFB;
            line-height: 1.5;
            box-sizing: border-box;
            width: 100%;
        }

        #sage-booking-app * {
            box-sizing: border-box;
        }

        /* Utility Classes (Scoped) */
        #sage-booking-app .sage-hidden { display: none !important; }
        #sage-booking-app .sage-flex { display: flex; }
        #sage-booking-app .sage-flex-col { flex-direction: column; }
        #sage-booking-app .sage-items-center { align-items: center; }
        #sage-booking-app .sage-justify-between { justify-content: space-between; }
        #sage-booking-app .sage-justify-center { justify-content: center; }
        #sage-booking-app .sage-gap-2 { gap: 0.5rem; }
        #sage-booking-app .sage-gap-4 { gap: 1rem; }
        #sage-booking-app .sage-gap-6 { gap: 1.5rem; }
        #sage-booking-app .sage-grid { display: grid; }
        #sage-booking-app .sage-w-full { width: 100%; }

        /* Colors - Light Mode Only */
        #sage-booking-app {
            --sage-primary: #27AE60;
            --sage-primary-dark: #219150;
            --sage-bg-light: #F9FAFB;
            --sage-card-light: #FFFFFF;
            --sage-text-light: #111827;
            --sage-border-light: #E5E7EB;
            --sage-summary-light: #F3F4F6;
        }

        /* Main Container */
        #sage-booking-app .sage-main {
            max-width: 48rem; /* max-w-3xl */
            margin: 0 auto;
            padding: 1rem;
        }

        /* Typography */
        #sage-booking-app h1 { font-size: 1.875rem; font-weight: 700; text-align: center; margin-bottom: 2rem; color: inherit; }
        #sage-booking-app h2 { font-size: 1.25rem; font-weight: 700; margin: 0; color: inherit; }
        #sage-booking-app h3 { font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; color: inherit; }
        #sage-booking-app p { margin: 0; }
        #sage-booking-app .sage-text-sm { font-size: 0.875rem; }
        #sage-booking-app .sage-text-xs { font-size: 0.75rem; }
        #sage-booking-app .sage-text-muted { color: #6B7280; }

        /* Cards */
        #sage-booking-app .sage-card {
            background-color: var(--sage-summary-light);
            border: 1px solid var(--sage-border-light);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            height: fit-content;
        }

        /* Avatar */
        #sage-booking-app .sage-avatar {
            width: 4rem; height: 4rem;
            border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            background-color: #FBBF24; /* amber-400 */
            color: white; font-weight: 700; font-size: 1.5rem;
        }
        #sage-booking-app .sage-avatar img {
            width: 100%; height: 100%; object-fit: cover;
        }

        /* Badge */
        #sage-booking-app .sage-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        #sage-booking-app .sage-badge-upcoming {
            background-color: #DBEAFE; color: #1D4ED8;
        }

        /* Grid Info */
        #sage-booking-app .sage-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding-top: 1.5rem;
            margin-top: 2rem;
            border-top: 1px solid var(--sage-border-light);
        }
        @media (min-width: 640px) {
            #sage-booking-app .sage-info-grid { grid-template-columns: 1fr 1fr; }
            #sage-booking-app .sm\:sage-flex-row { flex-direction: row; }
            #sage-booking-app .sm\:sage-items-center { align-items: center; }
        }
        @media (min-width: 768px) {
            #sage-booking-app .md\:sage-flex-row { flex-direction: row; }
            #sage-booking-app .md\:sage-w-2\/5 { width: 40%; }
            #sage-booking-app .md\:sage-flex-1 { flex: 1 1 0%; }
        }

        /* Labels */
        #sage-booking-app .sage-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #9CA3AF;
            margin-bottom: 0.25rem;
            display: block;
        }

        /* Buttons */
        #sage-booking-app .sage-btn {
            padding: 1rem;
            border-radius: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            border: 1px solid transparent;
            width: 100%;
            display: block;
            font-family: inherit;
        }
        #sage-booking-app .sage-btn-primary {
            background-color: var(--sage-primary);
            color: white;
            box-shadow: 0 10px 15px -3px rgba(39, 174, 96, 0.3);
        }
        #sage-booking-app .sage-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(39, 174, 96, 0.4);
        }
        #sage-booking-app .sage-btn-secondary {
            background-color: #F3F4F6;
            color: #4B5563;
            border-color: #E5E7EB;
        }
        #sage-booking-app .sage-btn-secondary:hover {
            background-color: #E5E7EB;
        }

        /* Calendar Wrapper */
        #sage-booking-app .sage-calendar-input-wrapper {
            background-color: var(--sage-card-light);
            border: 1px solid var(--sage-border-light);
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        /* Calendar Grid */
        #sage-booking-app .sage-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.25rem;
            text-align: center;
        }
        #sage-booking-app .sage-calendar-day-header {
            font-size: 0.75rem;
            font-weight: 700;
            color: #9CA3AF;
            padding-bottom: 0.5rem;
        }
        #sage-booking-app .sage-day-btn {
            padding: 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        #sage-booking-app .sage-day-btn:hover {
            background-color: #F3F4F6;
        }
        #sage-booking-app .sage-day-btn.selected {
            background-color: var(--sage-primary);
            color: white;
            font-weight: 700;
        }
        #sage-booking-app .sage-day-btn.disabled {
            opacity: 0.4;
            pointer-events: none;
            cursor: not-allowed;
        }

        /* Time Slots */
        #sage-booking-app .sage-slot-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            #sage-booking-app .sage-slot-grid { grid-template-columns: repeat(3, 1fr); }
        }
        #sage-booking-app .sage-slot-btn {
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid var(--sage-border-light);
            border-radius: 0.75rem;
            background-color: var(--sage-card-light);
            color: inherit;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        #sage-booking-app .sage-slot-btn:hover, #sage-booking-app .sage-slot-btn.selected {
            border-color: var(--sage-primary);
            color: var(--sage-primary);
        }
        #sage-booking-app .sage-slot-btn.selected {
            background-color: rgba(39, 174, 96, 0.05);
            border-width: 2px;
        }

        /* Icons */
        .material-symbols-outlined {
            vertical-align: middle;
            font-size: 1.25rem;
            user-select: none;
        }
        .text-primary { color: var(--sage-primary) !important; }

        /* Loader */
         #loading-overlay {
            position: fixed; inset: 0; background-color: rgba(255,255,255,0.8);
            z-index: 50; display: flex; align-items: center; justify-content: center;
        }
        .sage-spinner {
            border: 3px solid #E5E7EB; border-top-color: var(--sage-primary); border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    </style>

    <div id="sage-booking-root">
        <div id="sage-booking-app" class="">
            
            <main class="sage-main">
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="sage-hidden">
                    <div class="sage-spinner" style="width: 3rem; height: 3rem; border-width: 4px;"></div>
                </div>

                 <!-- Error Screen -->
                <div id="sage-booking-error" class="sage-hidden" style="text-align: center; padding: 3rem 0;">
                    <div style="background-color: #FEE2E2; color: #EF4444; width: 4rem; height: 4rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <span class="material-symbols-outlined">error</span>
                    </div>
                    <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Booking Not Found</h2>
                    <p class="sage-text-muted" style="margin-bottom: 1.5rem;">We couldn't find the appointment details.</p>
                </div>

                <div id="sage-booking-content">
                    <h1>Reschedule Appointment</h1>

                    <!-- Appointment Details Card -->
                    <div class="sage-card">
                        <div class="sage-flex sage-flex-col sm:sage-flex-row sm:sage-items-center sage-justify-between sage-gap-4">
                            <div class="sage-flex sage-items-center sage-gap-4">
                                <div class="sage-avatar">
                                    <img id="staff-avatar-img" src="" alt="Staff" class="sage-hidden">
                                    <span id="staff-initials">--</span>
                                </div>
                                <div>
                                    <h2 id="staff-name-main">Loading...</h2>
                                    <p class="sage-text-muted sage-text-sm" id="display-service-info">--</p>
                                </div>
                            </div>
                            <div>
                                <span class="sage-badge sage-badge-upcoming" id="display-status">Upcoming</span>
                            </div>
                        </div>

                        <div class="sage-info-grid">
                            <div>
                                <span class="sage-label">Booking ID</span>
                                <span class="sage-font-medium" id="display-booking-id">--</span>
                            </div>
                            <div>
                                <span class="sage-label">Date & Time</span>
                                <span class="sage-font-medium" id="display-datetime">--</span>
                            </div>
                            <div>
                                <span class="sage-label">Consultant</span>
                                <span class="sage-font-medium" id="display-consultant">--</span>
                            </div>
                            <div>
                                <span class="sage-label">Booked On</span>
                                <span class="sage-font-medium" id="display-booked-on">--</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reschedule Section Header -->
                    <h3 style="color: inherit;">
                        <span class="material-symbols-outlined text-primary">event</span> Reschedule To
                    </h3>

                    <!-- Reschedule Columns -->
                    <!-- Removed padding-top from right column and moved header out for alignment -->
                    <div class="sage-flex sage-flex-col md:sage-flex-row sage-gap-6">
                        <!-- Left: Calendar -->
                        <div class="sage-w-full md:sage-w-2/5">
                            <label class="sage-text-sm sage-font-bold sage-text-muted" style="display:block; margin-bottom:0.5rem;">Select Date</label>
                            
                            <div class="sage-calendar-input-wrapper">
                                <div class="sage-flex sage-justify-between sage-items-center" style="margin-bottom: 1rem;">
                                    <span style="font-weight: 700; font-size: 0.875rem;" id="calendar-current-month">--</span>
                                    <div class="sage-flex sage-gap-2">
                                        <button id="prev-month-btn" style="background:none; border:none; cursor:pointer; color:inherit; padding:0.25rem;"><span class="material-symbols-outlined">chevron_left</span></button>
                                        <button id="next-month-btn" style="background:none; border:none; cursor:pointer; color:inherit; padding:0.25rem;"><span class="material-symbols-outlined">chevron_right</span></button>
                                    </div>
                                </div>

                                <div class="sage-calendar-grid sage-calendar-day-header">
                                    <span>SU</span><span>MO</span><span>TU</span><span>WE</span><span>TH</span><span>FR</span><span>SA</span>
                                </div>
                                <div id="calendar-grid" class="sage-calendar-grid">
                                    <!-- JS Generated -->
                                </div>
                                <input type="hidden" id="display-date-input">
                            </div>
                        </div>

                        <!-- Right: Time Slots -->
                        <div class="sage-w-full md:sage-flex-1">
                             <label class="sage-text-sm sage-font-bold sage-text-muted" style="display:block; margin-bottom:0.5rem;">Select Time</label>
                             
                             <div id="slots-container" style="min-height: 200px;">
                                <div class="sage-text-muted" style="text-align: center; padding-top: 2rem;">Select a date to view availability</div>
                             </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="sage-flex sage-flex-col sm:sage-flex-row sage-gap-4" style="margin-top: 3rem;">
                        <button id="confirm-reschedule-btn" class="sage-btn sage-btn-primary">
                            Reschedule
                        </button>
                        <a href="<?php echo home_url('/appointment-booking/'); ?>" class="sage-btn sage-btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                            Book Another
                        </a>
                    </div>

                </div>
            </main>
            <!-- Footer Removed -->
        </div>
    </div>

    <script>
        const SAGE_API_BASE = "<?php echo $api_base; ?>";
        const SAGE_NONCE = "<?php echo $api_nonce; ?>";
        
        let currentState = {
            bookingId: null,
            staffId: null,
            serviceId: null,
            selectedDateStr: null,
            selectedSlot: null,
            appointmentDetails: null,
            isFetchingSlots: false // Added flag
        };
        
        let calendarState = {
            currentDate: new Date(),
            selectedDate: new Date()
        };

        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        function toISODate(date) {
            const offset = date.getTimezoneOffset(); 
            date = new Date(date.getTime() - (offset*60*1000));
            return date.toISOString().split('T')[0];
        }

        function formatDateForApi(isoDateStr) {
            const d = new Date(isoDateStr);
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const day = String(d.getUTCDate()).padStart(2, '0');
            const month = months[d.getUTCMonth()];
            const year = d.getUTCFullYear();
            return `${day}-${month}-${year}`;
        }

        function showLoading(show) {
            const el = document.getElementById('loading-overlay');
            if(show) el.classList.remove('sage-hidden');
            else el.classList.add('sage-hidden');
        }

        function showErrorState(message) {
             document.getElementById('sage-booking-content').classList.add('sage-hidden');
             document.getElementById('sage-booking-error').classList.remove('sage-hidden');
        }

        // --- Calendar Logic ---
        function initCalendar() {
             document.getElementById('prev-month-btn').addEventListener('click', () => {
                if(currentState.isFetchingSlots) return; // Block nav if fetching
                calendarState.currentDate.setMonth(calendarState.currentDate.getMonth() - 1);
                renderCalendar();
            });

            document.getElementById('next-month-btn').addEventListener('click', () => {
                if(currentState.isFetchingSlots) return; // Block nav if fetching
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
            
            // Empty slots
            for(let i=0; i<firstDay; i++) {
                grid.appendChild(document.createElement('div'));
            }

            // Days
            for(let day=1; day<=daysInMonth; day++) {
                const dayDate = new Date(year, month, day);
                const dayStr = toISODate(dayDate);
                const cell = document.createElement('div');
                cell.className = "sage-day-btn";
                cell.innerText = day;

                // Disable logic
                if (currentState.isFetchingSlots) {
                    cell.classList.add('disabled');
                } else {
                     if(currentState.selectedDateStr === dayStr) {
                        cell.classList.add('selected');
                    }
                    
                    cell.addEventListener('click', () => {
                        if (currentState.isFetchingSlots) return;

                        currentState.selectedDateStr = dayStr;
                        currentState.selectedSlot = null;
                        calendarState.selectedDate = dayDate;
                        renderCalendar(); 
                        fetchAvailableSlots(dayStr);
                    });
                }

                grid.appendChild(cell);
            }
        }

        // --- API: Staffs (for images) ---
        async function loadStaffs() {
            try {
                const res = await axios.get(`${SAGE_API_BASE}/staffs`);
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

        // --- Appointment Details Fetching ---
        async function fetchAppointmentDetails(bookingId) {
            showLoading(true);
            try {
                // Fetch appointment details
                const response = await axios.get(`${SAGE_API_BASE}/appointment?booking_id=${bookingId}&_t=${new Date().getTime()}`);
                const data = response.data.response;

                if (data && data.returnvalue) {
                    const booking = data.returnvalue;
                    currentState.appointmentDetails = booking;
                    currentState.staffId = booking.staff_id;
                    currentState.serviceId = booking.service_id;
                    
                    document.getElementById('staff-name-main').innerText = booking.staff_name || 'Staff Member';
                    
                    // Avatar / Image Logic
                    const avatarImg = document.getElementById('staff-avatar-img');
                    const initialsEl = document.getElementById('staff-initials');
                    
                    // Fetch Staffs to get the image
                    let imageUrl = booking.staff_image || booking.service_image || booking.image_url;
                    
                    // If not directly on booking, look up in staff list
                    if (!imageUrl && booking.staff_id) {
                        const staffs = await loadStaffs();
                        const staff = staffs.find(s => s.id == booking.staff_id);
                        if (staff && staff.photo) {
                            imageUrl = staff.photo;
                        }
                    }

                    if (imageUrl) {
                        avatarImg.src = imageUrl;
                        avatarImg.classList.remove('sage-hidden');
                        initialsEl.classList.add('sage-hidden');
                    } else {
                        // Fallback to initials
                        const nameParts = (booking.staff_name || 'Sage Spine').split(' ');
                        const initials = nameParts.map(n => n[0]).join('').substring(0,2).toUpperCase();
                        initialsEl.innerText = initials;
                        initialsEl.classList.remove('sage-hidden');
                        avatarImg.classList.add('sage-hidden');
                    }

                    document.getElementById('display-booking-id').innerText = bookingId;
                    document.getElementById('display-consultant').innerText = booking.staff_name || '--';
                    document.getElementById('display-service-info').innerText = (booking.staff_name || '') + (booking.duration ? ` • ${booking.duration}` : '');
                    
                    const formatDateTime = (str) => {
                        if (!str) return '--';
                        const d = new Date(str);
                        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + 
                               d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                    };
                    document.getElementById('display-datetime').innerText = formatDateTime(booking.start_time);
                    document.getElementById('display-booked-on').innerText = booking.booked_on || '--';

                    const statusEl = document.getElementById('display-status');
                    statusEl.innerText = booking.status || 'Upcoming';

                    // Initial Select Date
                    const today = new Date();
                    currentState.selectedDateStr = toISODate(today); 
                    renderCalendar();
                } else {
                    showErrorState();
                }
            } catch (error) {
                console.error(error);
                showErrorState();
            } finally {
                showLoading(false);
            }
        }

        // --- Slots Fetching ---
        async function fetchAvailableSlots(isoDateStr) {
             if (!currentState.serviceId || !currentState.staffId || !isoDateStr) return;
             
             // Set loading state
             currentState.isFetchingSlots = true;
             renderCalendar(); // Re-render to disable days

             const container = document.getElementById('slots-container');
             // Show spinner
             container.innerHTML = `<div style="display:flex; justify-content:center; padding-top:2rem;"><div class="sage-spinner" style="width:2rem; height:2rem;"></div></div>`;
             
             try {
                const apiDate = formatDateForApi(isoDateStr);
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
             } catch(e) {
                 container.innerHTML = `<div style="text-align: center; color: #EF4444;">Error loading slots.</div>`;
             } finally {
                 // Clear loading state
                 currentState.isFetchingSlots = false;
                 renderCalendar(); // Re-render to enable days
             }
        }

        function renderSlots(slots) {
            const container = document.getElementById('slots-container');
            container.innerHTML = '';
            
            // Normalize slots
            let slotList = [];
            if (Array.isArray(slots)) {
                 slotList = slots;
            } else if (typeof slots === 'object') {
                 slotList = Object.values(slots);
            }

            if (!slotList || slotList.length === 0) {
                container.innerHTML = `<div style="text-align: center; color: #6B7280;">No slots available.</div>`;
                return;
            }

            const morningSlots = [];
            const afternoonSlots = [];

            slotList.forEach(time => {
                let displayTime = time; 
                let isPm = false;
                if(typeof time === 'string') {
                    if(time.toLowerCase().includes('pm')) isPm = true;
                    else if(!time.toLowerCase().includes('am')) {
                        const h = parseInt(time.split(':')[0]);
                        if(h >= 12) isPm = true;
                    }
                }
                if(isPm) afternoonSlots.push(time);
                else morningSlots.push(time);
            });

            const renderSection = (title, times) => {
                if(times.length === 0) return '';
                let html = `<div style="margin-bottom: 1.5rem;">
                              <p class="sage-label" style="margin-bottom: 0.75rem;">${title}</p>
                              <div class="sage-slot-grid">`;
                times.forEach(t => {
                    html += `<button class="sage-slot-btn" onclick="selectSlot(this, '${t}')">${t}</button>`;
                });
                html += `</div></div>`;
                return html;
            };

            container.innerHTML = renderSection('Morning', morningSlots) + renderSection('Afternoon', afternoonSlots);
        }

        window.selectSlot = function(el, time) {
             currentState.selectedSlot = time;
             document.querySelectorAll('.sage-slot-btn').forEach(btn => btn.classList.remove('selected'));
             el.classList.add('selected');
        };

        // Reschedule
        document.getElementById('confirm-reschedule-btn').addEventListener('click', async () => {
             if (!currentState.bookingId || !currentState.selectedDateStr || !currentState.selectedSlot) {
                 alert("Please select a date and time slot.");
                 return;
             }
             if(!confirm("Reschedule to " + currentState.selectedDateStr + " " + currentState.selectedSlot + "?")) return;

             showLoading(true);
             
             // Simple time conversion for this example - adapt based on API needs
             const startTime = `${currentState.selectedDateStr} ${currentState.selectedSlot}`; 
             
             try {
                 const response = await axios.post(`${SAGE_API_BASE}/reschedule`, {
                    booking_id: currentState.bookingId,
                    staff_id: currentState.staffId,
                    start_time: startTime
                });
                
                // Assuming success structure matches previous implementation
                const result = response.data;
                const isSuccess = (result?.response?.returnvalue?.status === 'success') || (result?.status === 'success');

                if(isSuccess) {
                    alert("Rescheduled Successfully!");
                    window.location.reload();
                } else {
                    alert("Failed: " + (result.message || "Unknown error"));
                }
             } catch(e) {
                 alert("Error connecting to server.");
             } finally {
                 showLoading(false);
             }
        });

        document.addEventListener('DOMContentLoaded', () => {
             initCalendar();
             const bid = getQueryParam('booking_id');
             if(bid) {
                 currentState.bookingId = bid;
                 fetchAppointmentDetails(bid);
             } else {
                 showErrorState();
             }
        });
    </script>
    <?php
    return ob_get_clean();
}
