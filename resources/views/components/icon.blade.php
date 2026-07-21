@props(['name'])
<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    @switch($name)
        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0L21.75 12M4.5 10.5v9.75h5.25V15h4.5v5.25h5.25V10.5" />
            @break
        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0M18 9.75a2.25 2.25 0 1 0 0-4.5M20.25 18a5.25 5.25 0 0 0-3-4.75" />
            @break
        @case('pipeline')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h5.5v5.5h-5.5zM14.75 5.25h5.5v5.5h-5.5zM3.75 15.25h5.5v3.5h-5.5zM14.75 15.25h5.5v3.5h-5.5zM9.25 8h5.5M6.5 10.75v4.5M17.5 10.75v4.5" />
            @break
        @case('briefcase')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 6V4.75A1.75 1.75 0 0 1 11.5 3h1A1.75 1.75 0 0 1 14.25 4.75V6M3.75 8.25A2.25 2.25 0 0 1 6 6h12a2.25 2.25 0 0 1 2.25 2.25v8.5A2.25 2.25 0 0 1 18 19H6a2.25 2.25 0 0 1-2.25-2.25v-8.5ZM3.75 11.25h16.5" />
            @break
        @case('check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15.75 9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            @break
        @case('document')
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6L18 8.25v12H7.5A1.5 1.5 0 0 1 6 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5ZM13.5 3.75v4.5H18M8.75 12h6.5M8.75 15h6.5M8.75 18h4" />
            @break
        @case('chart')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5V4.5M4.5 19.5h15M8.25 16.5v-5.25M12 16.5V7.5M15.75 16.5v-8.25" />
            @break
        @case('cog')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.6 3.75h4.8l.6 2.1a7 7 0 0 1 1.5.88l2.13-.52 2.4 4.16-1.54 1.57c.06.37.06.75 0 1.12l1.54 1.57-2.4 4.16-2.13-.52a7 7 0 0 1-1.5.88l-.6 2.1H9.6l-.6-2.1a7 7 0 0 1-1.5-.88l-2.13.52-2.4-4.16 1.54-1.57a6.7 6.7 0 0 1 0-1.12L2.97 10.37l2.4-4.16 2.13.52a7 7 0 0 1 1.5-.88l.6-2.1ZM12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" />
            @break
        @case('plus')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
            @break
        @case('menu')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.75h16M4 12h16M4 17.25h16" />
            @break
        @case('sidebar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.25A1.75 1.75 0 0 1 6.25 3.5h11.5a1.75 1.75 0 0 1 1.75 1.75v13.5a1.75 1.75 0 0 1-1.75 1.75H6.25a1.75 1.75 0 0 1-1.75-1.75V5.25ZM9 3.75v16.5M13.25 8.5 16.75 12l-3.5 3.5" />
            @break
        @case('logout')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 4.5H6.5A1.75 1.75 0 0 0 4.75 6.25v11.5A1.75 1.75 0 0 0 6.5 19.5h3.25M15 8.25 18.75 12 15 15.75M18.25 12H9.75" />
            @break
        @case('user')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
            @break
        @case('chevron-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="m6.75 9.75 5.25 5.25 5.25-5.25" />
            @break
        @case('x')
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
            @break
        @case('search')
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M18 10.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
            @break
        @case('calendar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3v3M16.5 3v3M4.5 8.25h15M6 5.25h12A1.5 1.5 0 0 1 19.5 6.75v11.5A1.75 1.75 0 0 1 17.75 20H6.25A1.75 1.75 0 0 1 4.5 18.25V6.75A1.5 1.5 0 0 1 6 5.25Z" />
            @break
        @case('wallet')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5A1.75 1.75 0 0 1 6.25 5.75h9A1.75 1.75 0 0 1 17 7.5v1.25h1.25A1.75 1.75 0 0 1 20 10.5v6.25a1.75 1.75 0 0 1-1.75 1.75H6.25A1.75 1.75 0 0 1 4.5 16.75V7.5ZM14.75 13.5a1 1 0 1 0 2 0 1 1 0 0 0-2 0Z" />
            @break
        @case('board')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.75h4.25v14.5H4.5zM9.875 4.75h4.25v9.5h-4.25zM15.25 4.75h4.25v12h-4.25z" />
            @break
        @case('book')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5c-1.4-1.1-3.35-1.5-5.1-1.5-.75 0-1.4.6-1.4 1.4v10.7c0 .8.65 1.4 1.4 1.4 1.75 0 3.7.4 5.1 1.5 1.4-1.1 3.35-1.5 5.1-1.5.75 0 1.4-.6 1.4-1.4V6.4c0-.8-.65-1.4-1.4-1.4-1.75 0-3.7.4-5.1 1.5ZM12 6.5V18.5" />
            @break
        @case('clock')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.75M20.25 12a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0Z" />
            @break
        @case('pencil')
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.5l3 3L8 19l-4 1 1-4L16.5 4.5Z" />
            @break
        @case('trash')
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 7V5.5A1.5 1.5 0 0 1 11.5 4h1A1.5 1.5 0 0 1 14 5.5V7M6.5 7l.7 11a1.5 1.5 0 0 0 1.5 1.4h6.6a1.5 1.5 0 0 0 1.5-1.4l.7-11" />
            @break
        @case('pin')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4h6l-1 5 3 3v2H7v-2l3-3-1-5ZM12 14v6" />
            @break
        @default
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15v10.5h-15zM7.5 9.75h9M7.5 12.75h6" />
    @endswitch
</svg>
