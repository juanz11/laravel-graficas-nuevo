<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SNC Pharma - Clientes</title>
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
                        <span class="text-[10px] text-cyan-400 font-semibold block uppercase tracking-wider -mt-1">Gestión de Clientes</span>
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
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Clientes</h1>
            <p class="text-gray-400 text-sm mt-1">Edita la información de un cliente (nombre, clase o código) y se aplicará a todos sus registros de venta.</p>
        </div>

        <!-- Search Section -->
        <div class="glass-card rounded-2xl p-6 mb-8">
            <form method="GET" action="{{ route('clients.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label for="filter-search" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Buscar cliente</label>
                    <input type="text" id="filter-search" name="search" value="{{ $search }}"
                        class="w-full bg-white/5 border border-white/10 hover:border-white/20 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 placeholder-gray-500 transition-all"
                        placeholder="Código, nombre o clase...">
                </div>
                <div class="flex items-center space-x-2 w-full sm:w-auto">
                    <button type="submit"
                        class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-1.5 h-10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Buscar
                    </button>
                    @if($search)
                        <a href="{{ route('clients.index') }}"
                            class="px-4 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white text-xs font-bold rounded-xl transition-all h-10 flex items-center justify-center">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Clients Table -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] text-[10px] font-bold uppercase tracking-wider text-purple-400 border-b border-white/5">
                            <th class="px-6 py-4">Código</th>
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4">Clase</th>
                            <th class="px-6 py-4 text-right">Registros</th>
                            <th class="px-6 py-4 text-right">Unidades</th>
                            <th class="px-6 py-4 text-right">Ventas ($)</th>
                            <th class="px-6 py-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-gray-300">
                        @forelse ($clients as $client)
                            <tr class="hover:bg-white/[0.01] transition-all">
                                <td class="px-6 py-3.5 font-mono text-[11px] text-gray-400">{{ $client->client_code }}</td>
                                <td class="px-6 py-3.5 text-white font-medium">{{ $client->client_name }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/20">{{ $client->client_class ?? 'SIN CLASE' }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-right text-gray-400">{{ number_format($client->records, 0, ',', '.') }}</td>
                                <td class="px-6 py-3.5 text-right font-semibold text-white">{{ number_format($client->total_qty, 0, ',', '.') }}</td>
                                <td class="px-6 py-3.5 text-right text-emerald-400 font-semibold whitespace-nowrap">$ {{ number_format($client->total_sales, 2, ',', '.') }}</td>
                                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                    <button type="button"
                                        onclick="openClientModal(this)"
                                        data-code="{{ $client->client_code }}"
                                        data-name="{{ $client->client_name }}"
                                        data-class="{{ $client->client_class }}"
                                        data-records="{{ $client->records }}"
                                        class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-white/5 hover:bg-indigo-500/20 border border-white/5 text-gray-400 hover:text-indigo-400 transition-all"
                                        title="Editar cliente">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    No se encontraron clientes para la búsqueda aplicada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-white/5 font-medium">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-gray-500">
                        Mostrando <span class="text-white font-semibold">{{ $clients->firstItem() ?? 0 }}</span> a <span class="text-white font-semibold">{{ $clients->lastItem() ?? 0 }}</span> de <span class="text-white font-semibold">{{ $clients->total() }}</span> clientes
                    </div>
                    <div class="flex items-center space-x-1">
                        @if ($clients->onFirstPage())
                            <span class="px-3.5 py-2 text-xs text-gray-600 bg-white/[0.01] border border-white/5 rounded-xl cursor-not-allowed">Anterior</span>
                        @else
                            <a href="{{ $clients->previousPageUrl() }}" class="px-3.5 py-2 text-xs text-white bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all">Anterior</a>
                        @endif

                        @if ($clients->hasMorePages())
                            <a href="{{ $clients->nextPageUrl() }}" class="px-3.5 py-2 text-xs text-white bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all">Siguiente</a>
                        @else
                            <span class="px-3.5 py-2 text-xs text-gray-600 bg-white/[0.01] border border-white/5 rounded-xl cursor-not-allowed">Siguiente</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Client Modal -->
    <div id="edit-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Overlay background -->
            <div onclick="toggleModal('edit-modal')" class="fixed inset-0 bg-[#070510]/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <!-- Modal Content Card -->
            <div class="relative inline-block align-bottom bg-[#0c0a18] border border-white/10 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 sm:p-8">
                <div class="absolute -top-16 -right-16 w-32 h-32 rounded-full bg-indigo-600/10 blur-xl pointer-events-none"></div>
                
                <div class="flex items-center justify-between pb-4 border-b border-white/5 mb-6">
                    <h3 class="text-lg font-bold text-white" id="modal-title">Editar Cliente</h3>
                    <button onclick="toggleModal('edit-modal')" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('clients.update') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="original_code" id="edit-original-code">

                    <div>
                        <label for="edit-client-code" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Código</label>
                        <input type="text" id="edit-client-code" name="client_code" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 transition-all font-mono">
                        <p class="text-[11px] text-gray-500 mt-1.5">Si usas el código de otro cliente existente, ambos se fusionarán.</p>
                    </div>

                    <div>
                        <label for="edit-client-name" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Nombre</label>
                        <input type="text" id="edit-client-name" name="client_name" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 transition-all">
                    </div>

                    <div>
                        <label for="edit-client-class" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Clase</label>
                        <input type="text" id="edit-client-class" name="client_class" list="classes-datalist"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 transition-all">
                        <datalist id="classes-datalist">
                            @foreach ($classesList as $class)
                                <option value="{{ $class }}">
                            @endforeach
                        </datalist>
                    </div>

                    <p class="text-xs text-amber-300/80 bg-amber-500/5 border border-amber-500/10 rounded-xl p-3">
                        El cambio se aplicará a <span id="edit-records-count" class="font-bold"></span> registro(s) de venta de este cliente.
                    </p>

                    <div class="flex items-center space-x-3.5 pt-4 border-t border-white/5">
                        <button type="button" onclick="toggleModal('edit-modal')" 
                            class="flex-1 py-3 text-xs font-bold text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit" 
                            class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-600/20">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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

        function openClientModal(btn) {
            document.getElementById('edit-original-code').value = btn.dataset.code;
            document.getElementById('edit-client-code').value = btn.dataset.code;
            document.getElementById('edit-client-name').value = btn.dataset.name;
            document.getElementById('edit-client-class').value = btn.dataset.class;
            document.getElementById('edit-records-count').textContent = btn.dataset.records;
            toggleModal('edit-modal');
        }
    </script>
</body>
</html>
