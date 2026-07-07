@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'home'],
        ['label' => 'Clients', 'route' => 'clients.index', 'pattern' => 'clients.*', 'icon' => 'users'],
        ['label' => 'Deals', 'route' => 'deals.index', 'pattern' => 'deals.*', 'icon' => 'pipeline'],
        ['label' => 'Projects', 'route' => 'projects.index', 'pattern' => 'projects.*', 'icon' => 'briefcase'],
        ['label' => 'Tasks', 'route' => 'tasks.index', 'pattern' => 'tasks.*', 'icon' => 'check'],
        ['label' => 'Invoices', 'route' => 'invoices.index', 'pattern' => 'invoices.*', 'icon' => 'document'],
        ['label' => 'Reports', 'route' => 'reports.index', 'pattern' => 'reports.*', 'icon' => 'chart'],
        ['label' => 'Settings', 'route' => 'settings.edit', 'pattern' => 'settings.*', 'icon' => 'cog'],
    ];
    $setting = \App\Models\Setting::firstOrCreate([], [
        'owner_name' => config('crm.owner_name'),
        'owner_email' => config('crm.owner_email'),
    ]);
    $workspaceLogo = $setting->logo_path;
    $ownerAvatar = $setting->avatar_path;
    $ownerName = $setting->owner_name ?: config('crm.owner_name');
    $ownerEmail = $setting->owner_email ?: config('crm.owner_email');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta id="theme-color-meta" name="theme-color" content="#f8fafc">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
        <title>{{ config('app.name', 'Freelance CRM') }}</title>
        <script>
            (() => {
                const themes = ['light', 'sepia', 'dark'];
                const themeColors = { light: '#f8fafc', sepia: '#efe1cc', dark: '#111827' };
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
                const setMetaColor = (theme) => {
                    const meta = document.querySelector('#theme-color-meta');
                    if (meta) meta.setAttribute('content', themeColors[theme] ?? themeColors.light);
                };
                const applyTheme = (theme) => {
                    const next = themes.includes(theme) ? theme : 'light';
                    document.documentElement.dataset.theme = next;
                    document.documentElement.style.colorScheme = next === 'dark' ? 'dark' : 'light';
                    storage.set('crm-theme', next);
                    setMetaColor(next);
                    return next;
                };
                const applySidebar = (collapsed) => {
                    document.documentElement.dataset.sidebar = collapsed ? 'collapsed' : 'expanded';
                    storage.set('crm-sidebar-collapsed', collapsed ? 'true' : 'false');
                };

                applyTheme(storage.get('crm-theme', 'light'));
                applySidebar(storage.get('crm-sidebar-collapsed', 'false') === 'true');

                window.crmShell = () => ({
                    sidebarOpen: false,
                    settingsOpen: false,
                    sidebarCollapsed: document.documentElement.dataset.sidebar === 'collapsed',
                    theme: document.documentElement.dataset.theme || 'light',
                    setTheme(value) {
                        this.theme = applyTheme(value);
                    },
                    toggleSidebar() {
                        this.sidebarCollapsed = ! this.sidebarCollapsed;
                        applySidebar(this.sidebarCollapsed);
                    },
                });
            })();
        </script>
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="crm-body font-sans">
        <a href="#main-content" class="crm-skip-link">Skip to content</a>
        <div x-data="crmShell()" class="crm-shell min-h-screen">
            <aside class="crm-sidebar fixed inset-y-3 left-3 z-40 hidden flex-col lg:flex" aria-label="Workspace sidebar">
                <div class="crm-sidebar-head">
                    <a wire:navigate href="{{ route('dashboard') }}" class="crm-brand" aria-label="Freelance CRM dashboard">
                        @if ($workspaceLogo)
                            <img src="{{ asset('storage/'.$workspaceLogo) }}" alt="" class="crm-brand-mark object-contain p-1">
                        @else
                            <span class="crm-brand-mark">F</span>
                        @endif
                        <span class="crm-brand-copy">
                            <span class="crm-brand-title">Freelance CRM</span>
                            <span class="crm-brand-subtitle">Client work cockpit</span>
                        </span>
                    </a>
                    <button type="button" class="crm-icon-button" @click="toggleSidebar" :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'" :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                        <x-icon name="sidebar" class="h-5 w-5" />
                    </button>
                </div>

                <nav aria-label="Main navigation" class="crm-nav">
                    @foreach ($navItems as $item)
                        @php($active = request()->routeIs($item['pattern']))
                        <a wire:navigate href="{{ route($item['route']) }}" @class([
                            'crm-nav-item',
                            'is-active' => $active,
                        ]) @if($active) aria-current="page" @endif :title="sidebarCollapsed ? @js($item['label']) : null">
                            <span class="crm-nav-icon"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                            <span class="crm-nav-label">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="crm-mobile-backdrop absolute inset-0" @click="sidebarOpen = false"></div>
                <aside class="crm-mobile-drawer relative flex h-full w-80 max-w-[88vw] flex-col">
                    <div class="crm-sidebar-head">
                        <a wire:navigate href="{{ route('dashboard') }}" class="crm-brand">
                            @if ($workspaceLogo)
                                <img src="{{ asset('storage/'.$workspaceLogo) }}" alt="" class="crm-brand-mark object-contain p-1">
                            @else
                                <span class="crm-brand-mark">F</span>
                            @endif
                            <span class="crm-brand-copy">
                                <span class="crm-brand-title">Freelance CRM</span>
                                <span class="crm-brand-subtitle">Client work cockpit</span>
                            </span>
                        </a>
                        <button type="button" class="crm-icon-button" @click="sidebarOpen = false" aria-label="Close navigation">
                            <x-icon name="x" class="h-5 w-5" />
                        </button>
                    </div>
                    <nav aria-label="Mobile navigation" class="crm-nav">
                        @foreach ($navItems as $item)
                            @php($active = request()->routeIs($item['pattern']))
                            <a wire:navigate href="{{ route($item['route']) }}" @class([
                                'crm-nav-item',
                                'is-active' => $active,
                            ]) @if($active) aria-current="page" @endif @click="sidebarOpen = false">
                                <span class="crm-nav-icon"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                                <span class="crm-nav-label">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>
            </div>

            <div class="crm-content">
                <header class="crm-topbar sticky top-0 z-30">
                    <div class="flex min-h-16 items-center justify-between gap-3 px-4 md:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button type="button" class="crm-icon-button lg:hidden" @click="sidebarOpen = true" aria-label="Open navigation">
                                <x-icon name="menu" class="h-5 w-5" />
                            </button>
                            <div>
                                <p class="crm-eyebrow">Workspace</p>
                                <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Freelance CRM' }}</h1>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="hidden items-center gap-2 md:flex">
                                <a wire:navigate href="{{ route('clients.create') }}" class="crm-button-secondary"><x-icon name="plus" class="h-4 w-4" />Client</a>
                                <a wire:navigate href="{{ route('deals.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Deal</a>
                            </div>

                            <div class="relative" @keydown.escape.window="settingsOpen = false">
                                <button type="button" class="crm-settings-trigger" @click="settingsOpen = ! settingsOpen" :aria-expanded="settingsOpen" aria-haspopup="menu" aria-label="Open account settings">
                                    @if ($ownerAvatar)
                                        <img src="{{ asset('storage/'.$ownerAvatar) }}" alt="" class="crm-avatar-img">
                                    @else
                                        <span class="crm-avatar-fallback">{{ str($ownerName)->substr(0, 1)->upper() }}</span>
                                    @endif
                                    <span class="hidden text-left md:grid">
                                        <span class="crm-user-name">{{ $ownerName }}</span>
                                        <span class="crm-user-email">{{ $ownerEmail }}</span>
                                    </span>
                                    <x-icon name="chevron-down" class="h-4 w-4" />
                                </button>

                                <div x-cloak x-show="settingsOpen" x-transition.origin.top.right @click.outside="settingsOpen = false" class="crm-settings-menu" role="menu">
                                    <div class="crm-settings-user">
                                        @if ($ownerAvatar)
                                            <img src="{{ asset('storage/'.$ownerAvatar) }}" alt="{{ $ownerName }} avatar" class="crm-avatar-img is-lg">
                                        @else
                                            <span class="crm-avatar-fallback is-lg">{{ str($ownerName)->substr(0, 1)->upper() }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="crm-user-name">{{ $ownerName }}</p>
                                            <p class="crm-user-email">{{ $ownerEmail }}</p>
                                        </div>
                                    </div>

                                    <div class="crm-theme-panel" role="radiogroup" aria-label="Theme">
                                        <button type="button" class="crm-theme-choice" :class="{ 'is-active': theme === 'light' }" @click="setTheme('light')" role="radio" :aria-checked="theme === 'light'" aria-label="Light theme">
                                            <span class="crm-theme-swatch is-light"></span>
                                            <span class="crm-theme-label">Light</span>
                                        </button>
                                        <button type="button" class="crm-theme-choice" :class="{ 'is-active': theme === 'sepia' }" @click="setTheme('sepia')" role="radio" :aria-checked="theme === 'sepia'" aria-label="Sepia theme">
                                            <span class="crm-theme-swatch is-sepia"></span>
                                            <span class="crm-theme-label">Sepia</span>
                                        </button>
                                        <button type="button" class="crm-theme-choice" :class="{ 'is-active': theme === 'dark' }" @click="setTheme('dark')" role="radio" :aria-checked="theme === 'dark'" aria-label="Dark theme">
                                            <span class="crm-theme-swatch is-dark"></span>
                                            <span class="crm-theme-label">Dark</span>
                                        </button>
                                    </div>

                                    <div class="crm-settings-links">
                                        <a wire:navigate href="{{ route('settings.edit') }}" role="menuitem" @click="settingsOpen = false">
                                            <x-icon name="user" class="h-5 w-5" />
                                            Owner profile
                                        </a>
                                        <a wire:navigate href="{{ route('settings.edit') }}" role="menuitem" @click="settingsOpen = false">
                                            <x-icon name="cog" class="h-5 w-5" />
                                            Workspace settings
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button role="menuitem">
                                                <x-icon name="logout" class="h-5 w-5" />
                                                Lock
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                @if (session('status'))
                    <div class="mx-auto max-w-7xl px-4 pt-4 md:px-6 lg:px-8">
                        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700" role="status">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                <main id="main-content" class="mx-auto max-w-7xl px-4 py-6 pb-24 md:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>

            <nav aria-label="Bottom navigation" class="crm-bottom-nav fixed inset-x-0 bottom-0 z-30 lg:hidden">
                <div class="grid grid-cols-5">
                    @foreach (array_slice($navItems, 0, 5) as $item)
                        @php($active = request()->routeIs($item['pattern']))
                        <a wire:navigate href="{{ route($item['route']) }}" @class([
                            'crm-bottom-item',
                            'is-active' => $active,
                        ])>
                            <x-icon :name="$item['icon']" class="h-5 w-5" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>
        </div>
        @livewireScripts
    </body>
</html>
