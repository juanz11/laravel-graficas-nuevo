<!-- Topbar -->
<header class="sticky top-0 z-20 border-b border-white/5 bg-[#090714]/80 backdrop-blur-md">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3 min-w-0">
            <button onclick="toggleSidebarMenu()" class="lg:hidden p-2 -ml-2 text-gray-300 hover:text-white hover:bg-white/5 rounded-xl transition-all" aria-label="Abrir menú">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <button onclick="toggleSidebarCollapse()" class="hidden lg:flex p-2 -ml-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all" aria-label="Minimizar menú" title="Minimizar menú">
                <svg id="sidebar-collapse-icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
            <div class="min-w-0">
                <h1 class="text-sm font-bold text-white tracking-tight truncate">{{ $title ?? 'SNC Pharma' }}</h1>
                @if (!empty($subtitle))
                    <p class="text-[11px] text-gray-500 truncate">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center space-x-3 shrink-0">
            <div class="hidden sm:flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-full bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center">
                    <span class="text-xs font-bold text-indigo-300">{{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                </div>
                <span class="text-xs font-semibold text-gray-300">{{ auth()->user()->name ?? 'Usuario' }}</span>
            </div>
        </div>
    </div>
</header>
