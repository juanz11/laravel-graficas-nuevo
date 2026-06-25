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
                <ul class="text-red-400 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Entrada Manual de Ventas</h1>
                <p class="text-gray-400 text-sm mt-1">Agregar ventas manualmente a un mes existente</p>
            </div>

            <!-- Form -->
            <div class="glass-card rounded-2xl p-8">
                <form action="{{ route('manual-entry.store') }}" method="POST">
                    @csrf

                    <!-- Month and Year Selection -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="month" class="block text-sm font-semibold text-gray-300 mb-2">Mes</label>
                            <select id="month" name="month" required
                                class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                <option value="">Seleccionar mes</option>
                                <option value="1" {{ old('month') == 1 ? 'selected' : '' }} class="bg-[#090714] text-white">Enero</option>
                                <option value="2" {{ old('month') == 2 ? 'selected' : '' }} class="bg-[#090714] text-white">Febrero</option>
                                <option value="3" {{ old('month') == 3 ? 'selected' : '' }} class="bg-[#090714] text-white">Marzo</option>
                                <option value="4" {{ old('month') == 4 ? 'selected' : '' }} class="bg-[#090714] text-white">Abril</option>
                                <option value="5" {{ old('month') == 5 ? 'selected' : '' }} class="bg-[#090714] text-white">Mayo</option>
                                <option value="6" {{ old('month') == 6 ? 'selected' : '' }} class="bg-[#090714] text-white">Junio</option>
                                <option value="7" {{ old('month') == 7 ? 'selected' : '' }} class="bg-[#090714] text-white">Julio</option>
                                <option value="8" {{ old('month') == 8 ? 'selected' : '' }} class="bg-[#090714] text-white">Agosto</option>
                                <option value="9" {{ old('month') == 9 ? 'selected' : '' }} class="bg-[#090714] text-white">Septiembre</option>
                                <option value="10" {{ old('month') == 10 ? 'selected' : '' }} class="bg-[#090714] text-white">Octubre</option>
                                <option value="11" {{ old('month') == 11 ? 'selected' : '' }} class="bg-[#090714] text-white">Noviembre</option>
                                <option value="12" {{ old('month') == 12 ? 'selected' : '' }} class="bg-[#090714] text-white">Diciembre</option>
                            </select>
                        </div>
                        <div>
                            <label for="year" class="block text-sm font-semibold text-gray-300 mb-2">Año</label>
                            <select id="year" name="year" required
                                class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                                <option value="">Seleccionar año</option>
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ old('year') == $i ? 'selected' : '' }} class="bg-[#090714] text-white">
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Client Selection -->
                    <div class="mb-6">
                        <label for="client_code" class="block text-sm font-semibold text-gray-300 mb-2">Cliente</label>
                        <select id="client_code" name="client_code" required
                            class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                            <option value="">Seleccionar cliente</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->client_code }}" {{ old('client_code') == $client->client_code ? 'selected' : '' }} class="bg-[#090714] text-white">
                                    {{ $client->client_name }} ({{ $client->client_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Selection -->
                    <div class="mb-6">
                        <label for="product_code" class="block text-sm font-semibold text-gray-300 mb-2">Producto</label>
                        <select id="product_code" name="product_code" required
                            class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 cursor-pointer transition-all">
                            <option value="">Seleccionar producto</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->product_code }}" {{ old('product_code') == $product->product_code ? 'selected' : '' }} class="bg-[#090714] text-white">
                                    {{ $product->product_description }} ({{ $product->product_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quantity and Sales -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="quantity" class="block text-sm font-semibold text-gray-300 mb-2">Cantidad Vendida</label>
                            <input type="number" id="quantity" name="quantity" step="0.01" min="0" required
                                value="{{ old('quantity') }}"
                                class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all"
                                placeholder="0">
                        </div>
                        <div>
                            <label for="total_sales" class="block text-sm font-semibold text-gray-300 mb-2">Ventas ($)</label>
                            <input type="number" id="total_sales" name="total_sales" step="0.01" min="0" required
                                value="{{ old('total_sales') }}"
                                class="w-full bg-white/5 border border-white/15 hover:border-white/20 rounded-xl px-4 py-3 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all"
                                placeholder="0.00">
                        </div>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white text-sm font-semibold rounded-xl transition-all">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-xl transition-all">
                            Guardar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
