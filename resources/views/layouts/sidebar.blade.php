@php
    $isDashboard = request()->routeIs('dashboard');
    $navLink = 'flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group';
    $navIcon = 'w-5 h-5 shrink-0';
    $navActive = 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/30';
    $navIdle = 'text-gray-400 hover:text-white hover:bg-white/5 border border-transparent';
@endphp

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm hidden lg:hidden" onclick="toggleSidebarMenu(true)"></div>

<!-- CRM Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full lg:translate-x-0 transition-all duration-300 bg-[#0b0916] border-r border-white/5 flex flex-col">
    <!-- Brand -->
    <div class="brand-row flex items-center space-x-3 px-5 h-16 border-b border-white/5 shrink-0">
        <div class="bg-white/10 p-1.5 rounded-xl border border-white/10 shrink-0">
            <img src="{{ asset('logo.png') }}" class="h-7 w-auto object-contain" alt="Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <span class="hidden text-white font-bold tracking-wider text-sm">SNC</span>
        </div>
        <div class="brand-text">
            <span class="text-white font-bold text-sm tracking-tight block">SNC Pharma</span>
            <span class="text-[10px] text-indigo-400 font-semibold block uppercase tracking-wider -mt-0.5">Panel Comercial</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-gray-600">Panel</div>

        <a href="{{ route('dashboard') }}" class="{{ $navLink }} {{ $isDashboard ? $navActive : $navIdle }}">
            <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-gray-600">Gestión</div>

        <a href="{{ route('manual-entry') }}" class="{{ $navLink }} {{ request()->routeIs('manual-entry*') ? $navActive : $navIdle }}">
            <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Entrada Manual</span>
        </a>

        <a href="{{ route('sales.list') }}" class="{{ $navLink }} {{ request()->routeIs('sales.list', 'sales.edit-json') ? $navActive : $navIdle }}">
            <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Gestionar Ventas</span>
        </a>

        <a href="{{ route('clients.index') }}" class="{{ $navLink }} {{ request()->routeIs('clients*') ? $navActive : $navIdle }}">
            <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Clientes</span>
        </a>

        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-gray-600">Análisis</div>

        <a href="{{ route('compare') }}" class="{{ $navLink }} {{ request()->routeIs('compare*') ? $navActive : $navIdle }}">
            <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span>Comparar</span>
        </a>

        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-gray-600">Importación</div>

        @if ($isDashboard)
            <button onclick="toggleModal('import-modal'); toggleSidebarMenu(true);" class="{{ $navLink }} {{ $navIdle }} w-full text-left">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Importar Excel</span>
            </button>
            <button onclick="toggleModal('import-simple-modal'); toggleSidebarMenu(true);" class="{{ $navLink }} {{ $navIdle }} w-full text-left">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Importar Simple</span>
            </button>
            <button onclick="toggleModal('import-discounts-modal'); toggleSidebarMenu(true);" class="{{ $navLink }} {{ $navIdle }} w-full text-left">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                </svg>
                <span>Importar TXT</span>
            </button>
        @else
            <a href="{{ route('dashboard', ['open' => 'import-modal']) }}" class="{{ $navLink }} {{ $navIdle }}">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Importar Excel</span>
            </a>
            <a href="{{ route('dashboard', ['open' => 'import-simple-modal']) }}" class="{{ $navLink }} {{ $navIdle }}">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Importar Simple</span>
            </a>
            <a href="{{ route('dashboard', ['open' => 'import-discounts-modal']) }}" class="{{ $navLink }} {{ $navIdle }}">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                </svg>
                <span>Importar TXT</span>
            </a>
        @endif

        @if (isset($selectedMonthVal) && $selectedMonthVal)
            <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-gray-600">Mes Actual</div>

            <a href="{{ route('manual-entry.edit', ['date' => $selectedMonthVal]) }}" class="{{ $navLink }} {{ $navIdle }}">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Editar Mes</span>
            </a>

            <button type="button" onclick="updateMonthRate(); toggleSidebarMenu(true);" class="{{ $navLink }} {{ $navIdle }} w-full text-left">
                <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Actualizar Tasa</span>
            </button>

            <form id="update-rate-form" action="{{ route('sales.update-rate') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonthVal }}">
                <input type="hidden" name="exchange_rate" id="update-rate-input">
            </form>
        @endif
    </nav>

    <!-- Bottom actions -->
    <div class="sidebar-bottom px-3 py-4 border-t border-white/5 space-y-1 shrink-0">
        <button id="fullscreen-btn" onclick="toggleSidebarMenu(true); (typeof toggleFullscreen === 'function') ? toggleFullscreen() : (document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen());"
            class="{{ $navLink }} {{ $navIdle }} w-full text-left">
            <svg id="fullscreen-icon" class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            <span>Pantalla Completa</span>
        </button>

        <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
        <a href="#" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();"
            class="{{ $navLink }} text-red-300/80 hover:text-red-200 hover:bg-red-500/10 border border-transparent">
            <svg class="{{ $navIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>

<style>
    /* Collapsed sidebar (desktop only): icons only */
    @media (min-width: 1024px) {
        body.sidebar-collapsed #sidebar { width: 5rem; }
        body.sidebar-collapsed #sidebar .brand-row { justify-content: center; padding-left: 0; padding-right: 0; }
        body.sidebar-collapsed #sidebar .brand-text,
        body.sidebar-collapsed #sidebar nav > div,
        body.sidebar-collapsed #sidebar nav span,
        body.sidebar-collapsed #sidebar .sidebar-bottom span { display: none; }
        body.sidebar-collapsed #sidebar nav,
        body.sidebar-collapsed #sidebar .sidebar-bottom { padding-left: 0.5rem; padding-right: 0.5rem; }
        body.sidebar-collapsed #sidebar a,
        body.sidebar-collapsed #sidebar button { justify-content: center; padding-left: 0.5rem; padding-right: 0.5rem; }
        body.sidebar-collapsed #main-wrapper { padding-left: 5rem; }
        body.sidebar-collapsed #sidebar-collapse-icon { transform: rotate(180deg); }
    }
    #main-wrapper { transition: padding-left 0.3s; }
    #sidebar-collapse-icon { transition: transform 0.3s; }
</style>

<script>
    // Restore collapsed state across pages
    if (localStorage.getItem('snc-sidebar-collapsed') === '1') {
        document.body.classList.add('sidebar-collapsed');
    }

    // Off-canvas sidebar (mobile)
    function toggleSidebarMenu(forceClose) {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        if (!sidebar) return;
        var shouldClose = forceClose === true || !sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full', shouldClose);
        if (overlay) overlay.classList.toggle('hidden', shouldClose);
    }

    // Collapse/expand sidebar to icon-only mode (desktop)
    function toggleSidebarCollapse() {
        var collapsed = document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('snc-sidebar-collapsed', collapsed ? '1' : '0');
    }

    // Tooltips on icons when sidebar is collapsed
    document.querySelectorAll('#sidebar a, #sidebar button').forEach(function (el) {
        var label = el.querySelector('span');
        if (label && !el.title) el.title = label.textContent.trim();
    });
</script>
