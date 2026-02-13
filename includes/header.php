<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>StudyTrack</title>
    <style>
        /* Design System - StudyTrack */
        :root[data-theme="dark"] {
            /* Colors */
            --bg-primary: #000000;
            --bg-secondary: #1A1A1A;
            --bg-tertiary: #2A2A2A;
            --text-primary: #FFFFFF;
            --text-secondary: #AAAAAA;
            --text-tertiary: #666666;
            
            /* Brand Colors */
            --brand-blue: #2B7FD6;
            --brand-purple: #8B5CF6;
            
            /* Event Colors */
            --color-notice: #F97316;
            --color-assignment: #3B82F6;
            --color-exam: #8B5CF6;
            --color-presentation: #10B981;
            --color-meeting: #F59E0B;
            --color-other: #6B7280;
            
            /* Semantic Colors */
            --success: #10B981;
            --warning: #F59E0B;
            --error: #EF4444;
            --pending: #F97316;
            
            /* Spacing */
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-2xl: 48px;
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 9999px;
            
            /* Typography */
            --font-size-xs: 12px;
            --font-size-sm: 14px;
            --font-size-base: 16px;
            --font-size-lg: 18px;
            --font-size-xl: 24px;
            --font-size-2xl: 32px;
            --font-size-3xl: 48px;
            
            /* Shadows */
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 8px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.7);
        }
        
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: var(--font-size-base);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-md);
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-size: var(--font-size-base);
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: var(--spacing-sm);
        }
        
        .btn-primary {
            background: var(--brand-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2369B8;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--text-tertiary);
        }
        
        .btn-secondary:hover {
            background: #333333;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-danger {
            background: var(--error);
            color: white;
        }
        
        .btn-full {
            width: 100%;
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        label {
            display: block;
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-tertiary);
            border: 1px solid var(--text-tertiary);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: var(--font-size-base);
            transition: all 0.2s ease;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 3px rgba(43, 127, 214, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        ::placeholder {
            color: var(--text-tertiary);
        }
        
        /* Cards */
        .card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-md);
        }
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-md);
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow-lg);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
        }
        
        .modal-header h2 {
            font-size: var(--font-size-xl);
            color: var(--text-primary);
        }
        
        .close-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .close-btn:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }
        
        .modal-footer {
            display: flex;
            gap: var(--spacing-md);
            margin-top: var(--spacing-xl);
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        
        .badge-pending {
            background: rgba(249, 115, 22, 0.2);
            color: var(--pending);
        }
        
        .badge-approved {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }
        
        .badge-info {
            background: rgba(43, 127, 214, 0.2);
            color: var(--brand-blue);
        }
        
        .badge-high {
            background: rgba(239, 68, 68, 0.2);
            color: var(--error);
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-secondary { color: var(--text-secondary); }
        .text-small { font-size: var(--font-size-sm); }
        .mt-sm { margin-top: var(--spacing-sm); }
        .mt-md { margin-top: var(--spacing-md); }
        .mt-lg { margin-top: var(--spacing-lg); }
        .mb-sm { margin-bottom: var(--spacing-sm); }
        .mb-md { margin-bottom: var(--spacing-md); }
        .mb-lg { margin-bottom: var(--spacing-lg); }
        .flex { display: flex; }
        .flex-center { display: flex; align-items: center; justify-content: center; }
        .gap-sm { gap: var(--spacing-sm); }
        .gap-md { gap: var(--spacing-md); }
        
        /* Page Layout */
        .page-wrapper {
            min-height: 100vh;
            padding-bottom: 80px; /* Space for bottom nav */
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--bg-tertiary);
            position: sticky;
            top: 0;
            background: var(--bg-primary);
            z-index: 100;
        }
        
        .page-header h1 {
            font-size: var(--font-size-xl);
            color: var(--text-secondary);
            font-weight: 400;
        }
        
        .page-content {
            padding: var(--spacing-lg);
        }
        
        /* Responsive */
        @media (min-width: 768px) {
            .container {
                padding: var(--spacing-xl);
            }
            
            .page-content {
                padding: var(--spacing-xl);
            }
        }
    </style>
</head>
<body>
