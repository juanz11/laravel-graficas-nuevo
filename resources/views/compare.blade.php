<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SNC Pharma - Comparar Reporte</title>
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
                        <span class="text-[10px] text-purple-400 font-semibold block uppercase tracking-wider -mt-1">Comparar Reporte</span>
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
    <main class="flex-grow relative z-10 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                <ul class="text-red-400 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Comparar Reporte Excel</h1>
            <p class="text-gray-400 text-sm mt-1">Sube un reporte para ver las diferencias contra el mes guardado — <strong class="text-purple-300">no se importa nada</strong>.</p>
        </div>

        <!-- Upload Form -->
        <div class="glass-card rounded-2xl p-6 sm:p-8 mb-8">
            <form method="POST" action="{{ route('compare.run') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-end gap-4">
                @csrf
                <div class="flex-grow">
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Archivo del Reporte (.xlsx, .xls, .csv, .txt)</label>
                    <div class="relative border-2 border-dashed border-white/10 hover:border-purple-500/50 rounded-xl p-6 text-center cursor-pointer bg-white/[0.01] hover:bg-white/[0.02] transition-all group">
                        <input type="file" name="report_file" id="report_file" required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="updateFileName(this)">
                        <div class="space-y-1.5">
                            <svg class="w-8 h-8 text-purple-400 group-hover:scale-105 transition-transform mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="text-sm font-semibold text-white">Haz clic o arrastra el archivo aquí</div>
                            <p class="text-xs text-gray-500">Se compara contra el mes indicado en el propio reporte.</p>
                        </div>
                    </div>
                    <div id="file-name-display" class="mt-3 text-xs text-purple-300 font-semibold hidden flex items-center space-x-1.5 bg-purple-500/10 p-2 rounded-xl border border-purple-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="selected-file-name"></span>
                    </div>
                </div>
                <button type="submit"
                    class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-purple-600/20 shrink-0">
                    Comparar
                </button>
            </form>
        </div>

        @isset($comparison)
            <!-- Result Header -->
            <div class="glass-card rounded-2xl p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">Resultados — {{ $comparison['month_label'] }}</h2>
                        <p class="text-gray-400 text-xs mt-1">
                            {{ $comparison['excel_count'] }} filas en el Excel vs {{ $comparison['db_count'] }} registros en el sistema · {{ $comparison['same_count'] }} idénticos
                        </p>
                        @unless ($comparison['has_data'])
                            <p class="text-amber-400 text-xs mt-2 font-semibold">Este mes aún no tiene datos importados — todo el archivo sería nuevo.</p>
                        @endunless
                    </div>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <span class="px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 font-semibold">{{ count($comparison['new']) }} nuevo(s)</span>
                        <span class="px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-300 font-semibold">{{ count($comparison['changed']) }} con diferencias</span>
                        <span class="px-3 py-1.5 rounded-full bg-red-500/10 border border-red-500/20 text-red-300 font-semibold">{{ count($comparison['missing']) }} solo en sistema</span>
                    </div>
                </div>

                <!-- Totals comparison -->
                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="rounded-xl bg-white/[0.02] border border-white/5 p-4">
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold block mb-1">Excel</span>
                        <span class="text-lg font-extrabold text-white block">{{ number_format($comparison['excel_units'], 0, ',', '.') }} unidades</span>
                        <span class="text-sm text-gray-300 block">Bs {{ number_format($comparison['excel_sales'], 2, ',', '.') }}</span>
                    </div>
                    <div class="rounded-xl bg-white/[0.02] border border-white/5 p-4">
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold block mb-1">Sistema</span>
                        <span class="text-lg font-extrabold text-white block">{{ number_format($comparison['db_units'], 0, ',', '.') }} unidades</span>
                        <span class="text-sm text-gray-300 block">Bs {{ number_format($comparison['db_sales'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- New in Excel -->
            @if (count($comparison['new']) > 0)
                <div class="glass-card rounded-2xl p-6 mb-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 mb-4">Nuevos en el Excel (se agregarían)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-bold uppercase tracking-wider text-gray-400 border-b border-white/5">
                                    <th class="px-4 py-2.5">Cliente</th>
                                    <th class="px-4 py-2.5">Producto</th>
                                    <th class="px-4 py-2.5 text-right">Cantidad</th>
                                    <th class="px-4 py-2.5 text-right">Ventas (Bs)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-xs text-gray-300">
                                @foreach ($comparison['new'] as $row)
                                    <tr class="hover:bg-white/[0.02]">
                                        <td class="px-4 py-2.5 text-white">{{ $row['client_name'] }} <span class="text-gray-500 font-mono text-[10px]">({{ $row['client_code'] }})</span></td>
                                        <td class="px-4 py-2.5">
                                            {{ $row['product_description'] ?: $row['product_code'] }}
                                            @if ($row['is_discount'])
                                                <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-500/10 text-red-300 border border-red-500/20">DESCUENTO</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right">{{ number_format($row['quantity'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-2.5 text-right font-semibold text-white">{{ number_format($row['total_sales'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Changed rows -->
            @if (count($comparison['changed']) > 0)
                <div class="glass-card rounded-2xl p-6 mb-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 mb-4">Con diferencias (sistema → excel)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-bold uppercase tracking-wider text-gray-400 border-b border-white/5">
                                    <th class="px-4 py-2.5">Cliente</th>
                                    <th class="px-4 py-2.5">Producto</th>
                                    <th class="px-4 py-2.5 text-right">Cantidad</th>
                                    <th class="px-4 py-2.5 text-right">Ventas (Bs)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-xs text-gray-300">
                                @foreach ($comparison['changed'] as $row)
                                    <tr class="hover:bg-white/[0.02]">
                                        <td class="px-4 py-2.5 text-white">{{ $row['client_name'] }} <span class="text-gray-500 font-mono text-[10px]">({{ $row['client_code'] }})</span></td>
                                        <td class="px-4 py-2.5">
                                            {{ $row['product_description'] ?: $row['product_code'] }}
                                            @if ($row['is_manual'])
                                                <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/20">MANUAL</span>
                                            @endif
                                            @if ($row['is_discount'])
                                                <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-500/10 text-red-300 border border-red-500/20">DESCUENTO</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            {{ number_format($row['old_qty'], 0, ',', '.') }} →
                                            <span class="text-amber-300 font-semibold">{{ number_format($row['new_qty'], 0, ',', '.') }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            {{ number_format($row['old_sales'], 2, ',', '.') }} →
                                            <span class="text-amber-300 font-semibold">{{ number_format($row['new_sales'], 2, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Only in system -->
            @if (count($comparison['missing']) > 0)
                <div class="glass-card rounded-2xl p-6 mb-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-red-400 mb-4">Solo en el sistema (se eliminarían al importar)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-bold uppercase tracking-wider text-gray-400 border-b border-white/5">
                                    <th class="px-4 py-2.5">Cliente</th>
                                    <th class="px-4 py-2.5">Producto</th>
                                    <th class="px-4 py-2.5 text-right">Cantidad</th>
                                    <th class="px-4 py-2.5 text-right">Ventas (Bs)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-xs text-gray-300">
                                @foreach ($comparison['missing'] as $row)
                                    <tr class="hover:bg-white/[0.02]">
                                        <td class="px-4 py-2.5 text-white">{{ $row['client_name'] }} <span class="text-gray-500 font-mono text-[10px]">({{ $row['client_code'] }})</span></td>
                                        <td class="px-4 py-2.5">
                                            {{ $row['product_description'] ?: $row['product_code'] }}
                                            @if ($row['is_manual'])
                                                <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/20">MANUAL</span>
                                            @endif
                                            @if ($row['is_discount'])
                                                <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-500/10 text-red-300 border border-red-500/20">DESCUENTO</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right">{{ number_format($row['quantity'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-2.5 text-right font-semibold text-white">{{ number_format($row['total_sales'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if (count($comparison['new']) === 0 && count($comparison['changed']) === 0 && count($comparison['missing']) === 0)
                <div class="glass-card rounded-2xl p-8 text-center">
                    <svg class="w-14 h-14 text-emerald-400/80 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-xl font-bold text-white">Sin diferencias</h3>
                    <p class="text-gray-400 text-sm mt-2">El archivo coincide exactamente con los datos guardados de {{ $comparison['month_label'] }}.</p>
                </div>
            @endif
        @endisset
    </main>

    <!-- Footer -->
    <footer class="glass-card mt-auto border-t border-white/5 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-gray-500">
            &copy; 2026 SNC Pharma. Todos los derechos reservados. | Panel de Estadísticas Comerciales
        </div>
    </footer>

    <script>
        function updateFileName(input) {
            const display = document.getElementById('file-name-display');
            const nameSpan = document.getElementById('selected-file-name');
            if (input.files && input.files.length > 0) {
                nameSpan.textContent = input.files[0].name;
                display.classList.remove('hidden');
            } else {
                display.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
