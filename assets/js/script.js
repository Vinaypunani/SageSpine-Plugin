/**
 * Sage Spine Appointment Widget
 * Portable Popup Form - WordPress Plugin Version
 */

(function () {
    // API Endpoint from Localized Script
    const API_ENDPOINT = sageSpineVars.api_url;

    // --- STATE MANAGEMENT ---
    const STATE_KEY = 'sage_spine_state';

    const StateManager = {
        save: (state) => {
            sessionStorage.setItem(STATE_KEY, JSON.stringify(state));
        },
        load: () => {
            const flow = sessionStorage.getItem(STATE_KEY);
            return flow ? JSON.parse(flow) : null;
        },
        clear: () => {
            sessionStorage.removeItem(STATE_KEY);
        },
        updateUrl: (screen) => {
            const url = new URL(window.location);
            url.searchParams.delete('sage_token'); // Always clean token on navigation
            if (screen === 'initial') {
                url.searchParams.delete('screen');
            } else {
                url.searchParams.set('screen', screen);
            }
            window.history.pushState({ screen }, '', url);
        },
        replaceUrl: (screen) => {
            const url = new URL(window.location);
            url.searchParams.delete('sage_token'); // Always clean token on navigation
            if (screen === 'initial') {
                url.searchParams.delete('screen');
            } else {
                url.searchParams.set('screen', screen);
            }
            window.history.replaceState({ screen }, '', url);
        }
    };

    // Current State Container
    let appState = {
        screen: 'initial', // initial, lookup, select, verify, history
        searchParams: {},
        searchResults: [],
        selectedContact: null,
        fullContact: null
    };

    // Go to Screen
    function goToScreen(screen, data = {}, replace = false) {
        console.log(`Navigating to: ${screen}`, data);

        // Update Internal State
        appState.screen = screen;

        if (data.searchParams) appState.searchParams = data.searchParams;
        if (data.searchResults) appState.searchResults = data.searchResults;
        if (data.selectedContact) appState.selectedContact = data.selectedContact;
        if (data.fullContact) appState.fullContact = data.fullContact;

        // Persist
        StateManager.save(appState);

        // Update URL
        if (replace) {
            StateManager.replaceUrl(screen);
        } else {
            StateManager.updateUrl(screen);
        }

        // Render
        renderScreen(screen);
    }

    // Render Logic switch
    function renderScreen(screen) {
        // Ensure Modal is open
        const modal = document.getElementById('sageAppointmentModal');
        const modalContent = modal.querySelector('.sage-modal');

        if (!modal.classList.contains('open')) {
            modal.classList.add('open');
        }

        // Reset Wide Mode by default
        modalContent.classList.remove('sage-modal-wide');
        modalContent.style.width = '800px';

        switch (screen) {
            case 'initial':
                renderInitialView();
                break;
            case 'lookup':
                renderLookupView();
                break;
            case 'select':
                if (appState.searchResults && appState.searchResults.length > 0) {
                    renderPatientSelection(appState.searchResults);
                } else {
                    goToScreen('lookup', {}, true); // Fallback
                }
                break;
            case 'verify': // OTP Input
                if (appState.selectedContact) {
                    renderOtpInput(appState.selectedContact);
                } else {
                    goToScreen('lookup', {}, true);
                }
                break;
            case 'history': // Results
                if (appState.fullContact) {
                    renderResults(appState.fullContact, appState.fullContact.related_events || []);
                } else {
                    goToScreen('lookup', {}, true);
                }
                break;
            default:
                renderInitialView();
        }
    }

    // View Renderers
    function renderInitialView() {
        const modalContent = document.querySelector('.sage-modal');
        if (window.sageOriginalFormHTML) {
            modalContent.innerHTML = window.sageOriginalFormHTML;
            rebindBaseListeners();
        }

        const initialView = document.getElementById('sageInitialView');
        const lookupView = document.getElementById('sageLookupView');
        if (initialView) initialView.style.setProperty('display', 'block', 'important');
        if (lookupView) lookupView.style.setProperty('display', 'none', 'important');

        // Re-attach floating label listeners if form is present
        attachFloatingLabelListeners();
    }

    function renderLookupView() {
        const modalContent = document.querySelector('.sage-modal');
        if (window.sageOriginalFormHTML && (!document.getElementById('sageLookupView'))) {
            modalContent.innerHTML = window.sageOriginalFormHTML;
            rebindBaseListeners();
        }

        const initialView = document.getElementById('sageInitialView');
        const lookupView = document.getElementById('sageLookupView');

        if (initialView) initialView.style.setProperty('display', 'none', 'important');
        if (lookupView) {
            lookupView.style.setProperty('display', 'flex', 'important');
        }

        // Pre-fill if we have params
        if (appState.searchParams) {
            if (document.getElementById('sageFirstName')) document.getElementById('sageFirstName').value = appState.searchParams.firstName || '';
            if (document.getElementById('sageLastName')) document.getElementById('sageLastName').value = appState.searchParams.lastName || '';
            if (document.getElementById('sageDob')) document.getElementById('sageDob').value = appState.searchParams.dob || ''; // ISO Format
        }

        // Attach listeners and check initial state
        attachFloatingLabelListeners();
    }

    function rebindBaseListeners() {
        // Close
        const closeBtn = document.querySelector('.sage-modal-close');
        if (closeBtn) closeBtn.addEventListener('click', closeAndResetModal);

        // Form Submit
        const form = document.getElementById('sageAppointmentForm');
        if (form) form.addEventListener('submit', handleFormSubmit);

        // Choice Listeners
        attachChoiceListeners();

        // Icons
        if (window.lucide) window.lucide.createIcons();

        // Floating Labels
        attachFloatingLabelListeners();
    }

    const closeAndResetModal = () => {
        const modal = document.getElementById('sageAppointmentModal');
        if (!modal) return;

        modal.classList.remove('open');

        // Clear State ON CLOSE (optional, arguably we might want to keep it? 
        // But user asked for close to reset usually)
        // User asked "when i click on the back button so we do to the previous popup screen"
        // But if they Close, we probably want to reset.
        StateManager.clear();
        appState = { screen: 'initial', searchParams: {}, searchResults: [], selectedContact: null, fullContact: null };
        StateManager.updateUrl('initial');

        setTimeout(() => {
            const modalContent = modal.querySelector('.sage-modal');
            modalContent.classList.remove('sage-modal-wide');
            modalContent.style.width = '800px';
            modalContent.style.height = ''; // Reset height
            modalContent.style.minHeight = ''; // Reset minHeight

            if (window.sageOriginalFormHTML) {
                modalContent.innerHTML = window.sageOriginalFormHTML;
                rebindBaseListeners();
            }
        }, 300);
    };

    // Helper: Attach Logic to Choice View Buttons
    function attachChoiceListeners() {
        const btnSchedule = document.getElementById('sageBtnChoiceSchedule');
        const btnBook = document.getElementById('sageBtnChoiceBook');

        if (btnSchedule) {
            btnSchedule.addEventListener('click', (e) => {
                e.preventDefault();
                goToScreen('lookup');
            });
        }

        if (btnBook) {
            btnBook.addEventListener('click', (e) => {
                e.preventDefault();
                window.open(sageSpineVars.booking_url, '_blank');
            });
        }
    }

    // Helper: Floating Label Logic
    function attachFloatingLabelListeners() {
        const inputs = document.querySelectorAll('.sage-form-input');

        inputs.forEach(input => {
            // Initial check
            if (input.value && input.value.trim() !== '') {
                input.classList.add('has-value');
            } else {
                input.classList.remove('has-value');
            }

            // Events
            const checkValue = () => {
                if (input.value && input.value.trim() !== '') {
                    input.classList.add('has-value');
                } else {
                    input.classList.remove('has-value');
                }
            };

            input.addEventListener('input', checkValue);
            input.addEventListener('change', checkValue);
            input.addEventListener('blur', checkValue);
        });
    }

    // --- API & FORM HANDLERS ---

    // State to store last search params for refresh
    let lastSearchParams = { firstName: '', lastName: '', dob: '' };

    async function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('.sage-btn-submit');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Searching...';
        submitBtn.disabled = true;

        const firstName = document.querySelector('#sageFirstName').value;
        const lastName = document.querySelector('#sageLastName').value;
        const dobISO = document.querySelector('#sageDob').value;

        // Store
        lastSearchParams = { firstName, lastName, dob: dobISO };

        console.log('Form Submitted. Searching Zoho CRM...');
        await searchZohoContact(firstName, lastName, dobISO);

        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }

    // Helper: Search Contact
    async function searchZohoContact(firstName, lastName, dob) {
        try {
            const response = await fetch(API_ENDPOINT, {
                method: 'POST',
                cache: 'no-store',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': sageSpineVars.nonce
                },
                body: JSON.stringify({ firstName, lastName, dob })
            });

            const data = await response.json();

            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            if (data.data && data.data.length > 0) {
                const searchParams = { firstName, lastName, dob };

                if (data.data.length === 1) {
                    // Single Match -> Auto Trigger OTP
                    const contact = data.data[0];

                    // SPECIAL handling: We want to go to verify, but we need to trigger API first.
                    // And we need to save state.
                    appState.selectedContact = contact;
                    appState.searchParams = searchParams;

                    // We trigger send, then navigate
                    sendLinkAndShowMessage(contact);

                } else {
                    goToScreen('select', { searchResults: data.data, searchParams });
                }
            } else {
                renderResults(null, []); // No Results View (handled by renderResults logic)
            }

        } catch (error) {
            console.error('Error searching contacts:', error);
            alert('Error connecting to search service.');
        }
    }

    // Helper: Render Patient Selection
    function renderPatientSelection(contacts) {
        const modalContent = document.querySelector('.sage-modal');
        modalContent.classList.add('sage-modal-wide');

        const listHTML = contacts.map((contact, index) => {
            const phone = contact.Phone || contact.Mobile || 'N/A';
            const last4 = phone.length >= 4 ? phone.slice(-4) : phone;
            const phoneLabel = phone !== 'N/A' ? `Phone ending in ****${last4}` : 'No Phone Number';
            const fullName = contact.Full_Name || (contact.First_Name + ' ' + contact.Last_Name);
            const maskedEmail = contact.Masked_Email || 'Email Hidden';

            return `
                <div class="sage-event-card sage-patient-select-card" data-index="${index}" style="cursor: pointer; transition: transform 0.2s;">
                    <div class="sage-event-info">
                        <div class="sage-event-doctor" style="font-size: 1.1rem; color: #2c3e50;">
                            <i class="fas fa-user"></i> ${fullName}
                        </div>
                        <div class="sage-event-date-row" style="margin-top: 5px;">
                            <i class="fas fa-envelope"></i> ${maskedEmail}
                        </div>
                         <div class="sage-event-date-row" style="margin-top: 2px; font-size: 0.85rem; color: #999;">
                            <i class="fas fa-phone"></i> ${phoneLabel}
                        </div>
                    </div>
                    <i class="fas fa-chevron-right" style="color: #ccc;"></i>
                </div>
            `;
        }).join('');

        const html = `
            <div class="sage-results-inner">
                 <div class="sage-results-header" style="padding: 20px 30px !important; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; box-sizing: border-box !important;">
                    <i class="fas fa-arrow-left sage-back-icon" id="sageBackIcon" style="cursor: pointer; font-size: 18px; color: #555;"></i>
                    <h2 style="margin: 0; font-size: 20px; color: #333; flex: 1; text-align: center;">Verify Identity</h2>
                    <span class="sage-modal-close sage-results-close" style="position: static !important; font-size: 28px; line-height: 1; color: #aaa; cursor: pointer;">&times;</span>
                </div>
                
                <div class="sage-results-body" style="padding: 20px;">
                    <p style="text-align: center; color: #666; margin-bottom: 20px;">Select your record to verify your identity.</p>
                    <div class="sage-results-container">
                        ${listHTML}
                    </div>
                </div>
            </div>
        `;

        modalContent.innerHTML = html;

        // Attach Select Listeners
        const cards = modalContent.querySelectorAll('.sage-patient-select-card');
        cards.forEach(card => {
            card.addEventListener('click', () => {
                const index = card.getAttribute('data-index');
                const selectedContact = contacts[index];
                sendLinkAndShowMessage(selectedContact);
            });
        });

        document.getElementById('sageBackIcon').addEventListener('click', () => {
            window.history.back();
        });

        modalContent.querySelector('.sage-results-close').addEventListener('click', closeAndResetModal);
    }

    // Helper: Send Link
    async function sendLinkAndShowMessage(contact) {
        const modalContent = document.querySelector('.sage-modal');

        // Fix Height
        modalContent.style.height = 'auto';
        modalContent.style.minHeight = '0px';

        modalContent.innerHTML = `
            <div style="padding: 30px; text-align: center;">
                <i class="fas fa-paper-plane" style="font-size: 40px; color: #2E8B57; margin-bottom: 20px;"></i>
                <h3 style="color: #333; margin-bottom: 10px;">Check your email</h3>
                <p style="color: #555; max-width: 400px; margin: 0 auto; line-height: 1.6;">
                    If a person exists with that information, a confirmation email will be sent to them. Thank you!
                </p>
                <div style="margin-top: 30px; font-size: 0.9em; color: #888;">
                    <p>The link will expire in 15 minutes.</p>
                </div>
                 <button class="sage-btn-submit" id="sageBtnBackToHome" style="margin-top: 20px; display: inline-block !important; background: #fff !important; color: #555 !important; border: 1px solid #ccc !important; width: auto !important; font-weight: normal !important; box-shadow: none !important;">Back to Home</button>
            </div>
        `;

        // Attach listener for back button
        const backBtn = document.getElementById('sageBtnBackToHome');
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                closeAndResetModal();
            });
        }

        try {
            const response = await fetch(sageSpineVars.token_send_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': sageSpineVars.nonce },
                body: JSON.stringify({ contact_id: contact.id })
            });
            const data = await response.json();

            if (!data.success) {
                // Keep the UI but maybe show error toast? 
                // Or just let the user retry if they don't get the email.
                // For now, if simulated locally, we are fine.
                console.error('Failed to send link (backend)', data);
            }
        } catch (err) {
            console.error(err);
            alert('Error sending link: ' + err.message);
        }
    }

    // Helper: Verify OTP
    async function verifyOtp(contact, otp) {
        const btn = document.querySelector('#sageOtpForm button');
        btn.textContent = 'Verifying...';
        btn.disabled = true;

        try {
            const response = await fetch(sageSpineVars.otp_verify_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': sageSpineVars.nonce },
                body: JSON.stringify({ contact_id: contact.id, otp: otp })
            });

            const data = await response.json();

            if (data.success && data.data) {
                const fullContact = data.data;
                goToScreen('history', { fullContact: fullContact });
            } else {
                throw new Error(data.message || 'Invalid OTP');
            }

        } catch (err) {
            console.error(err);
            alert(err.message || 'Verification failed');
            btn.textContent = 'Verify';
            btn.disabled = false;
        }
    }

    // Helper: Render Results (History)
    function renderResults(contact, events) {
        const modalContent = document.querySelector('.sage-modal');
        modalContent.classList.add('sage-modal-wide');

        let bodyHTML = '';

        if (contact) {
            const firstName = contact.First_Name || '';
            const lastName = contact.Last_Name || '';
            const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
            const fullName = contact.Full_Name || (firstName + ' ' + lastName);
            const dob = contact.Date_of_Birth ? new Date(contact.Date_of_Birth).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
            const email = contact.Email || '';

            const now = new Date();
            const upcomingEvents = [];
            const pastEvents = [];

            events.forEach(event => {
                const dateStr = event.Start_DateTime || event.Created_Time;
                const dateObj = dateStr ? new Date(dateStr) : null;
                if (dateObj && dateObj > now) {
                    upcomingEvents.push(event);
                } else {
                    pastEvents.push(event);
                }
            });

            const generateEventsHTML = (eventList) => {
                if (eventList.length === 0) return '<p style="color: #7f8c8d; text-align: center; padding: 20px;">No appointments found.</p>';

                return eventList.map(event => {
                    console.log('Meeting Info:', event);
                    const dateStr = event.Start_DateTime || event.Created_Time;

                    // 1. Comparison Date (Absolute Time) - Use original string with timezone
                    const trueDateObj = dateStr ? new Date(dateStr) : null;

                    // 2. Display Date (Clinic Wall Time) - Strip timezone to prevent browser conversion
                    const rawDateStr = dateStr ? dateStr.replace(/Z|[+-]\d{2}(:?\d{2})?$/, '') : null;
                    const displayDateObj = rawDateStr ? new Date(rawDateStr) : (dateStr ? new Date(dateStr) : null);

                    const formattedDate = displayDateObj ? displayDateObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) + ' at ' + displayDateObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : 'TBD';

                    let displayStatus = (trueDateObj && trueDateObj > now) ? 'Upcoming' : 'Completed';
                    const title = event.Event_Title || event.Subject || 'Untitled Event';

                    // Maps URL
                    const clinicLocation = sageSpineVars.location || 'Sage Spine and Nerve Center';
                    const mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(clinicLocation)}`;

                    let actionButtonsHTML = '';
                    if (displayStatus === 'Upcoming') {

                        actionButtonsHTML = `
                            <div class="sage-event-actions">
                                <a href="${event.zohobookingstest__Booking_Summary}?Reschedule=true" target="_blank" class="sage-btn-reschedule">
                                    <i class="far fa-calendar-alt" style="margin-right: 6px;"></i> Reschedule
                                </a>

                                <a href="${mapsUrl}" target="_blank" class="sage-btn-direction">
                                    <i class="fas fa-map-marker-alt" style="margin-right: 6px;"></i> Directions
                                </a>

                                <a href="${event.zohobookingstest__Booking_Summary}?Cancel=true" target="_blank" class="sage-action-cancel" data-id="${event.booking_id || event.Id}">
                                    <i class="fas fa-times-circle" style="margin-right: 6px;"></i> Cancel
                                </a>
                            </div>
                         `;
                    }

                    // Address Line
                    const addressLine = '<div class="sage-event-date-row" style="margin-top: 5px;"><i class="fas fa-map-marker-alt" style="color: #2E8B57;"></i> 38500 Tanger Dr #110, North Branch, MN 55056</div>';

                    // Status Badge Style
                    // Status Badge Style
                    let statusIcon = '';
                    if (displayStatus === 'Upcoming') {
                        statusIcon = '<i class="fas fa-clock" style="margin-right: 5px;"></i>';
                    } else {
                        statusIcon = '<i class="fas fa-check-circle" style="margin-right: 5px;"></i>';
                    }
                    const statusBadgeDisplay = `<span style="padding: 2px 8px; border: 1px solid #ccc; border-radius: 4px; display: inline-block; font-size: 13px; color: #555;">${statusIcon}${displayStatus}</span>`;

                    return `
                        <div class="sage-event-card">
                            <div class="sage-event-info">
                                <div class="sage-event-date-row"><i class="far fa-calendar-alt"></i> ${formattedDate}</div>
                                <div class="sage-event-doctor">${title}</div>
                                ${addressLine}
                                <div class="sage-event-status-row" style="margin-top: 5px;">${statusBadgeDisplay}</div>
                            </div>
                            ${actionButtonsHTML}
                        </div>
                    `;
                }).join('');
            };

            // Styles for Tabs
            const styles = `
                <style>
                    .sage-tabs-header {
                        display: flex;
                        border-bottom: 2px solid #eee;
                        margin-bottom: 20px;
                    }
                    .sage-tab-item {
                        padding: 10px 20px;
                        cursor: pointer;
                        font-weight: 600;
                        color: #7f8c8d;
                        border-bottom: 2px solid transparent;
                        margin-bottom: -2px;
                        transition: all 0.3s ease;
                    }
                    .sage-tab-item.active {
                        color: #2c3e50;
                        border-bottom: 2px solid #0b0c2a;
                    }
                    .sage-tab-content {
                        display: none;
                    }
                    .sage-tab-content.active {
                        display: block;
                        animation: fadeIn 0.3s ease-in-out;
                    }
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                </style>
            `;

            bodyHTML = `
                ${styles}
                <div class="sage-contact-header">
                    <div class="sage-avatar">${initials}</div>
                    <div class="sage-contact-info">
                        <h3>${fullName}</h3>
                        <div class="sage-contact-dob">Dob ${dob}</div>
                        <div class="sage-contact-email">${email}</div>
                    </div>
                </div>
                
                <!-- Tabs Header -->
                <div class="sage-tabs-header">
                    <div class="sage-tab-item active" data-tab="upcoming">Upcoming</div>
                    <div class="sage-tab-item" data-tab="past">Past</div>
                </div>

                <!-- Tabs Content -->
                <div class="sage-results-container" style="width: 100% !important; box-sizing: border-box !important;">
                    <div id="tab-content-upcoming" class="sage-tab-content active">
                        ${generateEventsHTML(upcomingEvents)}
                    </div>
                    <div id="tab-content-past" class="sage-tab-content">
                        ${generateEventsHTML(pastEvents)}
                    </div>
                </div>
            `;

        } else {
            // No Results
            bodyHTML = `
                <div style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #f39c12; margin-bottom: 20px;"></i>
                <h3 style="color: #2c3e50; margin-bottom: 10px;">No Record Found</h3>
                <p style="color: #666; font-size: 1rem; margin-bottom: 30px;">We couldn't find a patient record matching the provided details.</p>

                <div class="sage-no-record-actions">
                    <button id="sageBtnSearchAgain" class="sage-btn-submit" style="background: #2c3e50 !important;">Search Again</button>
                    
                    <a href="${sageSpineVars.booking_url}" target="_blank" style="flex: 1; display: block; text-decoration: none;">
                        <button class="sage-btn-submit" style="background: #2E8B57 !important;">Book Appointment</button>
                    </a>
                </div>
            </div>
            `;
        }

        const html = `
                <div class="sage-results-inner">
                <div class="sage-results-header" style="padding: 20px 30px !important; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between;">
                    <i class="fas fa-arrow-left sage-back-icon" id="sageBackIcon" style="cursor: pointer; font-size: 18px; color: #555;"></i>
                    <h2 style="margin: 0; font-size: 20px; color: #333; flex: 1; text-align: center;">Appointment History</h2>
                    <span class="sage-modal-close sage-results-close" style="cursor: pointer;">&times;</span>
                </div>
                <div class="sage-results-body" style="min-height: 400px; display: flex; flex-direction: column; justify-content: center;">${bodyHTML}</div>
            </div >
                `;

        modalContent.innerHTML = html;

        // Tab Switching Logic - Event Delegation
        // We use delegation because the modal content is replaced dynamically
        modalContent.addEventListener('click', (e) => {
            const tab = e.target.closest('.sage-tab-item');
            if (tab) {
                const tabs = modalContent.querySelectorAll('.sage-tab-item');
                const targetTabId = tab.getAttribute('data-tab');

                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents
                modalContent.querySelectorAll('.sage-tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Show target tab content
                const targetContent = document.getElementById(`tab-content-${targetTabId}`);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            }
        });

        document.getElementById('sageBackIcon').addEventListener('click', () => {
            goToScreen('lookup');
        });

        // Search Again Listener
        const searchAgain = document.getElementById('sageBtnSearchAgain');
        if (searchAgain) {
            searchAgain.addEventListener('click', () => {
                goToScreen('lookup');
            });
        }

        // Cancel Action Listener


        modalContent.querySelector('.sage-results-close').addEventListener('click', closeAndResetModal);
    }


    // --- INIT ---
    async function initAppointmentWidget() {
        console.log('Initializing Sage Spine Appointment Widget...');

        // Fix: Capture original HTML immediately preventing "Verifying Link..." spinner from being captured as the original state.
        const _preCheckModal = document.getElementById('sageAppointmentModal');
        if (_preCheckModal && !window.sageOriginalFormHTML) {
            const _preCheckContent = _preCheckModal.querySelector('.sage-modal');
            if (_preCheckContent) {
                window.sageOriginalFormHTML = _preCheckContent.innerHTML;
            }
        }

        // Check for Token in URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('sage_token');

        if (token) {
            // Verify Token
            console.log('Found sage_token, verifying...');

            // Open Modal Immediately with Loading
            const modal = document.getElementById('sageAppointmentModal');
            if (modal) {
                modal.classList.add('open');
                const modalContent = modal.querySelector('.sage-modal');
                if (modalContent) {
                    modalContent.innerHTML = `
                        <div style="padding: 50px; text-align: center;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: #2E8B57;"></i>
                            <p style="margin-top: 20px; color: #555;">Verifying Link...</p>
                        </div>
                    `;
                }

                try {
                    const response = await fetch(sageSpineVars.token_verify_url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': sageSpineVars.nonce },
                        body: JSON.stringify({ token: token })
                    });
                    const data = await response.json();

                    if (data.success && data.data) {
                        // Success!
                        // Persist Session: Do NOT remove sage_token from URL
                        // urlParams.delete('sage_token'); 
                        // const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                        // window.history.replaceState({}, document.title, newUrl);

                        console.log('Token verified. Session active.');

                        // Show Results
                        const fullContact = data.data;
                        // Ensure modal is reset logic structure
                        if (!window.sageOriginalFormHTML) {
                            // We might have overwritten it, but we need it for reset. 
                            // Hopefully it was captured or we capture it on fresh page reload usually.
                            // If we came here directly, we might not have captured it yet.
                            // But renderInitialView uses it. 
                        }

                        // We need to ensure appState is set
                        appState.screen = 'history';
                        appState.fullContact = fullContact;
                        StateManager.save(appState);

                        renderResults(fullContact, fullContact.related_events || []);

                    } else {
                        // Clean URL on failure to avoid infinite loop
                        urlParams.delete('sage_token');
                        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                        window.history.replaceState({}, document.title, newUrl);

                        throw new Error(data.message || 'Invalid or expired link.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Verification failed: ' + err.message);

                    // Clean URL on exception to avoid infinite loop
                    urlParams.delete('sage_token');
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                    window.history.replaceState({}, document.title, newUrl);

                    closeAndResetModal();
                }
            }
        }

        const modal = document.getElementById('sageAppointmentModal');
        if (!modal) return;

        if (!window.sageOriginalFormHTML) {
            window.sageOriginalFormHTML = modal.querySelector('.sage-modal').innerHTML;
        }

        attachChoiceListeners();

        const closeBtn = modal.querySelector('.sage-modal-close');

        const openModal = (e) => {
            if (e) e.preventDefault();
            modal.classList.add('open');
            StateManager.updateUrl('initial');
        };

        if (closeBtn) {
            closeBtn.addEventListener('click', closeAndResetModal);
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeAndResetModal();
        });

        const form = document.getElementById('sageAppointmentForm');
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }

        // Attach to Buttons
        const attachToButtons = () => {
            // Broader selector: matches "sage-open-appointment-popup", "open-appointment-popup", etc.
            const popupTriggers = document.querySelectorAll('.open-appointment-popup, [class*="open-appointment-popup"]');

            console.log(`Sage Popup: Found ${popupTriggers.length} trigger buttons.`);

            popupTriggers.forEach(btn => {
                btn.removeEventListener('click', openModal);
                btn.addEventListener('click', openModal);
                // Visual cue cursor
                btn.style.cursor = 'pointer';
            });

            const linkTriggers = document.querySelectorAll('[class*="-open-appointment-link"]');
            linkTriggers.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.open(sageSpineVars.booking_url, '_blank');
                });
            });
        };

        attachToButtons();

        // PopState Listener
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.screen) {
                const storedState = StateManager.load();
                if (storedState) {
                    appState = storedState;
                    // Sync screen priority
                    appState.screen = e.state.screen;
                    renderScreen(appState.screen);
                } else {
                    // Fallback if session died?
                    renderScreen('initial');
                }
            } else {
                renderScreen('initial');
            }
        });

        // Initialization Logic for Page Load (Reload support)
        // const urlParams = new URLSearchParams(window.location.search); // Already defined at top
        const screen = urlParams.get('screen');
        // Only restore screen state if we are NOT verifying a token
        if (screen && !urlParams.has('sage_token')) {
            const storedState = StateManager.load();
            if (storedState) {
                appState = storedState;
                renderScreen(screen); // Opens modal
            } else {
                StateManager.replaceUrl('initial');
            }
        }

        window.openSageAppointmentPopup = openModal;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAppointmentWidget);
    } else {
        initAppointmentWidget();
    }

})();
