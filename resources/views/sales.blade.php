<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SNC Pharma - Gestionar Ventas</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Background glow -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-indigo-500/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] rounded-full bg-purple-500/5 blur-[120px] pointer-events-none"></div>

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
                        <span class="text-[10px] text-indigo-400 font-semibold block uppercase tracking-wider -mt-1">Gestionar Ventas</span>
                    </div>
                </div>

                <!-- Back to Dashboard -->
                <div>
                    <a href="{{ route('dashboard') }}" 
                        class="text-xs font-semibold px-3 py-2 sm:px-4 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Volver al Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                <p class="text-emerald-400 text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                <ul class="text-red-400 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Administración de Ventas</h1>
            <p class="text-gray-400 text-sm mt-1">Busca, edita o elimina registros de ventas (tanto importados como cargados manualmente)</p>
        </div>

        <!-- Filters Section -->
        <div class="glass-card rounded-2xl p-6 mb-8">
            <form method="GET" action="{{ route('sales.list') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                <!-- Month Filter -->
                <div>
                    <label for="filter-month" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Mes del reporte</label>
                    <select id="filter-month" name="month"
                        style="background-color: #0c0a18; color: #fff;"
                        class="w-full bg-white/5 border border-white/10 hover:border-white/20 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer transition-all">
                        <option value="" style="background-color: #0c0a18; color: #fff;">Todos los meses</option>
                        @foreach ($months as $m)
                            <option value="{{ $m['val'] }}" {{ $selectedMonthVal === $m['val'] ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Client Filter -->
                <div>
                    <label for="filter-client" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Cliente</label>
                    <select id="filter-client" name="client"
                        style="background-color: #0c0a18; color: #fff;"
                        class="w-full bg-white/5 border border-white/10 hover:border-white/20 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer transition-all">
                        <option value="" style="background-color: #0c0a18; color: #fff;">Todos los clientes</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->client_code }}" {{ $selectedClient === $c->client_code ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">{{ $c->client_name }} ({{ $c->client_code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Text Search -->
                <div>
                    <label for="filter-search" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Producto o Código</label>
                    <input type="text" id="filter-search" name="search" value="{{ $search }}"
                        class="w-full bg-white/5 border border-white/10 hover:border-white/20 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 placeholder-gray-500 transition-all"
                        placeholder="Ej: Ibuprofeno, SNC...">
                </div>

                <!-- Search Actions -->
                <div class="flex items-center space-x-2">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-1.5 h-10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Filtrar
                    </button>
                    @if($selectedMonthVal || $selectedClient || $search)
                        <a href="{{ route('sales.list') }}"
                            class="px-4 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white text-xs font-bold rounded-xl transition-all h-10 flex items-center justify-center">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Sales Records Table -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] text-[10px] font-bold uppercase tracking-wider text-purple-400 border-b border-white/5">
                            <th class="px-6 py-4">Mes del reporte</th>
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4">Producto</th>
                            <th class="px-6 py-4 text-right">Cantidad</th>
                            <th class="px-6 py-4 text-right">Venta (Bs)</th>
                            <th class="px-6 py-4 text-right">Costo (Bs)</th>
                            <th class="px-6 py-4 text-right">Utilidad (Bs)</th>
                            <th class="px-6 py-4 text-center">Tipo</th>
                            <th class="px-6 py-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-gray-300">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-white/[0.01] transition-all">
                                <td class="px-6 py-3.5 font-medium whitespace-nowrap text-white">
                                    {{ \Carbon\Carbon::parse($sale->report_date)->format('m/Y') }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="block text-white font-medium">{{ $sale->client_name }}</span>
                                    <span class="block text-[10px] text-gray-500 font-mono">{{ $sale->client_code }} • Clase: {{ $sale->client_class ?? 'N/D' }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="block text-white font-medium">{{ $sale->product_description }}</span>
                                    <span class="block text-[10px] text-gray-500 font-mono">{{ $sale->product_code }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-right font-semibold text-white">
                                    {{ number_format($sale->quantity, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3.5 text-right text-emerald-400 font-semibold whitespace-nowrap">
                                    Bs. {{ number_format($sale->total_sales, 2, ',', '.') }}
                                    <span class="block text-[9px] text-gray-400 font-normal">(${{ number_format($sale->total_sales / ($sale->exchange_rate ?: 1), 2, ',', '.') }})</span>
                                </td>
                                <td class="px-6 py-3.5 text-right text-red-400 whitespace-nowrap">
                                    Bs. {{ number_format($sale->total_cost, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-3.5 text-right text-indigo-400 font-semibold whitespace-nowrap">
                                    Bs. {{ number_format($sale->total_utility, 2, ',', '.') }}
                                    <span class="block text-[9px] text-gray-500 font-normal">{{ number_format($sale->utility_percentage, 1, ',', '.') }}% util.</span>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    @if ($sale->is_manual)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Manual</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">Excel</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Edit button -->
                                        <button onclick="openEditModal({{ $sale->id }})" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/5 hover:bg-indigo-500/20 border border-white/5 text-gray-400 hover:text-indigo-400 transition-all"
                                            title="Editar registro">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <!-- Delete form/button -->
                                        <form method="POST" action="{{ route('sales.destroy', $sale->id) }}" onsubmit="return confirmDelete(event)" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/5 hover:bg-red-500/20 border border-white/5 text-gray-400 hover:text-red-400 transition-all"
                                                title="Eliminar registro">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    No se encontraron registros de ventas para los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination -->
            <div class="px-6 py-4 border-t border-white/5 font-medium">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-gray-500">
                        Mostrando <span class="text-white font-semibold">{{ $sales->firstItem() ?? 0 }}</span> a <span class="text-white font-semibold">{{ $sales->lastItem() ?? 0 }}</span> de <span class="text-white font-semibold">{{ $sales->total() }}</span> registros
                    </div>
                    <div class="flex items-center space-x-1">
                        @if ($sales->onFirstPage())
                            <span class="px-3.5 py-2 text-xs text-gray-600 bg-white/[0.01] border border-white/5 rounded-xl cursor-not-allowed">Anterior</span>
                        @else
                            <a href="{{ $sales->previousPageUrl() }}" class="px-3.5 py-2 text-xs text-white bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all">Anterior</a>
                        @endif

                        @php
                            $start = max($sales->currentPage() - 2, 1);
                            $end = min($start + 4, $sales->lastPage());
                            if ($end - $start < 4) {
                                $start = max($end - 4, 1);
                            }
                        @endphp

                        @if($start > 1)
                            <a href="{{ $sales->url(1) }}" class="w-9 h-9 flex items-center justify-center text-xs rounded-xl border {{ $sales->currentPage() == 1 ? 'bg-purple-600 text-white border-purple-500 shadow-lg shadow-purple-600/20' : 'bg-white/5 hover:bg-white/10 border-white/10 text-white' }}">1</a>
                            @if($start > 2)
                                <span class="text-gray-600 text-xs px-1">...</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            <a href="{{ $sales->url($i) }}" class="w-9 h-9 flex items-center justify-center text-xs rounded-xl border {{ $sales->currentPage() == $i ? 'bg-purple-600 text-white border-purple-500 shadow-lg shadow-purple-600/20' : 'bg-white/5 hover:bg-white/10 border-white/10 text-white' }}">{{ $i }}</a>
                        @endfor

                        @if($end < $sales->lastPage())
                            @if($end < $sales->lastPage() - 1)
                                <span class="text-gray-600 text-xs px-1">...</span>
                            @endif
                            <a href="{{ $sales->url($sales->lastPage()) }}" class="w-9 h-9 flex items-center justify-center text-xs rounded-xl border {{ $sales->currentPage() == $sales->lastPage() ? 'bg-purple-600 text-white border-purple-500 shadow-lg shadow-purple-600/20' : 'bg-white/5 hover:bg-white/10 border-white/10 text-white' }}">{{ $sales->lastPage() }}</a>
                        @endif

                        @if ($sales->hasMorePages())
                            <a href="{{ $sales->nextPageUrl() }}" class="px-3.5 py-2 text-xs text-white bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all">Siguiente</a>
                        @else
                            <span class="px-3.5 py-2 text-xs text-gray-600 bg-white/[0.01] border border-white/5 rounded-xl cursor-not-allowed">Siguiente</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="edit-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Overlay background -->
            <div onclick="toggleModal('edit-modal')" class="fixed inset-0 bg-[#070510]/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <!-- Modal Content Card -->
            <div class="relative inline-block align-bottom bg-[#0c0a18] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full p-6 sm:p-8">
                <div class="absolute -top-16 -right-16 w-32 h-32 rounded-full bg-purple-600/10 blur-xl pointer-events-none"></div>
                
                <div class="flex items-center justify-between pb-4 border-b border-white/5 mb-6">
                    <h3 class="text-lg font-bold text-white" id="modal-title">Editar Registro de Venta</h3>
                    <button onclick="toggleModal('edit-modal')" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="edit-form" method="POST" action="" class="space-y-5">
                    @csrf
                    <!-- ID Oculto -->
                    <input type="hidden" id="edit-id" name="id">

                    <!-- Mes y Año -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit-month" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Mes del reporte</label>
                            <select id="edit-month" name="month" required
                                style="background-color: #0c0a18; color: #fff;"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit-year" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Año</label>
                            <select id="edit-year" name="year" required
                                style="background-color: #0c0a18; color: #fff;"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer">
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Cliente y Producto -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit-client" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cliente</label>
                            <select id="edit-client" name="client_code" required
                                style="background-color: #0c0a18; color: #fff;"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer">
                                @foreach($clients as $c)
                                    <option value="{{ $c->client_code }}">{{ $c->client_name }} ({{ $c->client_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="edit-product" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Producto</label>
                            <select id="edit-product" name="product_code" required
                                style="background-color: #0c0a18; color: #fff;"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer">
                                @foreach($products as $p)
                                    <option value="{{ $p->product_code }}">{{ $p->product_description }} ({{ $p->product_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Cantidad, Tasa y Venta Bs -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="edit-quantity" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cantidad</label>
                            <input type="number" id="edit-quantity" name="quantity" required
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        </div>
                        <div>
                            <label for="edit-exchange-rate" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Tasa (Bs/$)</label>
                            <input type="number" step="0.0001" id="edit-exchange-rate" name="exchange_rate" required
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        </div>
                        <div>
                            <label for="edit-total-sales" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Ventas (Bs)</label>
                            <input type="number" step="0.01" id="edit-total-sales" name="total_sales" required
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        </div>
                    </div>

                    <!-- Costo, Utilidad y Checkbox Cálculo Auto -->
                    <div class="p-4 bg-white/[0.02] border border-white/5 rounded-2xl space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-purple-400 uppercase tracking-wider">Cálculos Internos</span>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" id="auto-calc" checked class="rounded bg-white/5 border-white/10 text-purple-600 focus:ring-purple-500/40">
                                <span class="text-[11px] text-gray-400 font-medium">Recalcular costo/utilidad (15% costo)</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="edit-total-cost" class="block text-xs text-gray-400 mb-1.5">Costo Total (Bs)</label>
                                <input type="number" step="0.01" id="edit-total-cost" name="total_cost" required
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3.5 py-2 text-white text-xs focus:outline-none focus:ring-1 focus:ring-purple-500/40">
                            </div>
                            <div>
                                <label for="edit-total-utility" class="block text-xs text-gray-400 mb-1.5">Utilidad Total (Bs)</label>
                                <input type="number" step="0.01" id="edit-total-utility" name="total_utility" required
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3.5 py-2 text-white text-xs focus:outline-none focus:ring-1 focus:ring-purple-500/40">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3.5 pt-4 border-t border-white/5">
                        <button type="button" onclick="toggleModal('edit-modal')" 
                            class="flex-1 py-3 text-xs font-bold text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit" 
                            class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-purple-600/20">
                            Guardar Cambios
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

        // JS Calculations Helper
        const totalSalesInput = document.getElementById('edit-total-sales');
        const totalCostInput = document.getElementById('edit-total-cost');
        const totalUtilityInput = document.getElementById('edit-total-utility');
        const autoCalcCheckbox = document.getElementById('auto-calc');

        function calculateValues() {
            if (autoCalcCheckbox.checked) {
                const sales = parseFloat(totalSalesInput.value) || 0;
                const cost = sales * 0.15;
                const utility = sales - cost;
                
                totalCostInput.value = cost.toFixed(2);
                totalUtilityInput.value = utility.toFixed(2);
            }
        }

        totalSalesInput.addEventListener('input', calculateValues);
        autoCalcCheckbox.addEventListener('change', calculateValues);

        // Open edit modal and load data via AJAX
        function openEditModal(saleId) {
            // Limpiar formulario antes
            document.getElementById('edit-form').reset();
            
            // Cargar datos por AJAX
            fetch(`/sales/${saleId}/edit`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al obtener la venta.');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('edit-id').value = data.id;
                    document.getElementById('edit-month').value = data.month;
                    document.getElementById('edit-year').value = data.year;
                    document.getElementById('edit-client').value = data.client_code;
                    document.getElementById('edit-product').value = data.product_code;
                    document.getElementById('edit-quantity').value = data.quantity;
                    document.getElementById('edit-exchange-rate').value = data.exchange_rate;
                    document.getElementById('edit-total-sales').value = data.total_sales;
                    document.getElementById('edit-total-cost').value = data.total_cost;
                    document.getElementById('edit-total-utility').value = data.total_utility;

                    // Asignar dinámicamente el ACTION del formulario
                    document.getElementById('edit-form').action = `/sales/${data.id}/update`;

                    // Desmarcar cálculo automático si el costo original no coincide con el 15% (con margen de tolerancia)
                    const originalSales = parseFloat(data.total_sales) || 0;
                    const originalCost = parseFloat(data.total_cost) || 0;
                    const expectedCost = originalSales * 0.15;
                    
                    if (originalSales > 0 && Math.abs(originalCost - expectedCost) > 0.05) {
                        autoCalcCheckbox.checked = false;
                    } else {
                        autoCalcCheckbox.checked = true;
                    }

                    // Mostrar el modal
                    toggleModal('edit-modal');
                })
                .catch(error => {
                    alert('Error al cargar los datos del registro: ' + error.message);
                });
        }

        // Delete Confirm
        function confirmDelete(event) {
            if (!confirm('¿Estás seguro de que deseas eliminar este registro de venta de forma permanente? Esta acción no se puede deshacer.')) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
