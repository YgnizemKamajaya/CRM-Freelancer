@php
    $navGroups = [
        [
            'label' => null,
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard*', 'icon' => 'home'],
            ],
        ],
        [
            'label' => 'CRM',
            'items' => [
                ['label' => 'Clients', 'route' => 'clients.index', 'pattern' => 'clients.*', 'icon' => 'users'],
                ['label' => 'Deals', 'route' => 'deals.index', 'pattern' => 'deals.*', 'icon' => 'pipeline'],
                ['label' => 'Projects', 'route' => 'projects.index', 'pattern' => 'projects.*', 'icon' => 'briefcase'],
                ['label' => 'Tasks', 'route' => 'tasks.index', 'pattern' => 'tasks.*', 'icon' => 'check'],
                ['label' => 'Invoices', 'route' => 'invoices.index', 'pattern' => 'invoices.*', 'icon' => 'document'],
            ],
        ],
        [
            'label' => 'Personal',
            'items' => [
                ['label' => 'Hari ini', 'route' => 'today.index', 'pattern' => 'today.*', 'icon' => 'check'],
                ['label' => 'Focus', 'route' => 'focus.index', 'pattern' => 'focus.*', 'icon' => 'clock'],
                ['label' => 'Finance', 'route' => 'finance.index', 'pattern' => ['finance.*', 'wallets.*', 'transactions.*', 'budgets.*'], 'icon' => 'wallet'],
                ['label' => 'Goals', 'route' => 'goals.index', 'pattern' => 'goals.*', 'icon' => 'chart'],
                ['label' => 'Workspace', 'route' => 'workspace.index', 'pattern' => 'workspace.*', 'icon' => 'board'],
                ['label' => 'Journal', 'route' => 'journal.index', 'pattern' => 'journal.*', 'icon' => 'book'],
                ['label' => 'Vault', 'route' => 'vault.index', 'pattern' => 'vault.*', 'icon' => 'document'],
            ],
        ],
        [
            'label' => null,
            'items' => [
                ['label' => 'Reports', 'route' => 'reports.index', 'pattern' => 'reports.*', 'icon' => 'chart'],
                ['label' => 'Settings', 'route' => 'settings.edit', 'pattern' => 'settings.*', 'icon' => 'cog'],
            ],
        ],
    ];

    // Bottom nav (mobile) stays as the original curated 5 so existing muscle memory doesn't shift;
    // Finance/Workspace/Journal join it once they have real content (Fase 4).
    $bottomNavItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard*', 'icon' => 'home'],
        ['label' => 'Clients', 'route' => 'clients.index', 'pattern' => 'clients.*', 'icon' => 'users'],
        ['label' => 'Deals', 'route' => 'deals.index', 'pattern' => 'deals.*', 'icon' => 'pipeline'],
        ['label' => 'Projects', 'route' => 'projects.index', 'pattern' => 'projects.*', 'icon' => 'briefcase'],
        ['label' => 'Tasks', 'route' => 'tasks.index', 'pattern' => 'tasks.*', 'icon' => 'check'],
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
                    } catch { }
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
                themeMenuOpen: false,
                commandOpen: false,
                sidebarCollapsed: document.documentElement.dataset.sidebar === 'collapsed',
                theme: document.documentElement.dataset.theme || 'light',
                setTheme(value) {
                    this.theme = applyTheme(value);
                },
                toggleSidebar() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    applySidebar(this.sidebarCollapsed);
                },
                handleGlobalShortcut(event) {
                    const key = event.key ? event.key.toLowerCase() : '';
                    if ((event.metaKey || event.ctrlKey) && key === 'k') {
                        event.preventDefault();
                        this.commandOpen = true;
                    }
                },
            });
        })();
    </script>
    @stack('head')
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="crm-body font-sans">
    <a href="#main-content" class="crm-skip-link">Skip to content</a>
    <div x-data="crmShell()" class="crm-shell min-h-screen" @keydown.window="handleGlobalShortcut($event)">
        <aside class="crm-sidebar fixed inset-y-3 left-3 z-40 hidden flex-col lg:flex" aria-label="Workspace sidebar">
            <div class="crm-sidebar-head">
                <a wire:navigate href="{{ route('dashboard') }}" class="crm-brand" aria-label="Freelance CRM dashboard">
                    @if ($workspaceLogo)
                        <img src="{{ asset('storage/' . $workspaceLogo) }}" alt=""
                            class="crm-brand-mark object-contain p-1">
                    @else
                        <span class="crm-brand-mark">F</span>
                    @endif
                    <span class="crm-brand-copy">
                        <span class="crm-brand-title">Freelance CRM</span>
                        <span class="crm-brand-subtitle">Client work cockpit</span>
                    </span>
                </a>
                <button type="button" class="crm-icon-button" @click="toggleSidebar"
                    :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <x-icon name="sidebar" class="h-5 w-5" />
                </button>
            </div>

            <nav aria-label="Main navigation" class="crm-nav">
                @foreach ($navGroups as $group)
                @if ($group['label'])
                <p class="crm-nav-group-label">{{ $group['label'] }}</p>
                @endif
                @foreach ($group['items'] as $item)
                @php($active = request()->routeIs(...(array) $item['pattern']))
                <a wire:navigate href="{{ route($item['route']) }}" @class([
                    'crm-nav-item',
                    'is-active' => $active,
                ]) @if($active) aria-current="page" @endif :title="sidebarCollapsed ? @js($item['label']) : null">
                    <span class="crm-nav-icon"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                    <span class="crm-nav-label">{{ $item['label'] }}</span>
                    @if (!empty($item['soon']))
                    <span class="crm-nav-soon">Segera</span>
                    @endif
                </a>
                @endforeach
                @endforeach
            </nav>

            <!-- Profile Footer Section -->
            <div class="crm-sidebar-foot">
                <div class="relative" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
                    <button type="button" class="crm-user-card w-full text-left" @click="menuOpen = ! menuOpen"
                        :aria-expanded="menuOpen"
                        style="border: 0; cursor: pointer; background: transparent; padding: 0.65rem 0.5rem;">
                        @if ($ownerAvatar)
                            <img src="{{ asset('storage/' . $ownerAvatar) }}" alt="" class="crm-avatar-img">
                        @else
                            <span class="crm-avatar-fallback">{{ str($ownerName)->substr(0, 1)->upper() }}</span>
                        @endif

                        <span class="crm-user-copy min-w-0 flex-1 grid ml-2">
                            <span class="crm-user-name truncate">{{ $ownerName }}</span>
                            <span class="crm-user-email truncate" style="font-size: 0.7rem;">Workspace Owner</span>
                        </span>

                        <span class="crm-user-copy ml-auto">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                        </span>
                    </button>

                    <!-- Dropdown menu popup above -->
                    <div x-cloak x-show="menuOpen" x-transition.origin.bottom.left @click.outside="menuOpen = false"
                        class="crm-settings-menu" style="bottom: calc(100% + 0.6rem); top: auto; left: 0; right: auto;"
                        role="menu">
                        <div class="crm-settings-user">
                            @if ($ownerAvatar)
                                <img src="{{ asset('storage/' . $ownerAvatar) }}" alt="{{ $ownerName }} avatar"
                                    class="crm-avatar-img is-lg">
                            @else
                                <span class="crm-avatar-fallback is-lg">{{ str($ownerName)->substr(0, 1)->upper() }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="crm-user-name">{{ $ownerName }}</p>
                                <p class="crm-user-email">{{ $ownerEmail }}</p>
                            </div>
                        </div>

                        <div class="crm-settings-links">
                            <a wire:navigate href="{{ route('settings.edit') }}" role="menuitem"
                                @click="menuOpen = false">
                                <x-icon name="user" class="h-5 w-5" />
                                Owner profile
                            </a>
                            <a wire:navigate href="{{ route('settings.edit') }}" role="menuitem"
                                @click="menuOpen = false">
                                <x-icon name="cog" class="h-5 w-5" />
                                Workspace settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button role="menuitem"
                                    style="border: 0; cursor: pointer; background: transparent; width: 100%; text-align: left;">
                                    <x-icon name="logout" class="h-5 w-5" />
                                    Lock Workspace
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
            <div class="crm-mobile-backdrop absolute inset-0" @click="sidebarOpen = false"></div>
            <aside class="crm-mobile-drawer relative flex h-full w-80 max-w-[88vw] flex-col">
                <div class="crm-sidebar-head">
                    <a wire:navigate href="{{ route('dashboard') }}" class="crm-brand">
                        @if ($workspaceLogo)
                            <img src="{{ asset('storage/' . $workspaceLogo) }}" alt=""
                                class="crm-brand-mark object-contain p-1">
                        @else
                            <span class="crm-brand-mark">F</span>
                        @endif
                        <span class="crm-brand-copy">
                            <span class="crm-brand-title">Freelance CRM</span>
                            <span class="crm-brand-subtitle">Client work cockpit</span>
                        </span>
                    </a>
                    <button type="button" class="crm-icon-button" @click="sidebarOpen = false"
                        aria-label="Close navigation">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <nav aria-label="Mobile navigation" class="crm-nav">
                    @foreach ($navGroups as $group)
                    @if ($group['label'])
                    <p class="crm-nav-group-label">{{ $group['label'] }}</p>
                    @endif
                    @foreach ($group['items'] as $item)
                    @php($active = request()->routeIs(...(array) $item['pattern']))
                    <a wire:navigate href="{{ route($item['route']) }}" @class([
                        'crm-nav-item',
                        'is-active' => $active,
                    ]) @if($active) aria-current="page" @endif @click="sidebarOpen = false">
                        <span class="crm-nav-icon"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                        <span class="crm-nav-label">{{ $item['label'] }}</span>
                        @if (!empty($item['soon']))
                        <span class="crm-nav-soon">Segera</span>
                        @endif
                    </a>
                    @endforeach
                    @endforeach
                </nav>

                <!-- Mobile Profile Footer Section -->
                <div class="crm-sidebar-foot mt-auto">
                    <div class="relative" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
                        <button type="button" class="crm-user-card w-full text-left" @click="menuOpen = ! menuOpen"
                            :aria-expanded="menuOpen"
                            style="border: 0; cursor: pointer; background: transparent; padding: 0.65rem 0.5rem;">
                            @if ($ownerAvatar)
                                <img src="{{ asset('storage/' . $ownerAvatar) }}" alt="" class="crm-avatar-img">
                            @else
                                <span class="crm-avatar-fallback">{{ str($ownerName)->substr(0, 1)->upper() }}</span>
                            @endif

                            <span class="crm-user-copy min-w-0 flex-1 grid ml-2">
                                <span class="crm-user-name truncate">{{ $ownerName }}</span>
                                <span class="crm-user-email truncate" style="font-size: 0.7rem;">Workspace Owner</span>
                            </span>

                            <span class="crm-user-copy ml-auto">
                                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="19" cy="12" r="1"></circle>
                                    <circle cx="5" cy="12" r="1"></circle>
                                </svg>
                            </span>
                        </button>

                        <!-- Dropdown menu popup above -->
                        <div x-cloak x-show="menuOpen" x-transition.origin.bottom.left @click.outside="menuOpen = false"
                            class="crm-settings-menu"
                            style="bottom: calc(100% + 0.6rem); top: auto; left: 0; right: auto;" role="menu">
                            <div class="crm-settings-user">
                                @if ($ownerAvatar)
                                    <img src="{{ asset('storage/' . $ownerAvatar) }}" alt="{{ $ownerName }} avatar"
                                        class="crm-avatar-img is-lg">
                                @else
                                    <span
                                        class="crm-avatar-fallback is-lg">{{ str($ownerName)->substr(0, 1)->upper() }}</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="crm-user-name">{{ $ownerName }}</p>
                                    <p class="crm-user-email">{{ $ownerEmail }}</p>
                                </div>
                            </div>

                            <div class="crm-settings-links">
                                <a wire:navigate href="{{ route('settings.edit') }}" role="menuitem"
                                    @click="menuOpen = false">
                                    <x-icon name="user" class="h-5 w-5" />
                                    Owner profile
                                </a>
                                <a wire:navigate href="{{ route('settings.edit') }}" role="menuitem"
                                    @click="menuOpen = false">
                                    <x-icon name="cog" class="h-5 w-5" />
                                    Workspace settings
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button role="menuitem"
                                        style="border: 0; cursor: pointer; background: transparent; width: 100%; text-align: left;">
                                        <x-icon name="logout" class="h-5 w-5" />
                                        Lock Workspace
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <div class="crm-content">
            <header class="crm-topbar sticky top-0 z-30">
                <div class="flex min-h-16 items-center justify-between gap-3 px-4 md:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" class="crm-icon-button lg:hidden" @click="sidebarOpen = true"
                            aria-label="Open navigation">
                            <x-icon name="menu" class="h-5 w-5" />
                        </button>
                        <div>
                            <p class="crm-eyebrow">Workspace</p>
                            <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Freelance CRM' }}</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="crm-icon-button" @click="commandOpen = true"
                            aria-label="Buka pencarian cepat" title="Quick search / add (Ctrl+K)">
                            <x-icon name="search" class="h-5 w-5" />
                        </button>

                        <div class="hidden items-center gap-2 md:flex">
                            <a wire:navigate href="{{ route('clients.create') }}" class="crm-button-secondary"><x-icon
                                    name="plus" class="h-4 w-4" />Client</a>
                            <a wire:navigate href="{{ route('deals.create') }}" class="crm-button-primary"><x-icon
                                    name="plus" class="h-4 w-4" />Deal</a>
                        </div>

                        <!-- Lock Workspace Shutdown Button -->
                        <form method="POST" action="{{ route('logout') }}" class="flex">
                            @csrf
                            <button type="submit" class="crm-icon-button" aria-label="Lock workspace"
                                title="Lock workspace">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                    <line x1="12" y1="2" x2="12" y2="12"></line>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div class="mx-auto max-w-7xl px-4 pt-4 md:px-6 lg:px-8">
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                        role="status">
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
                @foreach ($bottomNavItems as $item)
                @php($active = request()->routeIs(...(array) $item['pattern']))
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

        <!-- Quick Capture / Command Palette -->
        <div x-cloak x-show="commandOpen"
            class="fixed inset-0 z-[70] flex justify-center px-4 pt-24 max-lg:pt-16"
            @keydown.escape.window="commandOpen = false">
            <div class="crm-mobile-backdrop absolute inset-0" @click="commandOpen = false"></div>
            <div x-show="commandOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                @click.outside="commandOpen = false" class="relative h-fit w-full max-w-xl">
                <livewire:quick-capture />
            </div>
        </div>

        <!-- Floating Theme Toggle Selector -->
        <div class="fixed bottom-6 right-6 max-lg:bottom-20 max-lg:right-4" style="z-index: 40;">
            <!-- Collapsible Menu -->
            <div x-cloak x-show="themeMenuOpen" x-transition.origin.bottom.right @click.outside="themeMenuOpen = false"
                class="absolute bottom-full right-0 mb-3 flex flex-col gap-1 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-2 shadow-2xl min-w-[150px]"
                style="box-shadow: var(--crm-shadow-panel);">

                <!-- Light -->
                <button type="button"
                    class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-xs font-semibold transition-colors text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    style="border: 0; cursor: pointer; background: transparent; display: flex; align-items: center;"
                    :style="theme === 'light' ? 'background: var(--crm-hover); color: var(--crm-heading);' : ''"
                    @click="setTheme('light'); themeMenuOpen = false">
                    <svg class="text-amber-500" style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    <span class="ml-2">Light</span>
                </button>

                <!-- Sunset (Sepia) -->
                <button type="button"
                    class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-xs font-semibold transition-colors text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    style="border: 0; cursor: pointer; background: transparent; display: flex; align-items: center;"
                    :style="theme === 'sepia' ? 'background: var(--crm-hover); color: var(--crm-heading);' : ''"
                    @click="setTheme('sepia'); themeMenuOpen = false">
                    <svg class="text-orange-500" style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M12 10a4 4 0 0 0-4 4h8a4 4 0 0 0-4-4z"></path>
                        <path d="M2 20h20M12 2v3M4.93 6.93l2.12 2.12M19.07 6.93l-2.12 2.12"></path>
                    </svg>
                    <span class="ml-2">Sunset</span>
                </button>

                <!-- Dark -->
                <button type="button"
                    class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-xs font-semibold transition-colors text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    style="border: 0; cursor: pointer; background: transparent; display: flex; align-items: center;"
                    :style="theme === 'dark' ? 'background: var(--crm-hover); color: var(--crm-heading);' : ''"
                    @click="setTheme('dark'); themeMenuOpen = false">
                    <svg class="text-indigo-400" style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    <span class="ml-2">Dark</span>
                </button>
            </div>

            <!-- Floating Trigger Button -->
            <button type="button" @click="themeMenuOpen = !themeMenuOpen"
                class="flex items-center justify-center rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 shadow-2xl hover:scale-110 active:scale-95 transition-all duration-200 focus:outline-none"
                style="cursor: pointer; box-shadow: var(--crm-shadow-panel); width: 2.75rem; height: 2.75rem;"
                aria-label="Toggle Theme">
                <!-- Sun Icon -->
                <svg x-show="theme === 'light'" class="text-amber-500" style="width: 1.35rem; height: 1.35rem;"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
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
                <svg x-show="theme === 'sepia'" class="text-orange-500" style="width: 1.35rem; height: 1.35rem;"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M12 10a4 4 0 0 0-4 4h8a4 4 0 0 0-4-4z"></path>
                    <path d="M2 20h20M12 2v3M4.93 6.93l2.12 2.12M19.07 6.93l-2.12 2.12"></path>
                </svg>
                <!-- Dark Icon -->
                <svg x-show="theme === 'dark'" class="text-indigo-400" style="width: 1.35rem; height: 1.35rem;"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
        </div>
    </div>
    @stack('scripts')
    @livewireScripts
</body>

</html>