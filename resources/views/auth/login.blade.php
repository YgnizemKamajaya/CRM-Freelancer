@php
    $setting = \App\Models\Setting::firstOrCreate([], [
        'owner_name' => config('crm.owner_name'),
        'owner_email' => config('crm.owner_email'),
    ]);
    $ownerAvatar = $setting->logo_path ?: $setting->avatar_path;
    $ownerName = $setting->owner_name ?: config('crm.owner_name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <title>Lock Screen - {{ config('app.name', 'Freelance CRM') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Design variables */
            --crm-page: #f8fafc;
            --crm-surface: #ffffff;
            --crm-surface-soft: #f1f5f9;
            --crm-line: #e2e8f0;
            --crm-line-strong: #cbd5e1;
            --crm-heading: #0f172a;
            --crm-text: #334155;
            --crm-muted: #64748b;
            --crm-action: #0f172a;
            --crm-on-action: #ffffff;
            --crm-hover: #f1f5f9;
            --crm-shadow-soft: 0 1px 2px rgba(15, 23, 42, 0.04), 0 10px 24px rgba(15, 23, 42, 0.05);
            --crm-shadow-panel: 0 18px 45px rgba(15, 23, 42, 0.1);
            --radius: 12px;
            --transition: 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        :root[data-theme='sepia'] {
            --crm-page: #f4ecd8;
            --crm-surface: #fbf6eb;
            --crm-surface-soft: #ebdcb9;
            --crm-line: #d9c59c;
            --crm-line-strong: #bca473;
            --crm-heading: #432818;
            --crm-text: #5f3e23;
            --crm-muted: #7f5c3d;
            --crm-action: #99582a;
            --crm-on-action: #ffe3c0;
            --crm-hover: #ebdcb9;
            --crm-shadow-soft: 0 1px 2px rgba(67, 40, 24, 0.07), 0 12px 32px rgba(67, 40, 24, 0.08);
            --crm-shadow-panel: 0 18px 48px rgba(67, 40, 24, 0.14);
        }

        :root[data-theme='dark'] {
            --crm-page: #0b0f19;
            --crm-surface: #121824;
            --crm-surface-soft: #1b2333;
            --crm-line: #222d41;
            --crm-line-strong: #32435e;
            --crm-heading: #f1f5f9;
            --crm-text: #cbd5e1;
            --crm-muted: #94a3b8;
            --crm-action: #f1f5f9;
            --crm-on-action: #0b0f19;
            --crm-hover: #1b2333;
            --crm-shadow-soft: none;
            --crm-shadow-panel: 0 18px 45px rgba(0, 0, 0, 0.46);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--crm-page);
            color: var(--crm-text);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
            transition: background-color var(--transition);
        }

        /* Clean entry card */
        .login-card {
            background: var(--crm-surface);
            border: 1px solid var(--crm-line);
            border-radius: var(--radius);
            box-shadow: var(--crm-shadow-panel);
            width: min(390px, calc(100vw - 2rem));
            padding: 2.25rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.75rem;
            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            transition: background-color var(--transition), border-color var(--transition);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Profile Section */
        .profile-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            text-align: center;
        }

        .avatar-container {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 1px solid var(--crm-line-strong);
            background: var(--crm-surface-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all var(--transition);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-fallback {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--crm-heading);
        }

        .profile-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--crm-heading);
            letter-spacing: -0.01em;
        }

        .profile-role {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--crm-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* PIN dots */
        .pin-display {
            display: flex;
            gap: 1.25rem;
            align-items: center;
            margin-top: 0.25rem;
        }

        .pin-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--crm-line-strong);
            transition: all var(--transition);
        }

        .pin-dot.filled {
            background: var(--crm-heading);
            transform: scale(1.2);
            animation: dotPop 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .pin-dot.error {
            background: #ef4444;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);
            animation: shake 0.5s ease;
        }

        @keyframes dotPop {
            0% { transform: scale(0.6); }
            100% { transform: scale(1.2); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        /* Status message */
        .status-message {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--crm-muted);
            min-height: 1.25rem;
            text-align: center;
            transition: color var(--transition);
        }

        .status-message.error {
            color: #ef4444;
        }

        .status-message.success {
            color: #10b981;
        }

        /* Numpad */
        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 72px);
            grid-template-rows: repeat(4, 56px);
            gap: 0.75rem;
            justify-content: center;
        }

        .num-key {
            background: var(--crm-surface-soft);
            border: 1px solid var(--crm-line);
            border-radius: 8px;
            color: var(--crm-heading);
            font-size: 1.25rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
            outline: none;
            user-select: none;
        }

        .num-key:hover {
            background: var(--crm-hover);
            border-color: var(--crm-line-strong);
            transform: translateY(-1px);
        }

        .num-key:active {
            transform: scale(0.95);
            background: var(--crm-line);
        }

        .num-key.key-delete {
            background: transparent;
            border-color: transparent;
            color: var(--crm-muted);
        }

        .num-key.key-delete:hover {
            background: var(--crm-surface-soft);
            border-color: var(--crm-line);
            color: var(--crm-heading);
        }

        .num-key.key-delete svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .num-key.key-blank {
            background: transparent;
            border-color: transparent;
            cursor: default;
            pointer-events: none;
        }

        /* Success overlay */
        .success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .success-overlay.show {
            opacity: 1;
            pointer-events: all;
        }

        .success-card {
            background: var(--crm-surface);
            border: 1px solid var(--crm-line);
            border-radius: var(--radius);
            padding: 2.5rem;
            text-align: center;
            box-shadow: var(--crm-shadow-panel);
            animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            width: min(340px, calc(100vw - 2rem));
        }

        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .success-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-icon svg {
            width: 24px;
            height: 24px;
            stroke: #10b981;
            fill: none;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .success-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--crm-heading);
        }

        .success-subtitle {
            font-size: 0.85rem;
            color: var(--crm-muted);
        }

        .btn-continue {
            margin-top: 0.5rem;
            padding: 0.75rem 2rem;
            background: var(--crm-action);
            color: var(--crm-on-action);
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            width: 100%;
        }

        .btn-continue:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        .btn-continue:active {
            transform: scale(0.98);
        }

        .fixed {
            position: fixed;
        }
        .z-50 {
            z-index: 50;
        }
        .bottom-6 {
            bottom: 1.5rem;
        }
        .right-6 {
            right: 1.5rem;
        }
        .mb-3 {
            margin-bottom: 0.75rem;
        }
        .rounded-xl {
            border-radius: 0.75rem;
        }
        .bg-white {
            background-color: #ffffff;
        }
        .dark\:bg-gray-800:is([data-theme='dark'] *) {
            background-color: #1f2937;
        }
        .border-gray-200 {
            border-color: #e5e7eb;
        }
        .dark\:border-gray-700:is([data-theme='dark'] *) {
            border-color: #374151;
        }
        .p-2 {
            padding: 0.5rem;
        }
        .shadow-2xl {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .min-w-\[150px\] {
            min-width: 150px;
        }
        .absolute {
            position: absolute;
        }
        .bottom-full {
            bottom: 100%;
        }
        .right-0 {
            right: 0;
        }
        .hidden {
            display: none !important;
        }
        .flex {
            display: flex;
        }
        .flex-col {
            flex-direction: column;
        }
        .gap-1 {
            gap: 0.25rem;
        }
        .gap-3 {
            gap: 0.75rem;
        }
        .w-full {
            width: 100%;
        }
        .px-3 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .rounded-lg {
            border-radius: 0.5rem;
        }
        .text-xs {
            font-size: 0.75rem;
        }
        .font-semibold {
            font-weight: 600;
        }
        .transition-colors {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
        .text-gray-700 {
            color: #374151;
        }
        .dark\:text-gray-300:is([data-theme='dark'] *) {
            color: #d1d5db;
        }
        .hover\:bg-gray-100:hover {
            background-color: #f3f4f6;
        }
        .dark\:hover\:bg-gray-700:hover:is([data-theme='dark'] *) {
            background-color: #374151;
        }
        .ml-2 {
            margin-left: 0.5rem;
        }
        .items-center {
            align-items: center;
        }
        .justify-center {
            justify-content: center;
        }
        .rounded-full {
            border-radius: 9999px;
        }
        .border-gray-200 {
            border-color: #e5e7eb;
        }
        .hover\:scale-110:hover {
            transform: scale(1.1);
        }
        .active\:scale-95:active {
            transform: scale(0.95);
        }
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
        .duration-200 {
            transition-duration: 200ms;
        }
        .focus\:outline-none:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }
        .text-amber-500 {
            color: #f59e0b;
        }
        .text-orange-500 {
            color: #f97316;
        }
        .text-indigo-400 {
            color: #818cf8;
        }
    </style>
</head>

<body>

    <!-- Lock Card -->
    <div class="login-card" id="lockCard">
        <!-- Profile -->
        <div class="profile-section">
            <div class="avatar-container">
                @if ($ownerAvatar)
                    <img src="{{ asset('storage/'.$ownerAvatar) }}" alt="" class="avatar-img">
                @else
                    <span class="avatar-fallback">{{ str($ownerName)->substr(0, 1)->upper() }}</span>
                @endif
            </div>
            <div class="profile-name">{{ $ownerName }}</div>
            <div class="profile-role">Workspace Owner</div>
        </div>

        <!-- PIN Dots -->
        <div class="pin-display" id="pinDisplay">
            <div class="pin-dot" data-index="0"></div>
            <div class="pin-dot" data-index="1"></div>
            <div class="pin-dot" data-index="2"></div>
            <div class="pin-dot" data-index="3"></div>
        </div>

        <!-- Status -->
        <div class="status-message" id="statusMessage">Enter PIN to unlock</div>

        <!-- Numpad -->
        <div class="numpad" id="numpad">
            <button class="num-key" data-value="1">1</button>
            <button class="num-key" data-value="2">2</button>
            <button class="num-key" data-value="3">3</button>
            <button class="num-key" data-value="4">4</button>
            <button class="num-key" data-value="5">5</button>
            <button class="num-key" data-value="6">6</button>
            <button class="num-key" data-value="7">7</button>
            <button class="num-key" data-value="8">8</button>
            <button class="num-key" data-value="9">9</button>
            <button class="num-key key-blank"></button>
            <button class="num-key" data-value="0">0</button>
            <button class="num-key key-delete" data-value="delete" title="Delete">
                <svg viewBox="0 0 24 24">
                    <path d="M21 4H8l-7 8 7 8h13a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z" />
                    <line x1="18" y1="9" x2="12" y2="15" />
                    <line x1="12" y1="9" x2="18" y2="15" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Success Overlay -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-card">
            <div class="success-icon">
                <svg viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <div class="success-title">Welcome Back!</div>
            <div class="success-subtitle">Redirecting to your dashboard...</div>
            <button class="btn-continue" id="btnContinue">Enter Dashboard</button>
        </div>
    </div>

    <!-- Floating Theme Toggle Selector -->
    <div class="fixed z-50 bottom-6 right-6" id="theme-toggle-container">
        <!-- Collapsible Menu -->
        <div id="theme-menu" class="absolute bottom-full right-0 mb-3 flex flex-col gap-1 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-2 shadow-2xl min-w-[150px] hidden"
             style="box-shadow: var(--crm-shadow-panel);">
            <!-- Morning Sun -->
            <button type="button" data-theme-btn="light"
                    class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-xs font-semibold transition-colors text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    style="border: 0; cursor: pointer; background: transparent; display: flex; align-items: center;">
                <svg class="text-amber-500" style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <span class="ml-2">Morning Sun</span>
            </button>
            <!-- Sunset -->
            <button type="button" data-theme-btn="sepia"
                    class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-xs font-semibold transition-colors text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    style="border: 0; cursor: pointer; background: transparent; display: flex; align-items: center;">
                <svg class="text-orange-500" style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 10a4 4 0 0 0-4 4h8a4 4 0 0 0-4-4z"></path>
                    <path d="M2 20h20M12 2v3M4.93 6.93l2.12 2.12M19.07 6.93l-2.12 2.12"></path>
                </svg>
                <span class="ml-2">Sunset</span>
            </button>
            <!-- Moon -->
            <button type="button" data-theme-btn="dark"
                    class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-xs font-semibold transition-colors text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    style="border: 0; cursor: pointer; background: transparent; display: flex; align-items: center;">
                <svg class="text-indigo-400" style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
                <span class="ml-2">Moon</span>
            </button>
        </div>

        <!-- Floating Trigger Button -->
        <button type="button" id="theme-trigger-btn"
                class="flex items-center justify-center rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 shadow-2xl hover:scale-110 active:scale-95 transition-all duration-200 focus:outline-none"
                style="cursor: pointer; box-shadow: var(--crm-shadow-panel); width: 2.75rem; height: 2.75rem;"
                aria-label="Toggle Theme">
            <!-- Sun Icon -->
            <svg id="theme-icon-light" class="text-amber-500 hidden" style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <!-- Sunset Icon -->
            <svg id="theme-icon-sepia" class="text-orange-500 hidden" style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 10a4 4 0 0 0-4 4h8a4 4 0 0 0-4-4z"></path>
                <path d="M2 20h20M12 2v3M4.93 6.93l2.12 2.12M19.07 6.93l-2.12 2.12"></path>
            </svg>
            <!-- Moon Icon -->
            <svg id="theme-icon-dark" class="text-indigo-400 hidden" style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>
    </div>

    <script>
        (function () {
            // ──────────────────────────────────────
            // Theme Management
            // ──────────────────────────────────────
            const themes = ['light', 'sepia', 'dark'];
            const storage = {
                get(key, fallback) {
                    try {
                        return localStorage.getItem(key) ?? fallback;
                    } catch {
                        return fallback;
                    }
                },
                set(key, value) {
                    try {
                        localStorage.setItem(key, value);
                    } catch {}
                },
            };
            const applyTheme = (theme) => {
                const next = themes.includes(theme) ? theme : 'light';
                document.documentElement.dataset.theme = next;
                document.documentElement.style.colorScheme = next === 'dark' ? 'dark' : 'light';
                storage.set('crm-theme', next);
                return next;
            };

            let currentTheme = applyTheme(storage.get('crm-theme', 'light'));

            const themeMenu = document.getElementById('theme-menu');
            const themeTriggerBtn = document.getElementById('theme-trigger-btn');

            themeTriggerBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                themeMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!themeMenu.contains(e.target) && e.target !== themeTriggerBtn) {
                    themeMenu.classList.add('hidden');
                }
            });

            const updateActiveIcon = (theme) => {
                document.getElementById('theme-icon-light').classList.add('hidden');
                document.getElementById('theme-icon-sepia').classList.add('hidden');
                document.getElementById('theme-icon-dark').classList.add('hidden');
                document.getElementById(`theme-icon-${theme}`).classList.remove('hidden');

                document.querySelectorAll('[data-theme-btn]').forEach(btn => {
                    if (btn.getAttribute('data-theme-btn') === theme) {
                        btn.style.background = 'var(--crm-hover)';
                        btn.style.color = 'var(--crm-heading)';
                    } else {
                        btn.style.background = 'transparent';
                        btn.style.color = '';
                    }
                });
            };

            document.querySelectorAll('[data-theme-btn]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const theme = btn.getAttribute('data-theme-btn');
                    currentTheme = applyTheme(theme);
                    updateActiveIcon(theme);
                    themeMenu.classList.add('hidden');
                });
            });

            updateActiveIcon(currentTheme);

            // ──────────────────────────────────────
            // PIN Configuration
            // ──────────────────────────────────────
            const MAX_PIN_LENGTH = 4;
            const AUTO_REDIRECT_DELAY = 1800; // ms before auto-redirect after success

            // DOM elements
            const pinDisplay = document.getElementById('pinDisplay');
            const pinDots = pinDisplay.querySelectorAll('.pin-dot');
            const statusMessage = document.getElementById('statusMessage');
            const numpad = document.getElementById('numpad');
            const numKeys = numpad.querySelectorAll('.num-key[data-value]');
            const successOverlay = document.getElementById('successOverlay');
            const btnContinue = document.getElementById('btnContinue');
            const lockCard = document.getElementById('lockCard');

            // State
            let pin = '';
            let isLocked = false;
            let autoRedirectTimer = null;
            let nextUrl = "{{ route('dashboard') }}";

            // Update PIN dots visually
            function updateDots(animateIndex = true) {
                pinDots.forEach((dot, i) => {
                    if (i < pin.length) {
                        if (i === pin.length - 1 && animateIndex) {
                            dot.classList.remove('filled');
                            void dot.offsetWidth; // Force reflow
                            dot.classList.add('filled');
                        } else {
                            dot.classList.add('filled');
                        }
                        dot.classList.remove('error');
                    } else {
                        dot.classList.remove('filled', 'error');
                    }
                });
            }

            // Show error on dots
            function showErrorOnDots(message) {
                pinDots.forEach(dot => dot.classList.add('error'));
                statusMessage.textContent = message || 'Incorrect PIN. Please try again.';
                statusMessage.classList.add('error');
                statusMessage.classList.remove('success');

                // Shake the card container
                lockCard.style.animation = 'none';
                void lockCard.offsetWidth;
                lockCard.style.animation = 'shake 0.5s ease';
            }

            // Reset UI after error
            function resetAfterError() {
                pin = '';
                updateDots(false);
                pinDots.forEach(dot => dot.classList.remove('error'));
                statusMessage.textContent = 'Enter PIN to unlock';
                statusMessage.classList.remove('error', 'success');
                lockCard.style.animation = '';
                isLocked = false;
            }

            // Handle successful login
            function handleSuccess(targetUrl) {
                isLocked = true;
                if (targetUrl) {
                    nextUrl = targetUrl;
                }
                statusMessage.textContent = 'Access granted!';
                statusMessage.classList.add('success');
                statusMessage.classList.remove('error');
                updateDots(true);

                setTimeout(() => {
                    successOverlay.classList.add('show');
                }, 400);

                autoRedirectTimer = setTimeout(() => {
                    redirectToDashboard();
                }, AUTO_REDIRECT_DELAY);
            }

            // Redirect to dashboard
            function redirectToDashboard() {
                if (autoRedirectTimer) clearTimeout(autoRedirectTimer);
                document.body.style.transition = 'opacity 0.4s ease';
                document.body.style.opacity = '0';
                setTimeout(() => {
                    window.location.href = nextUrl;
                }, 400);
            }

            // Validate PIN against backend
            async function validatePin() {
                if (pin.length < MAX_PIN_LENGTH) return;
                isLocked = true;

                try {
                    const response = await fetch("{{ route('login') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ pin: pin })
                    });

                    if (response.ok) {
                        const targetUrl = response.redirected ? response.url : "{{ route('dashboard') }}";
                        handleSuccess(targetUrl);
                    } else {
                        const data = await response.json();
                        const errorMsg = data.errors?.pin?.[0] || data.message || 'Incorrect PIN.';
                        showErrorOnDots(errorMsg);
                        setTimeout(() => {
                            resetAfterError();
                        }, 1500);
                    }
                } catch (error) {
                    console.error('Error during unlocking:', error);
                    showErrorOnDots('Connection error. Please try again.');
                    setTimeout(() => {
                        resetAfterError();
                    }, 1500);
                }
            }

            // Add digit
            function addDigit(digit) {
                if (isLocked) return;
                if (pin.length >= MAX_PIN_LENGTH) return;

                pin += digit;
                updateDots(true);
                statusMessage.textContent = 'Enter PIN to unlock';
                statusMessage.classList.remove('error', 'success');

                if (pin.length === MAX_PIN_LENGTH) {
                    setTimeout(() => validatePin(), 150);
                }
            }

            // Delete last digit
            function deleteDigit() {
                if (isLocked) return;
                if (pin.length === 0) return;

                pin = pin.slice(0, -1);
                updateDots(false);
                statusMessage.textContent = 'Enter PIN to unlock';
                statusMessage.classList.remove('error', 'success');
            }

            // Click Handlers
            numKeys.forEach(key => {
                key.addEventListener('click', (e) => {
                    e.preventDefault();
                    const value = key.getAttribute('data-value');

                    if (value === 'delete') {
                        deleteDigit();
                    } else if (value !== null && value !== '') {
                        addDigit(value);
                    }
                });
                key.addEventListener('mousedown', (e) => e.preventDefault());
            });

            // Keyboard support
            document.addEventListener('keydown', (e) => {
                if (successOverlay.classList.contains('show')) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        redirectToDashboard();
                    }
                    return;
                }

                if (isLocked) return;
                const key = e.key;

                if (key >= '0' && key <= '9') {
                    e.preventDefault();
                    addDigit(key);
                    highlightNumpadKey(key);
                } else if (key === 'Backspace' || key === 'Delete') {
                    e.preventDefault();
                    deleteDigit();
                    highlightNumpadKey('delete');
                } else if (key === 'Enter') {
                    e.preventDefault();
                    if (pin.length === MAX_PIN_LENGTH) {
                        validatePin();
                    }
                } else if (key === 'Escape') {
                    e.preventDefault();
                    pin = '';
                    updateDots(false);
                    statusMessage.textContent = 'Enter PIN to unlock';
                    statusMessage.classList.remove('error', 'success');
                }
            });

            function highlightNumpadKey(value) {
                const targetKey = numpad.querySelector(`.num-key[data-value="${value}"]`);
                if (targetKey) {
                    targetKey.style.background = 'var(--crm-line)';
                    targetKey.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        targetKey.style.background = '';
                        targetKey.style.transform = '';
                    }, 120);
                }
            }

            btnContinue.addEventListener('click', () => {
                redirectToDashboard();
            });

            successOverlay.addEventListener('click', (e) => {
                if (e.target === successOverlay) {
                    redirectToDashboard();
                }
            });
        })();
    </script>
</body>

</html>
