<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SNC Pharma - Entrada Manual</title>
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
                        <span class="text-[10px] text-purple-400 font-semibold block uppercase tracking-wider -mt-1">Entrada Manual</span>
                    </div>
                </div>

                <!-- Back to Dashboard -->
                <div>
                    <a href="{{ route('dashboard') }}" 
                        class="text-xs font-semibold px-3 py-2 sm:px-4 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="hidden sm:inline">Volver al Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-white tracking-tight">{{ isset($editDate) ? 'Editar ventas de ' . $editMonthLabel : 'Entrada Manual de Ventas' }}</h1>
                <p class="text-gray-400 text-sm mt-1">{{ isset($editDate) ? 'Modifica los clientes y productos. Al guardar se reemplazarán todos los registros de este mes.' : 'Agregar múltiples ventas manualmente a un mes existente' }}</p>
            </div>

            <!-- Form -->
            <div class="glass-card rounded-2xl p-8">
                <form action="{{ isset($editDate) ? route('manual-entry.update', ['date' => $editDate]) : route('manual-entry.store') }}" method="POST" id="manual-entry-form">
                    @csrf

                    <!-- Month, Year and Exchange Rate Selection -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                        @if (isset($editDate))
                            <input type="hidden" name="month" value="{{ $editMonth }}">
                            <input type="hidden" name="year" value="{{ $editYear }}">
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Mes</label>
                                <input type="text" disabled value="{{ $editMonthLabel }}"
                                    class="w-full bg-white/5 border border-white/15 rounded-xl px-4 py-3 text-white text-sm opacity-60 cursor-not-allowed">
                            </div>
                            <div class="hidden sm:block"></div>
                        @else
                            <div>
                                <label for="month" class="block text-sm font-semibold text-gray-300 mb-2">Mes</label>
                                <select id="month" name="month" required
                                    style="background-color: #0c0a18; color: #fff;"
                                    class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                    <option value="" style="background-color: #0c0a18; color: #fff;">Seleccionar mes</option>
                                    <option value="1" {{ old('month') == 1 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Enero</option>
                                    <option value="2" {{ old('month') == 2 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Febrero</option>
                                    <option value="3" {{ old('month') == 3 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Marzo</option>
                                    <option value="4" {{ old('month') == 4 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Abril</option>
                                    <option value="5" {{ old('month') == 5 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Mayo</option>
                                    <option value="6" {{ old('month') == 6 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Junio</option>
                                    <option value="7" {{ old('month') == 7 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Julio</option>
                                    <option value="8" {{ old('month') == 8 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Agosto</option>
                                    <option value="9" {{ old('month') == 9 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Septiembre</option>
                                    <option value="10" {{ old('month') == 10 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Octubre</option>
                                    <option value="11" {{ old('month') == 11 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Noviembre</option>
                                    <option value="12" {{ old('month') == 12 ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">Diciembre</option>
                                </select>
                            </div>
                            <div>
                                <label for="year" class="block text-sm font-semibold text-gray-300 mb-2">Año</label>
                                <select id="year" name="year" required
                                    style="background-color: #0c0a18; color: #fff;"
                                    class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                    <option value="" style="background-color: #0c0a18; color: #fff;">Seleccionar año</option>
                                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ old('year') == $i ? 'selected' : '' }} style="background-color: #0c0a18; color: #fff;">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif
                        <div>
                            <label for="exchange_rate" class="block text-sm font-semibold text-gray-300 mb-2">Tasa de cambio (Bs/$)</label>
                            <input type="number" id="exchange_rate" name="exchange_rate" step="0.01" min="0.01" value="{{ old('exchange_rate', isset($editExchangeRate) ? $editExchangeRate : 1) }}" required
                                class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all"
                                placeholder="Ej: 55.50">
                            <p class="text-[11px] text-gray-500 mt-1.5">Ingrese monto en Bs por cada dólar</p>
                        </div>
                    </div>

                    <!-- Client Blocks Container -->
                    <div id="clients-container" class="space-y-6 mb-6"></div>

                    <!-- Add Client Button -->
                    <button type="button" onclick="addClientBlock()"
                        class="w-full py-3 border border-dashed border-purple-500/30 hover:border-purple-500/60 hover:bg-purple-500/5 text-gray-400 hover:text-purple-400 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2 mb-8">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar cliente
                    </button>

                    <!-- Submit and Cancel Buttons -->
                    <div class="flex items-center justify-between gap-4 border-t border-white/5 pt-6">
                        <span id="entry-count" class="text-xs text-gray-500">0 entradas</span>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white text-sm font-semibold rounded-xl transition-all">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-xl transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Guardar Ventas
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const clientsData = @json($clients);
        const productsData = @json($products);
        const existingEntries = @json($existingEntries ?? []);

        let clientIdx = 0;
        let entryIdx = 0;

        function buildClientOptions() {
            let html = '<option value="">Seleccionar cliente</option>';
            clientsData.forEach(c => {
                html += `<option value="${c.client_code}" class="bg-[#090714] text-white">${c.client_name} (${c.client_code})</option>`;
            });
            return html;
        }

        function buildProductOptions() {
            let html = '<option value="">Seleccionar producto</option>';
            productsData.forEach(p => {
                html += `<option value="${p.product_code}" class="bg-[#090714] text-white">${p.product_description} (${p.product_code})</option>`;
            });
            return html;
        }

        function addClientBlock(data = null) {
            const ci = clientIdx++;
            const container = document.getElementById('clients-container');

            const block = document.createElement('div');
            block.className = 'client-block border border-white/10 rounded-2xl overflow-hidden';
            block.dataset.clientIdx = ci;

            block.innerHTML = `
                <!-- Client Header -->
                <div class="bg-white/[0.03] border-b border-white/5 px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Cliente</label>
                        <select class="client-select w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all"
                            onchange="updateClientHiddenFields(${ci}, this.value)"
                            required>
                            ${buildClientOptions()}
                        </select>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 mt-1 sm:mt-5">
                        <button type="button" onclick="addProductRow(${ci})"
                            class="px-3 py-2 bg-purple-600/20 hover:bg-purple-600/30 border border-purple-500/30 text-purple-400 hover:text-purple-300 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Agregar producto
                        </button>
                        <button type="button" onclick="removeClientBlock(this)"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 transition-all border border-red-500/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Products Table Header -->
                <div class="hidden sm:grid grid-cols-12 gap-3 px-5 py-2 bg-white/[0.01]">
                    <div class="col-span-7 text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto / Descripción</div>
                    <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cant. Vendida</div>
                    <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ventas (Bs)</div>
                    <div class="col-span-1"></div>
                </div>

                <!-- Products Rows -->
                <div class="products-container px-5 py-3 space-y-2" data-client-idx="${ci}"></div>
            `;

            container.appendChild(block);

            if (data) {
                const clientSelect = block.querySelector('.client-select');
                clientSelect.value = data.client_code;
                updateClientHiddenFields(ci, data.client_code);
                if (data.products && data.products.length > 0) {
                    data.products.forEach(p => addProductRow(ci, p));
                } else {
                    addProductRow(ci);
                }
            } else {
                addProductRow(ci);
            }

            updateEntryCount();
        }

        function updateClientHiddenFields(ci, clientCode) {
            // Update all hidden client_code inputs in this block's product rows
            const block = document.querySelector(`.client-block[data-client-idx="${ci}"]`);
            if (!block) return;
            block.querySelectorAll('.hidden-client-code').forEach(input => {
                input.value = clientCode;
            });
        }

        function addProductRow(ci, productData = null) {
            const ei = entryIdx++;
            const productsContainer = document.querySelector(`.products-container[data-client-idx="${ci}"]`);
            if (!productsContainer) return;

            // Get current client code from the block's select
            const block = document.querySelector(`.client-block[data-client-idx="${ci}"]`);
            const clientCode = block ? block.querySelector('.client-select').value : '';

            const isManual = productData ? !!productData.is_manual : false;

            const row = document.createElement('div');
            row.className = 'product-row grid grid-cols-12 gap-3 items-center py-1';
            row.innerHTML = `
                <input type="hidden" name="entries[${ei}][client_code]" class="hidden-client-code" value="${clientCode}">
                ${isManual ? `<input type="hidden" name="entries[${ei}][is_manual]" value="1">` : ''}
                <div class="col-span-12 sm:col-span-7">
                    ${isManual ? '<div class="text-[10px] font-bold text-purple-400 mb-1 uppercase tracking-wide">Registro Manual</div>' : ''}
                    <select name="entries[${ei}][product_code]" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                        ${buildProductOptions()}
                    </select>
                </div>
                <div class="col-span-5 sm:col-span-2">
                    <input type="number" name="entries[${ei}][quantity]" step="1" min="0" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all"
                        placeholder="0">
                </div>
                <div class="col-span-5 sm:col-span-2">
                    <input type="number" name="entries[${ei}][total_sales_bs]" step="0.01" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all"
                        placeholder="0.00">
                </div>
                <div class="col-span-2 sm:col-span-1 flex justify-end">
                    <button type="button" onclick="removeProductRow(this)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/5 hover:bg-red-500/20 text-gray-500 hover:text-red-400 transition-all border border-white/5 hover:border-red-500/20">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;
            productsContainer.appendChild(row);

            if (productData) {
                const select = row.querySelector('select');
                if (select) select.value = productData.product_code;
                const qtyInput = row.querySelector('input[step="1"]');
                if (qtyInput) qtyInput.value = productData.quantity;
                const salesInput = row.querySelector('input[step="0.01"]');
                if (salesInput) salesInput.value = productData.total_sales_bs;
            }

            updateEntryCount();
        }

        function removeProductRow(btn) {
            btn.closest('.product-row').remove();
            updateEntryCount();
        }

        function removeClientBlock(btn) {
            btn.closest('.client-block').remove();
            updateEntryCount();
        }

        function updateEntryCount() {
            const count = document.querySelectorAll('.product-row').length;
            document.getElementById('entry-count').textContent = count + (count === 1 ? ' entrada' : ' entradas');
        }

        // Pre-populate on load
        document.addEventListener('DOMContentLoaded', () => {
            if (existingEntries.length > 0) {
                existingEntries.forEach(e => addClientBlock(e));
            } else {
                addClientBlock();
            }
        });
    </script>
</body>
</html>
