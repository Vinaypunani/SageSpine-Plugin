<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function render_sage_book_appointment() {
    ob_start();
    $api_nonce = wp_create_nonce('wp_rest');
    $api_base = rest_url('sagespine/v1');
    ?>
    <script>
        const WP_API_NONCE = "<?php echo $api_nonce; ?>";
        const WP_MEDIA_ENDPOINT = "<?php echo esc_url_raw(rest_url('sagespine/v1/upload')); ?>";
    </script>
    <style>
        /* Scoped Reset to protect against Theme Styles */
        #sage-book-app {
            all: initial;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: block;
            width: 100%;
            box-sizing: border-box;
            line-height: 1.5;
            color: #1f2937;
        }
        #sage-book-app * {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: #e5e7eb;
        }
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
        
        /* Utility Classes */
        #sage-book-app .hidden { display: none !important; }
        #sage-book-app .flex { display: flex; }
        #sage-book-app .grid { display: grid; }
        
        /* Scrollbar Styling */
        #sage-book-app .custom-scrollbar::-webkit-scrollbar {
            height: 4px;
            width: 4px;
        }
        #sage-book-app .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        #sage-book-app .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 20px;
        }
        
        /* Layout Container */
        #sage-book-app .sage-booking-container {
            width: 100%;
            max-width: 80rem;
            margin: 0 auto;
            background-color: white;
            min-height: 100vh;
            position: relative;
            padding: 1rem;
        }
        
        /* Loading Animation */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* Loading Overlay */
        #sage-book-app #loading-overlay {
            position: fixed;
            inset: 0;
            background-color: white;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #sage-book-app #loading-overlay > div {
            animation: spin 1s linear infinite;
            border-radius: 9999px;
            height: 4rem;
            width: 4rem;
            border-width: 4px;
            border-color: #e5e7eb;
            border-top-color: #10b981;
        }
        
        /* Headers */
        #sage-book-app .booking-header {
            margin-bottom: 2rem;
        }
        #sage-book-app #booking-main-title {
            font-size: 1.875rem;
            font-weight: bold;
            text-align: center;
            color: #059669;
            margin-bottom: 0.5rem;
        }
        
        /* Main Layout */
        #sage-book-app .main-layout {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        /* Sidebar */
        #sage-book-app #appointment-sidebar {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        #sage-book-app .sidebar-step {
            padding: 1rem;
            border: 1px solid;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        #sage-book-app .sidebar-step-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        #sage-book-app .sidebar-step-inner {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        #sage-book-app .sidebar-step i[data-lucide] {
            width: 1.25rem;
            height: 1.25rem;
        }
        #sage-book-app .sidebar-step i.chevron {
            width: 1rem;
            height: 1rem;
        }
        #sage-book-app .sidebar-step span {
            font-weight: 500;
        }
        #sage-book-app .sidebar-step.active {
            border-color: #10b981;
            background-color: #ecfdf5;
        }
        #sage-book-app .sidebar-step.active span {
            color: #047857;
        }
        #sage-book-app .sidebar-step.active i[data-lucide] {
            color: #059669;
        }
        #sage-book-app .sidebar-step.inactive {
            border-color: #f3f4f6;
            background-color: white;
        }
        #sage-book-app .sidebar-step.inactive:hover {
            background-color: #f9fafb;
        }
        #sage-book-app .sidebar-step.inactive span {
            color: #4b5563;
        }
        #sage-book-app .sidebar-step.inactive i[data-lucide] {
            color: #9ca3af;
        }
        #sage-book-app .sidebar-step.completed {
            border-color: #d1fae5;
            background-color: white;
        }
        #sage-book-app .sidebar-step.completed:hover {
            background-color: #d1fae5;
        }
        #sage-book-app .sidebar-step.completed span {
            color: #1f2937;
        }
        #sage-book-app .sidebar-step.completed i[data-lucide] {
            color: #059669;
        }
        #sage-book-app .sidebar-step.future {
            border-color: #f3f4f6;
            background-color: #f9fafb;
            opacity: 0.6;
            cursor: not-allowed;
        }
        #sage-book-app .sidebar-step.future span {
            color: #9ca3af;
        }
        #sage-book-app .sidebar-step.future i[data-lucide] {
            color: #9ca3af;
        }
        
        /* Main Content */
        #sage-book-app #appointment-main-content {
            width: 100%;
            min-height: 400px;
        }
        
        /* Step Views */
        #sage-book-app .step-view {}
        
        /* Step Headers */
        #sage-book-app .step-header {
            margin-bottom: 2rem;
        }
        #sage-book-app .step-header h2 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #111827;
            margin-bottom: 0.25rem;
        }
        #sage-book-app .step-header p {
            color: #6b7280;
        }
        
        /* Services List */
        #sage-book-app #services-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        #sage-book-app .service-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
            padding: 1.5rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        #sage-book-app .service-card.selected {
            border-radius: 0.75rem;
            border: 2px solid #059669;
            background-color: rgba(236, 253, 245, 0.5);
        }
        #sage-book-app .service-card.unselected {
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background-color: white;
        }
        #sage-book-app .service-card.unselected:hover {
            border-color: rgba(5, 150, 105, 0.5);
            background-color: #f9fafb;
        }
        #sage-book-app .service-avatar {
            position: relative;
        }
        #sage-book-app .service-avatar img {
            width: 5rem;
            height: 5rem;
            border-radius: 9999px;
            object-fit: cover;
        }
        #sage-book-app .service-card.selected .service-avatar img {
            border: 2px solid #059669;
        }
        #sage-book-app .service-card.unselected .service-avatar img {
            filter: grayscale(100%);
            transition: all 0.2s;
        }
        #sage-book-app .service-card.unselected:hover .service-avatar img {
            filter: grayscale(0%);
        }
        #sage-book-app .service-info {
            flex: 1;
            text-align: center;
        }
        #sage-book-app .service-info h3 {
            font-size: 1.125rem;
            font-weight: bold;
            color: #111827;
        }
        #sage-book-app .service-info .specialty {
            color: #059669;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        #sage-book-app .service-meta {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            width: 100%;
        }
        #sage-book-app .service-duration {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            background-color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
        }
        #sage-book-app .service-radio {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 9999px;
            border: 2px solid;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #sage-book-app .service-card.selected .service-radio {
            border-color: #059669;
        }
        #sage-book-app .service-card.unselected .service-radio {
            border-color: #d1d5db;
            transition: all 0.2s;
        }
        #sage-book-app .service-card.unselected:hover .service-radio {
            border-color: #059669;
        }
        #sage-book-app .service-radio-dot {
            width: 0.625rem;
            height: 0.625rem;
            background-color: #059669;
            border-radius: 9999px;
        }
        
        /* Skeleton */
        #sage-book-app #services-skeleton {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        #sage-book-app .skeleton-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: white;
            border-bottom: 1px solid #f3f4f6;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        #sage-book-app .skeleton-avatar {
            flex-shrink: 0;
            margin-right: 1rem;
        }
        #sage-book-app .skeleton-avatar-img {
            height: 3rem;
            width: 3rem;
            background-color: #e5e7eb;
            border-radius: 0.125rem;
        }
        #sage-book-app .skeleton-content {
            flex: 1;
            min-width: 0;
        }
        #sage-book-app .skeleton-title {
            height: 1.25rem;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            width: 12rem;
            margin-bottom: 0.5rem;
        }
        #sage-book-app .skeleton-text-full {
            height: 0.75rem;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            width: 100%;
            margin-bottom: 0.25rem;
        }
        #sage-book-app .skeleton-text-3q {
            height: 0.75rem;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            width: 75%;
        }
        #sage-book-app .skeleton-meta {
            margin-left: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #sage-book-app .skeleton-duration {
            height: 1rem;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            width: 5rem;
        }
        #sage-book-app .skeleton-radio {
            height: 0.75rem;
            width: 0.75rem;
            background-color: #e5e7eb;
            border-radius: 9999px;
        }
        
        /* Service Cards - JavaScript Generated */
        #sage-book-app .service-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
            padding: 1.5rem;
            transition: all 0.2s;
            cursor: pointer;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background-color: white;
        }
        #sage-book-app .service-card:hover {
            border-color: rgba(16, 185, 129, 0.5);
            background-color: #f9fafb;
        }
        #sage-book-app .service-card.selected {
            border: 2px solid #10b981;
            background-color: rgba(236, 253, 245, 0.5);
        }
        #sage-book-app .service-card.selected:hover {
            border-color: #10b981;
            background-color: rgba(236, 253, 245, 0.5);
        }
        #sage-book-app .service-avatar {
            position: relative;
            flex-shrink: 0;
        }
        #sage-book-app .service-avatar img {
            width: 5rem;
            height: 5rem;
            border-radius: 9999px;
            object-fit: cover;
            filter: grayscale(100%);
            transition: all 0.3s;
        }
        #sage-book-app .service-card:hover .service-avatar img {
            filter: grayscale(0%);
        }
        #sage-book-app .service-card.selected .service-avatar img {
            filter: grayscale(0%);
            border: 2px solid #10b981;
        }
        #sage-book-app .service-info {
            flex: 1;
            text-align: center;
            min-width: 0;
        }
        #sage-book-app .service-info h3 {
            font-size: 1.125rem;
            font-weight: bold;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        #sage-book-app .service-description {
            color: #10b981;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        #sage-book-app .service-meta {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            width: 100%;
        }
        #sage-book-app .service-duration {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            background-color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
        }
        #sage-book-app .service-radio {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 9999px;
            border: 2px solid #d1d5db;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        #sage-book-app .service-card:hover .service-radio {
            border-color: #10b981;
        }
        #sage-book-app .service-radio.selected {
            border-color: #10b981;
        }
        #sage-book-app .service-radio-dot {
            width: 0.625rem;
            height: 0.625rem;
            background-color: #10b981;
            border-radius: 9999px;
        }
        
        /* Responsive Service Card Layout */
        @media (min-width: 768px) {
            #sage-book-app .service-card {
                flex-direction: row;
                align-items: flex-start;
            }
            #sage-book-app .service-info {
                text-align: left;
            }
            #sage-book-app .service-meta {
                flex-direction: column;
                width: auto;
                height: 100%;
            }
        }
        
        /* Buttons */
        #sage-book-app .btn-primary {
            background-color: #059669;
            color: white;
            padding: 0.625rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
            transition: all 0.2s;
            transform-origin: center;
        }
        #sage-book-app .btn-primary:hover {
            background-color: #047857;
        }
        #sage-book-app .btn-primary:active {
            transform: scale(0.95);
        }
        #sage-book-app .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #sage-book-app .btn-secondary:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
        }
        
        /* Step Navigation */
        #sage-book-app .step-nav {
            margin-top: 2rem;
            display: flex;
            flex-direction: row;
            gap: 0.75rem;
            justify-content: space-between;
            align-items: center;
        }
        #sage-book-app .step-nav-with-border {
            padding-top: 1.5rem;
            border-top: 1px solid #f3f4f6;
        }
        #sage-book-app .btn-continue {
            flex: 2;
            padding: 0.75rem 1rem;
            background-color: #059669;
            color: white;
            font-weight: bold;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
            transition: all 0.2s;
            transform-origin: center;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }
        #sage-book-app .btn-secondary {
            flex: 1;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: none;
        }
        
        /* Calendar Section */
        #sage-book-app .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            position: relative;
            z-index: 20;
        }
        #sage-book-app .month-selector {
            position: relative;
        }
        #sage-book-app .month-trigger-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
            background-color: white;
            cursor: pointer;
            box-shadow: none;
        }
        #sage-book-app .month-trigger-btn:hover {
            background-color: #f9fafb;
            border-color: #10b981;
        }
        #sage-book-app .month-trigger-btn h3 {
            font-size: 1.125rem;
            font-weight: 500;
            color: #1f2937;
            transition: all 0.2s;
            margin: 0;
        }
        #sage-book-app .month-trigger-btn:hover h3 {
            color: #10b981;
        }
        #sage-book-app .month-trigger-btn i {
            width: 1rem;
            height: 1rem;
            color: #9ca3af;
            transition: all 0.2s;
        }
        #sage-book-app .month-trigger-btn:hover i {
            color: #10b981;
            transform: rotate(180deg);
        }
        
        /* Month Picker */
        #sage-book-app #custom-month-picker {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0.75rem;
            z-index: 30;
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 1.25rem;
            width: 20rem;
            max-width: calc(100vw - 2rem);
        }
        #sage-book-app .month-picker-year-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f3f4f6;
        }
        #sage-book-app .month-picker-year-nav button {
            padding: 0.5rem;
            border-radius: 9999px;
            color: #6b7280;
            transition: all 0.2s;
            background-color: white;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #sage-book-app .month-picker-year-nav button:hover:not(:disabled) {
            background-color: #ecfdf5;
            border-color: #10b981;
            color: #10b981;
            transform: scale(1.05);
        }
        #sage-book-app .month-picker-year-nav button i {
            width: 1.25rem;
            height: 1.25rem;
        }
        #sage-book-app .month-picker-year-nav button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        #sage-book-app .month-picker-year-label {
            font-weight: bold;
            color: #1f2937;
            font-size: 1.25rem;
            letter-spacing: -0.025em;
        }
        #sage-book-app #month-picker-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.625rem;
        }
        #sage-book-app .month-picker-btn {
            padding: 0.75rem 0.5rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            border: 1px solid;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
            text-align: center;
        }
        #sage-book-app .month-picker-btn.disabled {
            border-color: transparent;
            color: #d1d5db;
            cursor: not-allowed;
            background-color: #f9fafb;
            opacity: 0.5;
        }
        #sage-book-app .month-picker-btn.enabled {
            border-color: #e5e7eb;
            color: #4b5563;
            background-color: white;
            box-shadow: none !important;
        }
        #sage-book-app .month-picker-btn.enabled:hover {
            border-color: #10b981;
            background-color: #ecfdf5;
            color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        #sage-book-app .month-picker-btn.enabled:active {
            transform: translateY(0);
        }
        /* Selected Month State */
        #sage-book-app .month-picker-btn.selected {
            border-color: #10b981;
            background-color: #10b981;
            color: white;
            font-weight: bold;
        }
        #sage-book-app .month-picker-btn.selected:hover {
            border-color: #059669;
            background-color: #059669;
            color: white;
        }
        /* Current Month Indicator */
        #sage-book-app .month-picker-btn.current {
            position: relative;
        }
        #sage-book-app .month-picker-btn.current::after {
            content: '';
            position: absolute;
            bottom: 0.25rem;
            left: 50%;
            transform: translateX(-50%);
            width: 0.25rem;
            height: 0.25rem;
            background-color: #10b981;
            border-radius: 9999px;
        }
        #sage-book-app .month-picker-btn.selected.current::after {
            background-color: white;
        }
        
        /* Calendar Icon Button */
        #sage-book-app #btn-month-picker-icon {
padding: 0.5rem;
    border-radius: 9999px;
    transition: all 0.2s;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    background: #f9fafb;
    color: black;
    border: 1px solid #e5e7eb;
        }
        #sage-book-app #btn-month-picker-icon:hover {
            background-color: #f3f4f6;
        }
        #sage-book-app #btn-month-picker-icon i {
            width: 1.25rem;
            height: 1.25rem;
            color: #4b5563;
        }
        
        /* Days Scroller */
        #sage-book-app .days-scroller {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        #sage-book-app .week-nav-btn {
            width: 2.5rem;
            height: 2.5rem;
            flex-shrink: 0;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
                background: #f9fafb;
    padding: 0px;
    color: black;
    box-shadow: none;
        }
        #sage-book-app .week-nav-btn:hover {
            background-color: #f9fafb;
        }
        #sage-book-app .week-nav-btn i {
            width: 1.25rem;
            height: 1.25rem;
            color: #4b5563;
        }
        #sage-book-app .days-track-wrapper {
            position: relative;
            flex-grow: 1;
            overflow: hidden;
            border-radius: 0.75rem;
        }
        #sage-book-app #calendar-days-track {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            padding-top: 0.25rem;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
            scroll-snap-type: x;
        }
        #sage-book-app .day-btn {
            flex: 1;
            min-width: 70px;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            user-select: none;
        }
        #sage-book-app .day-btn.past {
            border-color: #f3f4f6;
            background-color: #f9fafb;
            opacity: 0.4;
            cursor: not-allowed;
        }
        #sage-book-app .day-btn.no-slots {
            border-color: #f3f4f6;
            background-color: #f9fafb;
            opacity: 0.6;
            cursor: not-allowed;
        }
        #sage-book-app .day-btn.selected {
            border-color: #059669;
            background-color: #ecfdf5;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.2);
            transform: scale(1);
        }
        #sage-book-app .day-btn.unselected {
            border-color: #e5e7eb;
            background-color: white;
        }
        #sage-book-app .day-btn.unselected:hover {
            border-color: rgba(16, 185, 129, 0.5);
            background-color: #f9fafb;
        }
        #sage-book-app .day-num {
            font-size: 1.5rem;
            font-weight: bold;
        }
        #sage-book-app .day-btn.selected .day-num {
            color: #047857;
        }
        #sage-book-app .day-btn.unselected .day-num,
        #sage-book-app .day-btn.past .day-num,
        #sage-book-app .day-btn.no-slots .day-num {
            color: #111827;
        }
        #sage-book-app .day-name {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        #sage-book-app .day-btn.selected .day-name {
            color: #059669;
        }
        #sage-book-app .day-btn.unselected .day-name,
        #sage-book-app .day-btn.past .day-name,
        #sage-book-app .day-btn.no-slots .day-name {
            color: #6b7280;
        }
        
        /* Calendar Day - JavaScript Generated Elements */
        #sage-book-app .calendar-day {
            flex: 1;
            min-width: 70px;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            user-select: none;
            background-color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }
        #sage-book-app .calendar-day:hover {
            border-color: rgba(16, 185, 129, 0.5);
            background-color: #f9fafb;
        }
        #sage-book-app .calendar-day.selected {
            border-color: #10b981;
            border-width: 2px;
            background-color: #ecfdf5;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(16, 185, 129, 0.2);
            transform: scale(1);
        }
        #sage-book-app .calendar-day.selected:hover {
            border-color: #10b981;
            background-color: #ecfdf5;
        }
        #sage-book-app .calendar-day.past {
            border-color: #f3f4f6;
            background-color: #f9fafb;
            opacity: 0.6;
            cursor: not-allowed;
            border-color: #FFF;
            background-color: #ffffff;
        }
        #sage-book-app .calendar-day.past:hover {
            border-color: #f3f4f6;
            background-color: #f9fafb;
        }
        #sage-book-app .calendar-day.no-slots {
            opacity: 0.6;
            cursor: not-allowed;
            color: #2A2A2A;
            border-color: #FFF;
            background-color: #ffffff;
        }
        #sage-book-app .calendar-day.no-slots:hover {
            border-color: #f3f4f6;
            background-color: #f9fafb;
        }
        #sage-book-app .calendar-day .day-number {
            font-size: 1.5rem;
            line-height: 2rem;
            font-weight: bold;
            color: #2A2A2A;
        }
        #sage-book-app .calendar-day.selected .day-number {
            color: #047857;
        }
        #sage-book-app .calendar-day .day-name {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #2A2A2A;
        }
        #sage-book-app .calendar-day.selected .day-name {
            color: #059669;
        }
        
        /* Calendar Loading */
        #sage-book-app #calendar-loading {
            position: absolute;
            inset: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            backdrop-filter: blur(1px);
            transition: all 0.3s;
        }
        #sage-book-app #calendar-loading > div {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        /* Custom CSS Spinner */
        #sage-book-app .spinner {
            width: 2.5rem;
            height: 2.5rem;
            border: 3px solid #e5e7eb;
            border-top-color: #10b981;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        #sage-book-app #calendar-loading span {
            font-size: 0.75rem;
            font-weight: 500;
            color: #059669;
        }
        
        /* Slots Container */
        #sage-book-app #slots-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            min-height: 200px;
        }
        #sage-book-app #slots-container section {
            margin-bottom: 1.5rem;
        }
        #sage-book-app #slots-container h4 {
            font-size: 0.875rem;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #sage-book-app #slots-container h4 i {
            width: 1rem;
            height: 1rem;
        }
        #sage-book-app #slots-container .slot-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }
        #sage-book-app .slot-btn {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            width: 100%;
            background-color: white;
            color: #374151;
            cursor: pointer;
            box-shadow: none;
        }
        #sage-book-app .slot-btn:hover {
            border-color: #10b981;
            color: #10b981;
        }
        #sage-book-app .slot-btn.selected {
            border: 2px solid #10b981;
            background-color: #10b981;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }
        #sage-book-app .slot-btn.selected:hover {
            border-color: #10b981;
            background-color: #10b981;
            color: white;
        }
        #sage-book-app .slot-btn.unselected {
            border-color: #e2e8f0;
            background-color: white;
            color: #374151;
        }
        #sage-book-app .slot-btn.unselected:hover {
            border-color: #059669;
            color: #059669;
        }
        
        /* Slots Section & Grid - JavaScript Generated */
        #sage-book-app .slots-section {
            margin-bottom: 1.5rem;
        }
        #sage-book-app .slots-section-title {
            font-size: 0.875rem;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #sage-book-app .slots-section-title i {
            width: 1rem;
            height: 1rem;
        }
        #sage-book-app .slots-section-title i[data-lucide="sun"] {
            color: #f59e0b;
        }
        #sage-book-app .slots-section-title i[data-lucide="sunset"] {
            color: #6366f1;
        }
        #sage-book-app .slots-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            #sage-book-app .slots-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        /* Form Section */
        #sage-book-app .form-container {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        #sage-book-app .form-container h2 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            color: #111827;
        }
        #sage-book-app .form-container h2 i {
            width: 1.5rem;
            height: 1.5rem;
            margin-right: 0.5rem;
            color: #059669;
        }
        #sage-book-app .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        #sage-book-app .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        #sage-book-app .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        #sage-book-app .form-group {
            margin-bottom: 0;
        }
        #sage-book-app .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }
        #sage-book-app .form-group input,
        #sage-book-app .form-group select {
            width: 100%;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            background-color: white;
            color: #0f172a;
            outline: none;
            transition: all 0.2s;
            height: auto;
        }
        /* for onyly placehoder color */
        input::placeholder{
            color: "#9b9b9b" !important;
        }
        .nice-select{
            display: none !important;
        }
        select{
            display: block !important;
        }
        #sage-book-app .form-group input:focus,
        #sage-book-app .form-group select:focus {
            box-shadow: 0 0 0 2px #10b981;
            border-color: #10b981;
        }
        
        /* File Upload - Reference Match */
        #sage-book-app #drop-zone {
            position: relative;
            border: 1px dashed #cbd5e1;
            border-radius: 1rem;
            padding: 2.5rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        #sage-book-app #drop-zone:hover {
            border-color: #059669;
            background-color: rgba(16, 185, 129, 0.02);
        }
        #sage-book-app #drop-zone input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }
        #sage-book-app #drop-zone .upload-icon-outer {
            background-color: #ecfdf5;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            transition: all 0.3s;
        }
        #sage-book-app #drop-zone .upload-icon-inner {
            background-color: #10b981;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
        }
        #sage-book-app #drop-zone:hover .upload-icon-outer {
            transform: scale(1.05);
        }
        #sage-book-app #drop-zone .upload-icon-inner i {
            color: white;
            width: 1.25rem;
            height: 1.25rem;
        }
        #sage-book-app #drop-zone p {
            font-size: 1.125rem;
            color: #1e293b;
            font-weight: 500;
            margin: 0;
        }
        #sage-book-app #drop-zone p .upload-link {
            color: #10b981;
            font-weight: 600;
            text-decoration: none;
        }
        #sage-book-app #drop-zone .file-info {
            font-size: 0.875rem;
            color: #94a3b8;
            font-weight: 400;
            margin-top: 0.5rem;
            letter-spacing: 0.025em;
        }
        #sage-book-app #upload-status {
            position: absolute;
            inset: 0;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }
        #sage-book-app #upload-status p {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #sage-book-app #upload-status i {
            animation: spin 1s linear infinite;
            width: 1rem;
            height: 1rem;
        }
        #sage-book-app #file-name-display {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            text-align: center;
            color: #059669;
            font-weight: 500;
        }
        
        /* Form Actions */
        #sage-book-app .form-actions {
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-direction: row;
            gap: 0.75rem;
            justify-content: space-between;
            align-items: center;
        }
        #sage-book-app .btn-submit {
            flex: 2;
            padding: 0.75rem 1rem;
            background-color: #059669;
            color: white;
            font-weight: bold;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: none !important;
        }
        #sage-book-app .btn-submit:hover {
            background-color: #047857;
            transform: translateY(-2px);
        }
        #sage-book-app .btn-submit:active {
            transform: translateY(0);
        }
        #sage-book-app .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        #sage-book-app .form-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }
        #sage-book-app .form-footer i {
            width: 0.75rem;
            height: 0.75rem;
        }
        
        /* Success Screen - Reference Match & Refinement */
        #sage-book-app #view-success {
            text-align: center;
            padding: 3rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 450px;
            background-image: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
        }
        #sage-book-app .success-container {
            width: 100%;
            max-width: 42rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #sage-book-app .success-icon-wrapper {
            margin-bottom: 2rem;
        }
        #sage-book-app .success-check-icon circle {
            stroke-dasharray: 230;
            stroke-dashoffset: 230;
            animation: circle-draw 0.6s ease-out forwards;
        }
        #sage-book-app .success-check-icon polyline {
            stroke-dasharray: 50;
            stroke-dashoffset: 50;
            animation: checkmark-draw 0.4s 0.5s ease-out forwards;
        }
        @keyframes circle-draw { to { stroke-dashoffset: 0; } }
        @keyframes checkmark-draw { to { stroke-dashoffset: 0; } }

        #sage-book-app .success-content h1 {
            font-size: 1.875rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }
        #sage-book-app .success-content p {
            color: #64748b;
            font-size: 1.125rem;
            margin-bottom: 2.5rem;
        }
        #sage-book-app .appointment-card {
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(226, 232, 240, 0.5);
            padding: 2rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 36rem;
            text-align: left;
        }
        #sage-book-app .appointment-card-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 0.375rem;
            background-color: #10b981;
        }
        #sage-book-app .appointment-card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }
        #sage-book-app .appointment-date-box {
            flex-shrink: 0;
            width: 5rem;
            height: 6rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
        }
        #sage-book-app .appointment-date-month {
            background-color: #e2e8f0;
            width: 100%;
            padding: 0.25rem 0;
            font-size: 0.625rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            text-align: center;
        }
        #sage-book-app .appointment-date-day {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.875rem;
            font-weight: bold;
            color: #1e293b;
        }
        #sage-book-app .appointment-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }
        #sage-book-app .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #475569;
            font-size: 0.9375rem;
        }
        #sage-book-app .detail-item i {
            width: 1.25rem;
            height: 1.25rem;
            color: #94a3b8;
            flex-shrink: 0;
        }

        #sage-book-app .success-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        #sage-book-app .btn-calendar,
        #sage-book-app .btn-new-appt {
            flex: 1;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        #sage-book-app .btn-calendar {
            background-color: white;
            border-color: #e2e8f0;
            color: #1e293b;
        }
        #sage-book-app .btn-calendar:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }
        #sage-book-app .btn-new-appt {
            background-color: #10b981;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
        }
        #sage-book-app .btn-new-appt:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        #sage-book-app .success-footer {
            margin-top: 2rem;
            color: #94a3b8;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #sage-book-app .success-footer i {
            width: 1rem;
            height: 1rem;
        }

        #sage-book-app input::placeholder {
    color: #94a3b8 !important;
}

#sage-book-app textarea::placeholder {
    color: #94a3b8 !important;
}



        @media (min-width: 640px) {
            #sage-book-app .appointment-card-body {
                flex-direction: row;
                align-items: center;
                gap: 2.5rem;
            }
            #sage-book-app .date-box {
                margin: 0;
            }
            #sage-book-app .success-content h1 {
                font-size: 2.25rem;
            }
        }
        
        /* Animations */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes ping {
            75%, 100% {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        /* Responsive Breakpoints */
        @media (min-width: 640px) {
            #sage-book-app .step-nav {
                flex-direction: row;
                gap: 1.5rem;
            }
            #sage-book-app .btn-continue {
                flex: none;
                width: auto;
                padding-left: 3rem;
                padding-right: 3rem;
            }
            #sage-book-app .btn-secondary {
                flex: none;
                width: auto;
                padding-left: 2.5rem;
                padding-right: 2.5rem;
            }
            #sage-book-app .form-actions {
                flex-direction: row;
                gap: 1.5rem;
            }
            #sage-book-app .btn-submit {
                flex: none;
                width: auto;
                padding-left: 3rem;
                padding-right: 3rem;
            }
            #sage-book-app #slots-container .slot-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            #sage-book-app .success-actions {
                flex-direction: row;
            }
            #sage-book-app .btn-book-another {
                width: auto;
            }
            #sage-book-app .success-title {
                font-size: 2.25rem;
            }
        }
        @media (min-width: 768px) {
            #sage-book-app .main-layout {
                flex-direction: row;
            }
            #sage-book-app #appointment-sidebar {
                width: 33.333333%;
            }
            #sage-book-app #appointment-main-content.with-sidebar {
                width: 66.666667%;
            }
            #sage-book-app .service-card {
                flex-direction: row;
                align-items: flex-start;
            }
            #sage-book-app .service-info {
                text-align: left;
            }
            #sage-book-app .service-meta {
                flex-direction: column;
                width: auto;
            }
            #sage-book-app .form-container {
                padding: 2rem;
            }
            #sage-book-app .form-grid-2 {
                grid-template-columns: repeat(2, 1fr);
            }
            #sage-book-app .form-grid-3 {
                grid-template-columns: repeat(3, 1fr);
            }
            #sage-book-app .appointment-card-content {
                flex-direction: row;
            }
            #sage-book-app .appointment-details {
                text-align: left;
            }
            #sage-book-app .appointment-detail-row {
                justify-content: flex-start;
            }
        }
        @media (min-width: 1024px) {}
        @media (min-width: 1280px) {}
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <div id="sage-book-app" class="sage-booking-container">
        
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="hidden">
            <div></div>
        </div>

        <!-- Steps Header -->

        <div class="main-layout" style="margin: 50px 0px;">
            
            <!-- Left Sidebar: Steps/Summary -->
            <div id="appointment-sidebar">
                 <!-- Step 1 Trigger -->
                <div id="step-1-trigger" class="sidebar-step active">
                    <div class="sidebar-step-content">
                        <div class="sidebar-step-inner">
                            <i data-lucide="user-plus"></i>
                            <span id="summary-service-name">Consultation</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron"></i>
                    </div>
                </div>

                <!-- Step 2 Trigger -->
                 <div id="step-2-trigger" class="sidebar-step inactive">
                    <div class="sidebar-step-content">
                        <div class="sidebar-step-inner">
                            <i data-lucide="calendar"></i>
                            <span id="summary-date-time">Date, Time & Consultant</span>
                        </div>
                         <i data-lucide="chevron-right" class="chevron"></i>
                    </div>
                </div>

                <!-- Step 3 Trigger -->
                <div id="step-3-trigger" class="sidebar-step inactive">
                    <div class="sidebar-step-content">
                        <div class="sidebar-step-inner">
                            <i data-lucide="user"></i>
                            <span>Your Info</span>
                        </div>
                         <i data-lucide="chevron-right" class="chevron hidden"></i>
                    </div>
                </div>
            </div>

            <!-- Right Content Area -->
            <div id="appointment-main-content" class="with-sidebar">
                
                <div class="booking-header" style="text-align: left; margin-bottom: 1.5rem;">
                    <h1 id="booking-main-title" style="margin: 0; font-size: 1.5rem; text-align: left;">Enter Appointment Details</h1>
                </div>

                <!-- STEP 1: Select Service/Doctor -->
                <div id="view-step-1" class="step-view">
                    <div class="step-header">
                        <h2>Select Provider</h2>
                        <p>Choose a healthcare professional for your visit.</p>
                    </div>

                    <div id="services-list">
                        <!-- Services injected here -->
                        <div id="services-skeleton">
                            <!-- Skeleton Card 1 -->
                            <div class="skeleton-card">
                                <div class="skeleton-avatar">
                                    <div class="skeleton-avatar-img"></div>
                                </div>
                                <div class="skeleton-content">
                                    <div class="skeleton-title"></div>
                                    <div class="skeleton-text-full"></div>
                                    <div class="skeleton-text-3q"></div>
                                </div>
                                <div class="skeleton-meta">
                                    <div class="skeleton-duration"></div>
                                    <div class="skeleton-radio"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-nav" style="justify-content: end;">
                        <button id="btn-step1-continue" onclick="handleStep1Continue(this)" class="btn-continue" disabled>
                            <span id="btn-step1-text">Continue to Step 2</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Date & Time -->
                <div id="view-step-2" class="step-view hidden">
                    <div class="step-header">
                        <h2>Step 2: Choose Date and Time</h2>
                        <p>Your appointment will be booked with <span id="selected-doctor-name">Doctor</span></p>
                    </div>

                    <div class="calendar-header">
                        <div class="month-selector">
                            <button id="month-trigger-btn" class="month-trigger-btn">
                                <h3 id="current-month-label">September, 2024</h3>
                                <i data-lucide="chevron-down" style="color: black;"></i>
                            </button>

                            <!-- Custom Month Picker Overlay -->
                            <div id="custom-month-picker" class="hidden">
                                <!-- Year Navigation -->
                                <div class="month-picker-year-nav">
                                    <button id="picker-prev-year">
                                        <i data-lucide="chevron-left"></i>
                                    </button>
                                    <span id="picker-year-label" class="month-picker-year-label">2024</span>
                                    <button id="picker-next-year">
                                        <i data-lucide="chevron-right"></i>
                                    </button>
                                </div>
                                <!-- Months Grid -->
                                <div id="month-picker-grid">
                                    <!-- Months injected here -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- <div>
                            <button id="btn-month-picker-icon">
                                <i data-lucide="calendar"></i>
                            </button>
                        </div> -->
                    </div>

                    <!-- Days Scroller -->
                    <div class="days-scroller">
                        <button id="prev-week-btn" class="week-nav-btn">
                            <i data-lucide="chevron-left"></i>
                        </button>

                        <div class="days-track-wrapper">
                            <div id="calendar-days-track" class="custom-scrollbar">
                                <!-- Days injected -->
                            </div>
                            <!-- Loading Overlay -->
                            <div id="calendar-loading" class="hidden">
                                 <div>
                                     <div class="spinner"></div>
                                     <span>Loading...</span>
                                 </div>
                            </div>
                        </div>

                        <button id="next-week-btn" class="week-nav-btn">
                            <i data-lucide="chevron-right"></i>
                        </button>
                    </div>

                    <!-- Slots -->
                    <div id="slots-container">
                        <div>Select a date to view availability</div>
                    </div>

                    <!-- Navigation Actions -->
                    <div class="step-nav step-nav-with-border">
                        <button onclick="setStep(1)" class="btn-secondary">
                            <!-- <i data-lucide="arrow-left"></i> -->
                            Back
                        </button>
                        <button id="btn-step2-continue" onclick="if(state.selectedSlot) setStep(3);" class="btn-continue" disabled>
                            Continue to Personal Info
                        </button>
                    </div>
                </div>

                <!-- STEP 3: User Details -->
                <div id="view-step-3" class="step-view hidden">
                    
                    <div class="form-container">
                        <h2>
                            <i data-lucide="clipboard-list"></i>
                            Please enter your details
                        </h2>
                        
                        <form id="booking-form" style="display: flex;
    flex-direction: column;
    gap: 1.25rem;">
                            <!-- Appointment Type -->
                            <div class="form-group">
                                <label for="appointment_type">Appointment Type *</label>
                                <select id="appointment_type" name="appointment_type" required>
                                    <option value="" disabled>Select Appointment Type</option>
                                    <option value="New Appointment" selected>New Appointment</option>
                                    <option value="Follow-up Appointment">Follow-up Appointment</option>
                                </select>
                            </div>

                            <!-- Personal Info -->
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input id="first_name" name="first_name" placeholder="John" required type="text"/>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input id="last_name" name="last_name" placeholder="Doe" required type="text"/>
                                </div>
                            </div>
                            
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input id="email" name="email" placeholder="john.doe@example.com" required type="email"/>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input id="phone" name="phone" placeholder="(555) 000-0000" required type="tel"/>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="dob">Date of Birth *</label>
                                <input id="dob" name="dob" required type="date"/>
                            </div>
                            
                            <div class="form-group">
                                <label for="reason_for_visit">Reason For Visit *</label>
                                <textarea id="reason_for_visit" name="reason_for_visit" rows="3" required placeholder="Briefly describe the reason for your visit" style="width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background-color: white; color: #0f172a; outline: none; transition: all 0.2s; font-family: inherit;"></textarea>
                            </div>

                            <!-- Address -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="address_street">Street Address *</label>
                                    <input id="address_street" name="address_street" placeholder="123 Main St" required type="text"/>
                                </div>
                                <div class="form-grid-3">
                                    <div class="form-group">
                                        <label for="address_city">City *</label>
                                        <input id="address_city" name="address_city" placeholder="City" required type="text"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="address_state">State *</label>
                                        <input id="address_state" name="address_state" placeholder="State" required type="text"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="address_zip">ZIP Code *</label>
                                        <input id="address_zip" name="address_zip" placeholder="ZIP Code" required type="text"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Insurance -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="insurance_info">Insurance Info *</label>
                                    <select id="insurance_info" name="insurance_info" required>
                                        <option disabled selected value="">Select Insurance</option>
                                        <option value="Medicare">Medicare</option>
                                        <option value="Medicare with Advantage Plan">Medicare with Advantage Plan</option>
                                        <option value="Worker's Compensation">Worker's Compensation</option>
                                        <option value="Auto/Motor Vehicle">Auto/Motor Vehicle</option>
                                        <option value="Blue Cross">Blue Cross</option>
                                        <option value="Ucare">Ucare</option>
                                        <option value="HealthPartners">HealthPartners</option>
                                        <option value="Medica">Medica</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Insurance Card Image</label>
                                    <div id="drop-zone">
                                        <input accept="image/*,application/pdf" id="insurance-upload" name="insurance_file" type="file"/>
                                        <div style="display: flex;
    flex-direction: column;
    align-items: center;">
                                            <div class="upload-icon-outer">
                                                <div class="upload-icon-inner">
                                                    <i data-lucide="upload-cloud"></i>
                                                </div>
                                            </div>
                                            <p>
                                                <span class="upload-link">Upload a file</span> or drag and drop
                                            </p>
                                            <p class="file-info">PNG, JPG, PDF up to 1MB</p>
                                        </div>
                                        <input type="hidden" name="insurance_card_url" id="insurance_card_url">
                                        <div id="upload-status" class="hidden">
                                             <p>
                                                 <i data-lucide="loader"></i> Uploading...
                                             </p>
                                        </div>
                                    </div>
                                    <p id="file-name-display" class="hidden"></p>
                                </div>
                            </div>
                            
                            <!-- Optional Demographics -->
                            <div class="demographics-section" style="margin-top: 1.25rem;">

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="pharmacy_phone">What is your preferred pharmacy phone number? *</label>
                                        <input id="pharmacy_phone" name="pharmacy_phone" placeholder="(555) 000-0000" type="text" required/>
                                    </div>
                                    <div class="form-group">
                                        <label for="referring_provider">Referring Provider</label>
                                        <input id="referring_provider" name="referring_provider" placeholder="Enter provider name" type="text"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="primary_provider">Who is primary provider ?</label>
                                        <input id="primary_provider" name="primary_provider" placeholder="Enter primary provider" type="text"/>
                                    </div>

                                    <div class="form-group">
                                        <label for="language">Language *</label>
                                        <select id="language" name="language" required>
                                            <option value="">Select Language</option>
                                            <option value="English">English</option>
                                            <option value="Spanish">Spanish</option>
                                            <option value="Other Indo-European Languages (e.g., German, French)">Other Indo-European Languages (e.g., German, French)</option>
                                            <option value="Asian and Pacific Islander Languages">Asian and Pacific Islander Languages</option>
                                            <option value="Other Languages (All other non-English, non-Spanish)">Other Languages (All other non-English, non-Spanish)</option>
                                        </select>
                                    </div>

                                    <div class="form-grid-3">
                                        <div class="form-group">
                                            <label for="sex">Sex *</label>
                                            <select id="sex" name="sex" required>
                                                <option value="">Select</option>
                                                <option value="Female">Female</option>
                                                <option value="Male">Male</option>
                                                <option value="Prefer not to answer">Prefer not to answer</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ethnicity">Ethnicity *</label>
                                            <select id="ethnicity" name="ethnicity" required>
                                                <option value="">Select</option>
                                                <option value="Not Hispanic or Latino (White)">Not Hispanic or Latino (White)</option>
                                                <option value="Not Hispanic or Latino (Two or More Races)">Not Hispanic or Latino (Two or More Races)</option>
                                                <option value="Not Hispanic or Latino (Asian)">Not Hispanic or Latino (Asian)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="race">Race *</label>
                                            <select id="race" name="race" required>
                                                <option value="">Select</option>
                                                <option value="White">White</option>
                                                <option value="Asian">Asian</option>
                                                <option value="Black or African American">Black or African American</option>
                                                <option value="American Indian/Alaska Native">American Indian/Alaska Native</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Anything more about you? -->
                            <div class="form-group" style="background-color: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="checkbox" id="more_info_toggle" style="width: 1.25rem; height: 1.25rem; accent-color: #10b981; cursor: pointer;">
                                    <label for="more_info_toggle" style="margin: 0; font-weight: 600; color: #334155; cursor: pointer; flex: 1;">Anything more about you? <span style="font-weight: 400; color: #64748b; font-size: 0.8em;">(Optional)</span></label>
                                </div>
                                <div id="more_info_container" class="hidden" style="margin-top: 1rem; transition: all 0.3s ease;">
                                    <label for="more_info" style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 500; color: #475569;">Please share any additional details:</label>
                                    <textarea id="more_info" name="more_info" rows="3" placeholder="Medical history, specific concerns, etc..." style="width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background-color: white; color: #0f172a; outline: none; transition: all 0.2s; font-family: inherit;"></textarea>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="step-nav step-nav-with-border">
                                <button onclick="setStep(2)" class="btn-secondary" type="button">
                                    <!-- <i data-lucide="arrow-left"></i> -->
                                    Back
                                </button>
                                <button class="btn-submit" type="submit">
                                    <!-- <i data-lucide="calendar-check"></i> -->
                                    Book Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- <p class="mt-8 text-center text-sm text-slate-500 flex items-center justify-center gap-1">
                        <i data-lucide="lock" class="w-3 h-3"></i>
                        Your data is protected by industry-standard encryption and HIPAA compliance.
                    </p> -->
                </div>

                <!-- SUCCESS VIEW -->
                <div id="view-success" class="step-view hidden">
                    <div class="success-container">
                        <div class="success-icon-wrapper">
                            <div class="success-check-icon">
                                <svg fill="none" height="80" viewBox="0 0 80 80" width="80">
                                    <circle class="circle" cx="40" cy="40" r="36" stroke="#10b981" stroke-width="4"/>
                                    <polyline class="checkmark" points="25,40 35,50 55,30" stroke="#10b981" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/>
                                </svg>
                            </div>
                        </div>

                        <div class="success-content">
                            <h1>Appointment Confirmed with <span id="confirm-doctor-name">Doctor</span>!</h1>
                            <p>Your appointment has been successfully scheduled.</p>
                        </div>

                        <!-- Appointment Card -->
                        <div class="appointment-card">
                            <div class="appointment-card-header"></div>
                            
                            <div class="appointment-card-body">
                                <!-- Date Box -->
                                <!-- <div class="date-box">
                                    <div class="date-box-month" id="confirm-month-abbr">
                                        FEB
                                    </div>
                                    <div class="date-box-day" id="confirm-day-number">
                                        20
                                    </div>
                                </div> -->
                                
                                <!-- Details -->
                                <div class="appointment-details">
                                    <div class="detail-item">
                                        <i data-lucide="calendar"></i>
                                        <span id="confirm-datetime">20 Feb 2026 | 07:30 AM</span>
                                    </div>
                                    <div class="detail-item">
                                        <i data-lucide="user"></i>
                                        <span id="confirm-doctor-detail">Doctor Name</span>
                                    </div>
                                    <div class="detail-item">
                                        <i data-lucide="globe"></i>
                                        <span id="confirm-timezone">America/Chicago - CST (-06:00)</span>
                                    </div>

                                    <div class="success-actions">
                                        <!-- <button class="btn-calendar" onclick="addToGoogleCalendar()">
                                            <i data-lucide="calendar-plus"></i>
                                            Add to Calendar
                                        </button> -->
                                        <button class="btn-new-appt" onclick="location.reload()">
                                            <i data-lucide="rotate-ccw"></i>
                                            Book Another
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="success-footer">
                            <i data-lucide="mail"></i>
                            A confirmation email has been sent to your registered email address.
                        </div>
                    </div>
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

            // Toggle logic for "Anything more about you?"
            const moreInfoToggle = document.getElementById('more_info_toggle');
            const moreInfoContainer = document.getElementById('more_info_container');

            if (moreInfoToggle && moreInfoContainer) {
                // Initial state check
                if (moreInfoToggle.checked) {
                    moreInfoContainer.classList.remove('hidden');
                }

                moreInfoToggle.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        moreInfoContainer.classList.remove('hidden');
                        // Focus the textarea for better UX
                        setTimeout(() => document.getElementById('more_info')?.focus(), 100);
                    } else {
                        moreInfoContainer.classList.add('hidden');
                    }
                });
            }
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
                
                // Get current selected month/year from calendarDate
                const selectedMonth = calendarDate.getMonth();
                const selectedYear = calendarDate.getFullYear();
                
                for (let i = 0; i < 12; i++) {
                    const date = new Date(pickerYear, i, 1);
                    const mStr = date.toLocaleString('default', { month: 'short' });
                    
                    const btn = document.createElement('button');
                    btn.className = "month-picker-btn";
                    
                    // Logic to disable past months
                    const isPast = (pickerYear === currentYear && i < currentMonth) || (pickerYear < currentYear);
                    
                    // Check if this is the currently selected month
                    const isSelected = (pickerYear === selectedYear && i === selectedMonth);
                    
                    // Check if this is the current month (today)
                    const isCurrent = (pickerYear === currentYear && i === currentMonth);
                    
                    if (isPast) {
                        btn.classList.add('disabled');
                        btn.disabled = true;
                    } else {
                        btn.classList.add('enabled');
                        btn.onclick = () => {
                            changeMonth(date);
                            container.classList.add('hidden');
                        };
                    }
                    
                    // Add selected state
                    if (isSelected) {
                        btn.classList.add('selected');
                    }
                    
                    // Add current month indicator
                    if (isCurrent) {
                        btn.classList.add('current');
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
            state.weekStartDate = newStart;
            await loadWeekData(false, 0, false); // No auto-select on manual month change
            
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
                const successView = document.getElementById('view-success');
                if (successView) {
                    successView.classList.remove('hidden');
                }
                
                // Full screen layout for success
                document.getElementById('appointment-sidebar').classList.add('hidden');
                document.getElementById('appointment-main-content').classList.remove('with-sidebar');
                
                // Hide Main Title on Success Page
                const mainTitle = document.getElementById('booking-main-title');
                if(mainTitle) mainTitle.classList.add('hidden');
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
            } else {
                const stepView = document.getElementById(`view-step-${step}`);
                if (stepView) {
                    stepView.classList.remove('hidden');
                }
                
                // Normal layout
                document.getElementById('appointment-sidebar').classList.remove('hidden');
                document.getElementById('appointment-main-content').classList.add('with-sidebar');
                
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
                const icon = el.querySelector('i:last-child'); 
                const text = el.querySelector('span');
                
                // Reset click handler
                el.onclick = () => {
                     if (s < state.step || s <= state.maxStep) {
                         setStep(s);
                     }
                };
                
                if (s === state.step) {
                    // Active Step
                    el.className = "sidebar-step active";
                    if(icon) {
                        icon.classList.remove('hidden');
                    }
                } else if (s < state.step) {
                    // Completed Step
                    el.className = "sidebar-step completed";
                    if(icon) {
                        icon.classList.remove('hidden');
                    }
                } else {
                    // Future Step
                     el.className = "sidebar-step inactive";
                     if(icon) {
                        icon.classList.add('hidden');
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
                document.getElementById('services-list').innerHTML = `<div style="color: #ef4444; padding: 1rem;">Failed to load services. Please refresh.</div>`;
            }
        }

        function renderServices(list) {
            const container = document.getElementById('services-list');
            const skeleton = document.getElementById('services-skeleton');
            
            // Hide skeleton
            if (skeleton) skeleton.remove();
            
            container.innerHTML = '';

            if (list.length === 0) {
                 container.innerHTML = `<div style="color: #6b7280; padding: 1rem;">No services found.</div>`;
                 return;
            }

            list.forEach(svc => {
                const name = svc.name || "Doctor";
                const avatarUrl = svc.photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=10b981&color=fff`;
                const isSelected = state.selectedService && state.selectedService.id === svc.id;
                
                const el = document.createElement('div');
                el.className = isSelected ? 'service-card selected' : 'service-card';
                el.id = `service-card-${svc.id}`;
                el.onclick = () => selectService(svc);
                
                const radioHtml = isSelected
                    ? `<div class="service-radio selected"><div class="service-radio-dot"></div></div>`
                    : `<div class="service-radio"></div>`;
                
                el.innerHTML = `
                    <div class="service-avatar">
                         <img src="${avatarUrl}" alt="${name}">
                    </div>
                    
                    <div class="service-info">
                         <h3>${name}</h3>
                         <p class="service-description">${svc.description ? svc.description.split('Log in')[0] : 'Interventional Pain Medicine'}</p>
                    </div>
                    
                    <div class="service-meta">
                         <div class="service-duration">
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
            
            // Trigger auto-search from today
            // UPDATE: Don't loadWeekData here if we want to defer loading to "Continue" click?
            // User requested loading on Step 2 button click.
            // But we need to know availability to enable button? 
            // Actually, usually "Continue" just goes to step 2.
            // If we load data HERE, it might be separate.
            // Current flow: Select Service -> Enable Button -> Click Continue -> Load Data -> Show Step 2.
            
            // To support user request: "shoe the Loading loader in the Button until API have Response and select the Avalable Slot"
            // We should NOT load data here, OR if we do, we re-load it on button click?
            // Better UX: Load data here silently? 
            // User specifically asked for button loading. So we move the loadWeekData call to the button click handler.
            // state.weekStartDate = getStartOfWeek(new Date()); 
            // await loadWeekData(true, 0, true); 
            
            // Revert: We still need to reset start date
            state.weekStartDate = getStartOfWeek(new Date()); 
        }

        async function handleStep1Continue(btn) {
            if (!state.selectedService) return;
            
            // UI Loading State
            const textSpan = document.getElementById('btn-step1-text');
            const originalText = textSpan ? textSpan.innerHTML : 'Continue to Step 2';
            
            btn.disabled = true;
            // Custom SVG Spinner
            const spinner = `<svg class="animate-spin" style="width: 1.25rem; height: 1.25rem; color: white;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle style="opacity: 0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path style="opacity: 0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>`;
            
            btn.innerHTML = `<div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">${spinner} <span>Processing...</span></div>`;
            // lucide.createIcons(); // No longer needed for custom SVG
            
            try {
                // Trigger availability check & auto-selection
                // We force autoAdvance=true and autoSelect=true to ensure we find a slot
                await loadWeekData(true, 0, true);
                
                // Move to next step
                setStep(2);
            } catch (e) {
                console.error("Error loading step 2:", e);
                // alert("Failed to load availability. Please try again.");
            } finally {
                // Restore button
                btn.disabled = false;
                btn.innerHTML = `<span id="btn-step1-text">${originalText}</span>`;
            }
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
                    await loadWeekData(false, 0, false); // Manual nav: no auto-select
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
            // Only auto-advance if going forward (direction > 0) -> CHANGED: No auto-advance on manual, BUT auto-select first day
            await loadWeekData(false, 0, true);
        }
        
        
        
        async function loadWeekData(searchNext = false, depth = 0, autoSelect = false) {
            const loadingEl = document.getElementById('calendar-loading');
            const trackEl = document.getElementById('calendar-days-track');
            const slotsContainer = document.getElementById('slots-container');
            const MAX_DEPTH = 12; // Approx 3 months search limit
            
            // Show loading
            if(loadingEl) loadingEl.classList.remove('hidden');

            if(trackEl && depth === 0) trackEl.innerHTML = ''; // Clear only on initial call to avoid flicker during recursing? Actually maybe always clear
            
            // Hide slots until a date is selected
            if (slotsContainer) slotsContainer.innerHTML = '<div style="text-align: center; color: #6b7280; padding: 2.5rem;">Select a date to view availability</div>';
            
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
            const minDisplayTime = new Promise(resolve => setTimeout(resolve, 500)); 
            
            // Render calendar structure first (without data)
            renderCalendarDays();
            
            // Fetch availability data
            const dataFetch = fetchWeekAvailability();
            
            // Wait for both to complete
            await Promise.all([dataFetch, minDisplayTime]);
            
            // Check for availability to auto-select
            const firstDayWithSlots = findFirstDayWithSlots();
            
            // ONLY Auto-select if requested (initial load)
            if (firstDayWithSlots && autoSelect) {
                // Found slots! Select the day
                selectDate(firstDayWithSlots);
                
                // Hide loading state and show slots
                if(loadingEl) loadingEl.classList.add('hidden');
                if(slotsContainer) slotsContainer.classList.remove('hidden');
                
            } else {
                // No slots in this week
                if (searchNext && depth < MAX_DEPTH) {
                    // Auto-advance to next week
                    const nextDate = new Date(state.weekStartDate);
                    nextDate.setDate(nextDate.getDate() + 7);
                    state.weekStartDate = nextDate;
                    
                    // Recursive call
                    await loadWeekData(true, depth + 1, true); // Keep autoSelect=true if we are auto-advancing
                } else {
                    // Stop searching
                    if(loadingEl) loadingEl.classList.add('hidden');
                    if(slotsContainer) {
                        slotsContainer.classList.remove('hidden');
                        if (searchNext && depth >= MAX_DEPTH) {
                             slotsContainer.innerHTML = '<div style="text-align: center; color: #ef4444; padding: 2.5rem;">No availability found for the next 3 months.</div>';
                        }
                    }
                }
            }
        }

        function findFirstDayWithSlots() {
            if (!state.selectedService) return null;
            
            const daysToCheck = 7;
            const todayStr = getLocalDateString(new Date());

            for (let i = 0; i < daysToCheck; i++) {
                const d = new Date(state.weekStartDate);
                d.setDate(d.getDate() + i);
                const dateStr = getLocalDateString(d);
                
                // Skip past
                if (dateStr < todayStr) continue;

                const cached = state.slotsCache[dateStr];
                // Check if we have valid slots
                if (cached && 
                    cached.serviceId === state.selectedService.id && 
                    ((Array.isArray(cached.slots) && cached.slots.length > 0) || (typeof cached.slots === 'object' && Object.keys(cached.slots).length > 0))
                   ) {
                    return d;
                }
            }
            return null;
        }

        // --- Helper: Get Start of Week (Monday) ---
        function getStartOfWeek(date) {
            const d = new Date(date);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
            return new Date(d.setDate(diff));
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

            for (let i = 0; i < daysToShow; i++) {
                const date = new Date(state.weekStartDate);
                date.setDate(date.getDate() + i);
                
                const dateStr = getLocalDateString(date);
                const dayName = date.toLocaleString('default', { weekday: 'short' }).toUpperCase();
                const dayNum = date.getDate();
                
                const btn = document.createElement('div');
                btn.id = `day-btn-${dateStr}`;
                
                // Past Day Check
                const isPast = dateStr < todayStr;
                
                // Check if we have cached data for this day
                const cachedData = state.slotsCache[dateStr];
                const hasNoSlots = cachedData && 
                                   cachedData.serviceId === state.selectedService?.id && 
                                   (!Array.isArray(cachedData.slots) || cachedData.slots.length === 0);
                
                if (isPast) {
                    btn.className = "calendar-day past";
                } else if (hasNoSlots) {
                    btn.className = "calendar-day no-slots";
                    btn.title = "No slots available";
                } else if (state.selectedDateStr === dateStr) {
                    btn.className = "calendar-day selected";
                } else {
                    btn.className = "calendar-day";
                }
                
                btn.innerHTML = `
                    <div class="day-number">${dayNum}</div>
                    <div class="day-name">${dayName}</div>
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
                // Mark day as having no slots - update the class
                if(state.selectedDateStr !== dateStr) {
                    btn.className = "calendar-day no-slots";
                    btn.onclick = null;
                    btn.title = "No slots available";
                }
            } else {
                // Day has slots - ensure it's clickable
                if(state.selectedDateStr !== dateStr) {
                    btn.className = "calendar-day";
                    btn.onclick = () => selectDate(new Date(dateStr));
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
            
            // Show/hide loader based on cache
            const loader = document.getElementById('calendar-loading');
            
            if(state.slotsCache[dateStr] && state.slotsCache[dateStr].serviceId === state.selectedService.id) {
                // Slots already cached - render immediately
                if (loader) loader.classList.add('hidden');
                renderSlots(state.slotsCache[dateStr].slots);
            } else {
                // Need to fetch - show loader
                if (loader) loader.classList.remove('hidden');
                
                // Clear slots container to show loader properly
                const slotsContainer = document.getElementById('slots-container');
                if (slotsContainer) slotsContainer.innerHTML = '';
                
                fetchSlotsForDate(dateStr).then(() => {
                    // Hide loader after fetch completes
                    if (loader) loader.classList.add('hidden');
                });
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
                 container.innerHTML = `<div style="text-align: center; color: #6b7280; padding: 2.5rem;">No availability for this date.</div>`;
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
                btn.className = isSelected ? 'slot-btn selected' : 'slot-btn';
                btn.innerText = formatTimeDisplay(time);
                btn.onclick = () => selectSlot(time);
                return btn;
            };

            // Render Morning Section
            if (morningSlots.length > 0) {
                 const section = document.createElement('section');
                 section.className = "slots-section";
                 section.innerHTML = `
                    <h4 class="slots-section-title">
                        <i data-lucide="sun"></i> Morning
                    </h4>
                    <div class="slots-grid"></div>
                 `;
                 const list = section.querySelector('.slots-grid');
                 morningSlots.forEach(t => list.appendChild(createSlotBtn(t)));
                 container.appendChild(section);
            }

            // Render Afternoon Section
            if (afternoonSlots.length > 0) {
                 const section = document.createElement('section');
                 section.className = "slots-section";
                 section.innerHTML = `
                    <h4 class="slots-section-title">
                        <i data-lucide="sunset"></i> Afternoon
                    </h4>
                    <div class="slots-grid"></div>
                 `;
                 const list = section.querySelector('.slots-grid');
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
                // additional_fields as JSON string
                // Map new optional fields + DOB
                const additionalFields = {
                    "Appointment Type": data.appointment_type || "",
                    "What is your preferred pharmacy phone number?": data.pharmacy_phone || "",
                    "Referring Provider": data.referring_provider || "",
                    "Who is primary provider ?": data.primary_provider || "",
                    "Language": data.language || "",
                    "Sex": data.sex || "",
                    "Ethnicity": data.ethnicity || "",
                    "Race": data.race || "",
                    "Reason For Visit": data.reason_for_visit || "",
                    "Anything more about you?": data.more_info || "",
                    "Date Of Birth": formattedDOB,
                    "Street Address": data.address_street || "",
                    "City": data.address_city || "",
                    "State": data.address_state || "",
                    "ZIP Code": data.address_zip || "",
                    "Insurance Info": data.insurance_info || "",
                    "Insurance Card": data.insurance_card_url || ""
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
