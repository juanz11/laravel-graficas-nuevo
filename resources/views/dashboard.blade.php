<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SNC Pharma - Estadísticas de Venta</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #090714;
            color: #f3f4f6;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-indigo {
            box-shadow: 0 0 20px 0 rgba(99, 102, 241, 0.15);
        }
        .client-header:hover {
            background: rgba(255, 255, 255, 0.04);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Background glow -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-indigo-500/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] rounded-full bg-purple-500/5 blur-[120px] pointer-events-none"></div>

    <!-- Floating Exit Fullscreen Button (hidden by default) -->
    <button id="exit-fullscreen-btn" onclick="toggleFullscreen()" 
        class="fixed top-4 right-4 z-[100] hidden px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-xl shadow-lg transition-all flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span>Salir de Pantalla Completa</span>
    </button>

    <!-- Navigation Header -->
    <nav class="glass-card sticky top-0 z-50 border-b border-white/5 bg-[#090714]/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="bg-white/10 p-2 rounded-xl border border-white/10 shrink-0">
                        <img src="{{ asset('logo.png') }}" class="h-8 w-auto object-contain" alt="Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <span class="hidden text-white font-bold tracking-wider">SNC</span>
                    </div>
                    <div>
                        <span class="text-white font-bold text-base tracking-tight block">SNC Pharma</span>
                        <span class="text-[10px] text-purple-400 font-semibold block uppercase tracking-wider -mt-1">Estadísticas de Venta</span>
                    </div>
                </div>

                <!-- User Info & Action -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <button onclick="toggleFullscreen()" id="fullscreen-btn"
                        class="text-xs font-semibold px-3 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl transition-all flex items-center space-x-1.5">
                        <svg id="fullscreen-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <span class="hidden sm:inline">Pantalla Completa</span>
                    </button>

                    <button onclick="toggleModal('import-modal')" 
                        class="text-xs font-semibold px-3 py-2 sm:px-4 bg-purple-600 hover:bg-purple-500 text-white rounded-xl transition-all flex items-center space-x-1.5 shadow-lg shadow-purple-600/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Importar Excel</span>
                    </button>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                        class="text-xs font-semibold px-3 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-200 hover:text-white rounded-xl transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:inline">Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        
        <!-- Messages Section -->
        @if (session('success'))
            <div class="mb-8 p-4 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-200 text-sm flex items-start space-x-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-green-450" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-8 p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-200 text-sm flex items-start space-x-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-red-450" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <strong class="block font-semibold">Ocurrió un error al importar:</strong>
                    <ul class="list-disc list-inside mt-1 text-xs text-red-300 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- If No Data is Available -->
        @if (!$hasData)
            <div class="glass-card rounded-3xl p-8 sm:p-16 text-center max-w-2xl mx-auto my-12 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-48 h-48 rounded-full bg-purple-600/10 blur-[60px]"></div>
                <div class="absolute -bottom-24 -right-24 w-48 h-48 rounded-full bg-indigo-600/10 blur-[60px]"></div>
                
                <svg class="w-20 h-20 text-purple-400/80 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">No hay datos de ventas importados</h2>
                <p class="text-gray-400 text-sm mt-3 max-w-md mx-auto leading-relaxed">
                    Sube el reporte de ventas por cliente de SNC Pharma (en formato Excel .xlsx/.xls, CSV o archivo de texto .txt) para analizar la facturación, los clientes y las líneas comerciales.
                </p>
                
                <button onclick="toggleModal('import-modal')" 
                    class="mt-8 px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-sm font-semibold rounded-2xl transition-all shadow-lg shadow-purple-600/20 inline-flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Importar Primer Reporte</span>
                </button>
            </div>
        @else
            <!-- Header Filter Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Estadísticas de Venta</h1>
                    <p class="text-gray-400 text-sm mt-1">Análisis del mes: <span class="text-purple-400 font-semibold">{{ $selectedMonthLabel }}</span></p>
                </div>
                
                <!-- Filters Form -->
                <form id="filter-form" action="{{ route('dashboard') }}" method="GET" class="w-full sm:w-auto">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Units/Sales Toggle -->
                        <div class="relative">
                            <label for="view-type" class="sr-only">Tipo de Vista</label>
                            <select id="view-type" name="view_type" onchange="document.getElementById('filter-form').submit();"
                                class="w-full sm:w-40 bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                <option value="units" {{ !request('view_type') || request('view_type') === 'units' ? 'selected' : '' }} class="bg-[#090714] text-white">Unidades</option>
                                <option value="sales" {{ request('view_type') === 'sales' ? 'selected' : '' }} class="bg-[#090714] text-white">Ventas ($)</option>
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div class="relative">
                            <label for="month-select" class="sr-only">Seleccionar Mes</label>
                            <select id="month-select" name="month" onchange="document.getElementById('filter-form').submit();"
                                class="w-full sm:w-48 bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                <option value="">Todos los meses</option>
                                @foreach ($months as $m)
                                    <option value="{{ $m['val'] }}" {{ $m['val'] === $selectedMonthVal ? 'selected' : '' }} class="bg-[#090714] text-white">
                                        {{ $m['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Class Filter -->
                        <div class="relative">
                            <label for="class-select" class="sr-only">Seleccionar Clase</label>
                            <select id="class-select" name="class" onchange="document.getElementById('filter-form').submit();"
                                class="w-full sm:w-48 bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                <option value="">Todas las clases</option>
                                @foreach ($classesList as $class)
                                    <option value="{{ $class }}" {{ $class === $selectedClass ? 'selected' : '' }} class="bg-[#090714] text-white">
                                        {{ $class }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Client Filter -->
                        <div class="relative">
                            <label for="client-select" class="sr-only">Seleccionar Cliente</label>
                            <select id="client-select" name="client" onchange="document.getElementById('filter-form').submit();"
                                class="w-full sm:w-56 bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                <option value="">Todos los clientes</option>
                                @foreach ($clientsList as $client)
                                    <option value="{{ $client['code'] }}" {{ $client['code'] === $selectedClient ? 'selected' : '' }} class="bg-[#090714] text-white">
                                        {{ $client['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Product Filter -->
                        <div class="relative">
                            <label for="product-input" class="sr-only">Buscar Producto</label>
                            <input type="text" id="product-input" name="product" value="{{ $selectedProduct ?? '' }}" placeholder="Buscar producto..."
                                class="w-full sm:w-56 bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        </div>
                        
                        <!-- Apply Filter Button -->
                        <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-xl transition-all">
                            Filtrar
                        </button>
                        
                        <!-- Clear Filters Button -->
                        @if ($selectedClient || $selectedClass || $selectedProduct)
                        <a href="{{ route('dashboard', ['month' => $selectedMonthVal]) }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white text-sm font-semibold rounded-xl transition-all text-center">
                            Limpiar
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- KPI Cards Grid - Hidden --}}
            {{-- <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <!-- Ventas Totales -->
                <div class="glass-card rounded-2xl p-6 glow-indigo relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-purple-500/5 blur-2xl pointer-events-none"></div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Total Ventas ($)</span>
                        <span class="p-1 bg-purple-500/10 text-purple-300 rounded text-[10px] font-semibold">Facturación</span>
                    </div>
                    <h3 class="text-2xl font-extrabold text-white tracking-tight">$ {{ number_format($kpis['total_sales'], 2, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-2">Monto neto facturado en el mes</p>
                </div>
                
                <!-- Costo Total -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Costo Acumulado ($)</span>
                        <span class="p-1 bg-yellow-500/10 text-yellow-300 rounded text-[10px] font-semibold">Costo</span>
                    </div>
                    <h3 class="text-2xl font-extrabold text-white tracking-tight">$ {{ number_format($kpis['total_cost'], 2, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-2">Costo estimado de medicamentos vendidos</p>
                </div>

                <!-- Utilidad Total -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Utilidad Operacional ($)</span>
                        <span class="p-1 bg-emerald-500/10 text-emerald-300 rounded text-[10px] font-semibold">Beneficio</span>
                    </div>
                    <h3 class="text-2xl font-extrabold text-white tracking-tight">$ {{ number_format($kpis['total_utility'], 2, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-2">Ganancia neta calculada en el mes</p>
                </div>

                <!-- Margen de Utilidad -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Margen Promedio</span>
                        <span class="p-1 bg-blue-500/10 text-blue-300 rounded text-[10px] font-semibold">Porcentaje</span>
                    </div>
                    <h3 class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($kpis['utility_margin'], 2, ',', '.') }}%</h3>
                    <p class="text-xs text-gray-500 mt-2">Margen comercial de utilidad del mes</p>
                </div>
            </div> --}}

            <!-- Charts Section -->
            <div id="charts-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Trend line chart -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-white">Evolución de Ventas Mensual</h3>
                        <span class="text-xs text-gray-400">Histórico de importaciones</span>
                    </div>
                    <div class="h-96 w-full relative">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>

                <!-- Category distribution doughnut chart -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-white">Ventas por Clase de Cliente</h3>
                        <span class="text-xs text-gray-400">Participación</span>
                    </div>
                    <div class="h-96 w-full relative flex items-center justify-center">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Additional Charts Row -->
            <div id="additional-charts-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Top Products Bar Chart -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-white">Top 15 Productos por Ventas</h3>
                        <span class="text-xs text-gray-400">Mejores vendidos del mes</span>
                    </div>
                    <div class="h-96 w-full relative">
                        <canvas id="productsBarChart"></canvas>
                    </div>
                </div>

                <!-- Top Clients Bar Chart -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-white">Top 15 Clientes por Ventas</h3>
                        <span class="text-xs text-gray-400">Mayores compradores del mes</span>
                    </div>
                    <div class="h-96 w-full relative">
                        <canvas id="clientsBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Search and Clients Section -->
            <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <h3 class="text-xl font-bold text-white">Desglose de Ventas por Cliente</h3>
                
                <!-- Simple client search input -->
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" id="search-input" onkeyup="filterClients()" 
                        class="w-full pl-9 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 text-sm"
                        placeholder="Buscar cliente...">
                </div>
            </div>

            <!-- Clients Accordion List -->
            <div class="space-y-4" id="clients-container">
                @foreach ($salesByClient as $client)
                    <div class="client-card glass-card rounded-2xl overflow-hidden transition-all duration-300" data-name="{{ strtolower($client['name']) }}">
                        <!-- Accordion Trigger Header -->
                        <div onclick="toggleClientDetails('{{ $client['code'] }}')"
                            class="client-header flex flex-col sm:flex-row sm:items-center justify-between p-5 cursor-pointer select-none transition-all duration-200 gap-3">
                            <div class="flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-300 text-sm font-bold shrink-0">
                                    {{ substr($client['name'], 0, 2) }}
                                </div>
                                <div>
                                    <h4 class="text-white font-bold text-base leading-snug">{{ $client['name'] }}</h4>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400 mt-0.5">
                                        <span>Cod: <strong class="text-gray-300">{{ $client['code'] }}</strong></span>
                                        <span class="w-1 h-1 rounded-full bg-white/10"></span>
                                        <span>Clase: <span class="px-2 py-0.5 bg-white/5 border border-white/10 text-purple-300 rounded-full font-semibold">{{ $client['class'] }}</span></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between sm:justify-end space-x-6">
                                <div class="text-left sm:text-right">
                                    <span class="text-xs text-gray-450 block uppercase tracking-wider font-semibold">{{ $viewType === 'units' ? 'Total Unidades' : 'Total Ventas' }}</span>
                                    @if ($viewType === 'units')
                                        <span class="text-base font-extrabold text-white block">{{ number_format($client['total_qty'], 0, ',', '.') }} unidades</span>
                                        <span class="text-[10px] text-gray-400 block">$ {{ number_format($client['total_sales'], 2, ',', '.') }} en ventas</span>
                                    @else
                                        <span class="text-base font-extrabold text-white block">$ {{ number_format($client['total_sales'], 2, ',', '.') }}</span>
                                        <span class="text-[10px] text-gray-400 block">{{ number_format($client['total_qty'], 0, ',', '.') }} unidades vendidas</span>
                                    @endif
                                </div>
                                <div id="icon-{{ $client['code'] }}" class="w-8 h-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 transition-transform duration-350 shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Content (Table of Products) -->
                        <div id="details-{{ $client['code'] }}" class="hidden border-t border-white/5 bg-black/10 overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white/[0.01] text-[10px] font-bold uppercase tracking-wider text-purple-400 border-b border-white/5">
                                        <th class="px-6 py-3.5">Código Producto</th>
                                        <th class="px-6 py-3.5">Descripción</th>
                                        <th class="px-6 py-3.5 text-right">Cant. Vendida</th>
                                        <th class="px-6 py-3.5 text-right">Ventas ($)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 text-xs text-gray-300">
                                    @foreach ($client['items'] as $item)
                                        <tr class="hover:bg-white/[0.02] transition-all">
                                            <td class="px-6 py-3 font-mono text-[11px] text-gray-400">{{ $item->product_code }}</td>
                                            <td class="px-6 py-3 text-white font-medium">{{ $item->product_description }}</td>
                                            <td class="px-6 py-3 text-right">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                            <td class="px-6 py-3 text-right font-semibold text-white">$ {{ number_format($item->total_sales, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <!-- Import Modal -->
    <div id="import-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Overlay background -->
            <div onclick="toggleModal('import-modal')" class="fixed inset-0 bg-[#070510]/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <!-- Modal Content Card -->
            <div class="relative inline-block align-bottom bg-[#0c0a18] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 sm:p-8">
                <div class="absolute -top-16 -right-16 w-32 h-32 rounded-full bg-purple-600/10 blur-xl pointer-events-none"></div>
                
                <div class="flex items-center justify-between pb-4 border-b border-white/5 mb-6">
                    <h3 class="text-lg font-bold text-white" id="modal-title">Importar Reporte de Operaciones</h3>
                    <button onclick="toggleModal('import-modal')" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('sales.import') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Archivo del Reporte (.xlsx, .xls, .csv, .txt)</label>
                        
                        <!-- File Upload Dropzone box -->
                        <div class="relative border-2 border-dashed border-white/10 hover:border-purple-500/50 rounded-2xl p-6 text-center cursor-pointer bg-white/[0.01] hover:bg-white/[0.02] transition-all group">
                            <input type="file" name="report_file" id="report_file" required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="updateFileName(this)">
                            
                            <div class="space-y-2">
                                <svg class="w-10 h-10 text-purple-400 group-hover:scale-105 transition-transform mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <div class="text-sm font-semibold text-white">Haz clic o arrastra el archivo aquí</div>
                                <p class="text-xs text-gray-500">Admite formatos de reporte exportados directos de SNC Pharma.</p>
                            </div>
                        </div>
                        <div id="file-name-display" class="mt-3 text-xs text-purple-300 font-semibold hidden flex items-center space-x-1.5 justify-center bg-purple-500/10 p-2 rounded-xl border border-purple-500/20">
                            <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span id="selected-file-name"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Tasa de Cambio (Bs. por Dólar)</label>
                        <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 placeholder-gray-505 transition-all text-gray-300"
                            placeholder="Ej. 45.50">
                    </div>

                    <div class="flex items-center space-x-3.5 pt-4 border-t border-white/5">
                        <button type="button" onclick="toggleModal('import-modal')" 
                            class="flex-1 py-3 text-xs font-bold text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit" 
                            class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-purple-600/20">
                            Procesar Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="glass-card mt-auto border-t border-white/5 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-gray-500">
            &copy; 2026 SNC Pharma. Todos los derechos reservados. | Panel de Estadísticas Comerciales
        </div>
    </footer>

    <!-- JS Logic -->
    <script>
        // Modal Toggler
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Display Uploaded File Name
        function updateFileName(input) {
            const display = document.getElementById('file-name-display');
            const label = document.getElementById('selected-file-name');
            if (input.files && input.files[0]) {
                label.textContent = input.files[0].name;
                display.classList.remove('hidden');
            } else {
                display.classList.add('hidden');
            }
        }

        // Clients Accordion Toggle
        function toggleClientDetails(clientCode) {
            const details = document.getElementById('details-' + clientCode);
            const icon = document.getElementById('icon-' + clientCode);
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                details.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // Client Search Filter
        function filterClients() {
            const searchVal = document.getElementById('search-input').value.toLowerCase();
            const cards = document.getElementsByClassName('client-card');
            
            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getAttribute('data-name');
                if (name.includes(searchVal)) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        // Fullscreen Toggle
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    updateFullscreenIcon(true);
                    showChartsOnly(true);
                }).catch(err => {
                    console.error('Error al entrar en pantalla completa:', err);
                });
            } else {
                document.exitFullscreen().then(() => {
                    updateFullscreenIcon(false);
                    showChartsOnly(false);
                }).catch(err => {
                    console.error('Error al salir de pantalla completa:', err);
                });
            }
        }

        // Show only charts in fullscreen mode
        function showChartsOnly(showOnlyCharts) {
            const nav = document.querySelector('nav');
            const filterBar = document.querySelector('.flex.flex-col.sm\\:flex-row.sm\\:items-center.sm\\:justify-between.gap-4.mb-8');
            const searchSection = document.querySelector('.mb-6.flex.flex-col.sm\\:flex-row');
            const clientsContainer = document.getElementById('clients-container');
            const footer = document.querySelector('footer');
            const chartsContainer = document.getElementById('charts-container');
            const additionalChartsContainer = document.getElementById('additional-charts-container');
            const main = document.querySelector('main');
            const exitBtn = document.getElementById('exit-fullscreen-btn');

            if (showOnlyCharts) {
                // Hide all non-chart elements
                if (nav) nav.style.display = 'none';
                if (filterBar) filterBar.style.display = 'none';
                if (searchSection) searchSection.style.display = 'none';
                if (clientsContainer) clientsContainer.style.display = 'none';
                if (footer) footer.style.display = 'none';
                
                // Show floating exit button
                if (exitBtn) exitBtn.classList.remove('hidden');
                
                // Show only charts and adjust layout
                if (chartsContainer) {
                    chartsContainer.style.display = 'grid';
                    chartsContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
                    chartsContainer.style.gap = '2rem';
                    chartsContainer.style.padding = '2rem';
                    chartsContainer.style.height = '50vh';
                }
                if (additionalChartsContainer) {
                    additionalChartsContainer.style.display = 'grid';
                    additionalChartsContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
                    additionalChartsContainer.style.gap = '2rem';
                    additionalChartsContainer.style.padding = '0 2rem 2rem 2rem';
                    additionalChartsContainer.style.height = '50vh';
                }
                if (main) {
                    main.style.padding = '0';
                    main.style.maxWidth = '100%';
                }
            } else {
                // Restore all elements
                if (nav) nav.style.display = '';
                if (filterBar) filterBar.style.display = '';
                if (searchSection) searchSection.style.display = '';
                if (clientsContainer) clientsContainer.style.display = '';
                if (footer) footer.style.display = '';
                
                // Hide floating exit button
                if (exitBtn) exitBtn.classList.add('hidden');
                
                // Restore charts layout
                if (chartsContainer) {
                    chartsContainer.style.display = '';
                    chartsContainer.style.gridTemplateColumns = '';
                    chartsContainer.style.gap = '';
                    chartsContainer.style.padding = '';
                    chartsContainer.style.height = '';
                }
                if (additionalChartsContainer) {
                    additionalChartsContainer.style.display = '';
                    additionalChartsContainer.style.gridTemplateColumns = '';
                    additionalChartsContainer.style.gap = '';
                    additionalChartsContainer.style.padding = '';
                    additionalChartsContainer.style.height = '';
                }
                if (main) {
                    main.style.padding = '';
                    main.style.maxWidth = '';
                }
            }
        }

        // Update fullscreen icon based on state
        function updateFullscreenIcon(isFullscreen) {
            const icon = document.getElementById('fullscreen-icon');
            const btnText = document.querySelector('#fullscreen-btn span');
            if (isFullscreen) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                if (btnText) btnText.textContent = 'Salir de Pantalla Completa';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />';
                if (btnText) btnText.textContent = 'Pantalla Completa';
            }
        }

        // Listen for fullscreen change events (e.g., when user presses ESC)
        document.addEventListener('fullscreenchange', () => {
            const isFullscreen = !!document.fullscreenElement;
            updateFullscreenIcon(isFullscreen);
            showChartsOnly(isFullscreen);
        });

        @if ($hasData)
        // ----------------- CHARTS INITIALIZATION -----------------
        Chart.defaults.color = '#9ca3af';
        Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';

        // View type from server
        const viewType = '{{ $viewType }}';

        // 1. Sales Trend Line Chart (Dynamic from DB)
        const trendData = {!! json_encode($monthlyTrend) !!};
        const trendLabels = trendData.map(item => item.label);
        const trendTotals = trendData.map(item => viewType === 'units' ? item.total_qty : item.total_sales);

        const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
        const salesGradient = salesTrendCtx.createLinearGradient(0, 0, 0, 300);
        salesGradient.addColorStop(0, 'rgba(168, 85, 247, 0.4)');
        salesGradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

        new Chart(salesTrendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: viewType === 'units' ? 'Unidades' : 'Facturación ($)',
                    data: trendTotals,
                    borderColor: '#a855f7',
                    borderWidth: 3,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    fill: true,
                    backgroundColor: salesGradient,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (viewType === 'units') {
                                    return context.raw.toLocaleString('es-VE', {maximumFractionDigits: 0}) + ' unidades';
                                } else {
                                    return '$ ' + context.raw.toLocaleString('es-VE', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            callback: function(value) {
                                if (viewType === 'units') {
                                    return value >= 1e6 ? (value/1e6).toFixed(1) + 'M' : (value/1e3).toFixed(0) + 'k';
                                } else {
                                    return '$ ' + (value >= 1e6 ? (value/1e6).toFixed(1) + 'M' : (value/1e3).toFixed(0) + 'k');
                                }
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Class Distribution Doughnut Chart (Dynamic from DB)
        const classData = {!! json_encode($salesByClass) !!};
        const classLabels = classData.map(item => item.client_class);
        const classTotals = classData.map(item => parseFloat(viewType === 'units' ? item.total_qty : item.total_sales));

        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: classLabels,
                datasets: [{
                    data: classTotals,
                    backgroundColor: [
                        '#6366f1',
                        '#a855f7',
                        '#ec4899',
                        '#f59e0b',
                        '#10b981',
                        '#06b6d4',
                        '#ef4444',
                        '#8b5cf6',
                        '#f97316',
                        '#14b8a6'
                    ],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: { size: 12 },
                            color: '#9ca3af',
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / total) * 100).toFixed(1);
                                if (viewType === 'units') {
                                    return label + ': ' + value.toLocaleString('es-VE', {maximumFractionDigits: 0}) + ' unidades (' + percentage + '%)';
                                } else {
                                    return label + ': $ ' + value.toLocaleString('es-VE', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            }
        });

        // 3. Top Products Bar Chart
        const productData = {!! json_encode($salesByProduct) !!};
        const productLabels = productData.map(item => item.product_description.substring(0, 30) + (item.product_description.length > 30 ? '...' : ''));
        const productTotals = productData.map(item => parseFloat(viewType === 'units' ? item.total_qty : item.total_sales));

        const productsCtx = document.getElementById('productsBarChart').getContext('2d');
        const productGradient = productsCtx.createLinearGradient(0, 0, 0, 400);
        productGradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)');
        productGradient.addColorStop(1, 'rgba(16, 185, 129, 0.2)');

        new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: productLabels,
                datasets: [{
                    label: viewType === 'units' ? 'Unidades' : 'Ventas ($)',
                    data: productTotals,
                    backgroundColor: productGradient,
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (viewType === 'units') {
                                    return context.raw.toLocaleString('es-VE', {maximumFractionDigits: 0}) + ' unidades';
                                } else {
                                    return '$ ' + context.raw.toLocaleString('es-VE', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            callback: function(value) {
                                if (viewType === 'units') {
                                    return value >= 1e6 ? (value/1e6).toFixed(1) + 'M' : (value/1e3).toFixed(0) + 'k';
                                } else {
                                    return '$ ' + (value >= 1e6 ? (value/1e6).toFixed(1) + 'M' : (value/1e3).toFixed(0) + 'k');
                                }
                            }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            color: '#9ca3af'
                        }
                    }
                }
            }
        });

        // 4. Top Clients Bar Chart
        const clientData = {!! json_encode($salesByClient->take(15)) !!};
        const clientLabels = clientData.map(item => item.name.substring(0, 25) + (item.name.length > 25 ? '...' : ''));
        const clientTotals = clientData.map(item => parseFloat(viewType === 'units' ? item.total_qty : item.total_sales));

        const clientsCtx = document.getElementById('clientsBarChart').getContext('2d');
        const clientGradient = clientsCtx.createLinearGradient(0, 0, 0, 400);
        clientGradient.addColorStop(0, 'rgba(236, 72, 153, 0.8)');
        clientGradient.addColorStop(1, 'rgba(236, 72, 153, 0.2)');

        new Chart(clientsCtx, {
            type: 'bar',
            data: {
                labels: clientLabels,
                datasets: [{
                    label: viewType === 'units' ? 'Unidades' : 'Ventas ($)',
                    data: clientTotals,
                    backgroundColor: clientGradient,
                    borderColor: '#ec4899',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (viewType === 'units') {
                                    return context.raw.toLocaleString('es-VE', {maximumFractionDigits: 0}) + ' unidades';
                                } else {
                                    return '$ ' + context.raw.toLocaleString('es-VE', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            callback: function(value) {
                                if (viewType === 'units') {
                                    return value >= 1e6 ? (value/1e6).toFixed(1) + 'M' : (value/1e3).toFixed(0) + 'k';
                                } else {
                                    return '$ ' + (value >= 1e6 ? (value/1e6).toFixed(1) + 'M' : (value/1e3).toFixed(0) + 'k');
                                }
                            }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            color: '#9ca3af'
                        }
                    }
                }
            }
        });
        @endif
    </script>
</body>
</html>
