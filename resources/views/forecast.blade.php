<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SNC Pharma - Predicción de Ventas</title>
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
        select option {
            background: #17132b;
            color: #f3f4f6;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Background glow -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-indigo-500/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] rounded-full bg-purple-500/5 blur-[120px] pointer-events-none"></div>

    @include('layouts.sidebar')

    <div id="main-wrapper" class="lg:pl-64 flex flex-col min-h-screen">
        @include('layouts.topbar', ['title' => 'Predicción de Ventas', 'subtitle' => 'Proyección con IA basada en el historial mensual'])

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 relative z-10">

        @if (!$hasData)
            <div class="glass-card rounded-3xl p-8 sm:p-16 text-center max-w-2xl mx-auto my-12 shadow-2xl relative overflow-hidden">
                <svg class="w-20 h-20 text-indigo-400/80 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Sin historial de ventas</h2>
                <p class="text-gray-400 text-sm mt-3 max-w-md mx-auto leading-relaxed">
                    No hay datos de ventas para proyectar con los filtros seleccionados. Importa reportes desde el Dashboard o ajusta los filtros.
                </p>
            </div>
        @else

        <!-- Filters -->
        <form method="GET" action="{{ route('forecast') }}" class="glass-card rounded-2xl p-5 mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Cliente</label>
                <select name="client" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-indigo-500/50">
                    <option value="">Todos los clientes</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->client_code }}" @selected($selectedClient === $client->client_code)>{{ $client->client_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Producto</label>
                <input type="text" name="product" value="{{ $selectedProduct }}" placeholder="Código o descripción"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-gray-200 placeholder-gray-600 focus:outline-none focus:border-indigo-500/50">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Métrica</label>
                <select name="view_type" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-indigo-500/50">
                    <option value="sales" @selected($viewType === 'sales')>Ventas (USD)</option>
                    <option value="units" @selected($viewType === 'units')>Unidades</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Horizonte</label>
                <select name="horizon" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-indigo-500/50">
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected($horizon === $i)>{{ $i }} {{ $i === 1 ? 'mes' : 'meses' }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all">
                    Proyectar
                </button>
            </div>
        </form>

        <!-- Predicted KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            @foreach ($predictions as $prediction)
                <div class="glass-card rounded-2xl p-5 glow-indigo">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-indigo-400">{{ $prediction['label'] }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 font-semibold">Proyección</span>
                    </div>
                    <p class="text-2xl font-extrabold text-white tracking-tight">$ {{ number_format($prediction['sales_usd'], 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ number_format($prediction['units']) }} unidades estimadas</p>
                </div>
            @endforeach
        </div>

        <!-- Forecast Chart -->
        <div class="glass-card rounded-2xl p-6 mb-8">
            <h3 class="text-sm font-bold text-white tracking-tight mb-1">Historial y proyección mensual</h3>
            <p class="text-[11px] text-gray-500 mb-4">La línea punteada corresponde a los meses proyectados.</p>
            <div class="h-80">
                <canvas id="forecastChart"></canvas>
            </div>
        </div>

        <!-- AI Analysis -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center space-x-3 mb-3">
                <div class="bg-indigo-500/15 border border-indigo-500/30 p-2 rounded-xl">
                    <svg class="w-5 h-5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Análisis</h3>
                    <p class="text-[11px] text-gray-500">
                        @if ($source === 'gemini')
                            Generado con {{ config('services.gemini.model', 'Gemini') }}
                        @elseif ($source === 'openai')
                            Generado con {{ config('services.openai.model', 'GPT') }}
                        @else
                            Estimación estadística local (tendencia lineal)
                        @endif
                    </p>
                </div>
            </div>
            <p class="text-sm text-gray-300 leading-relaxed">{{ $analysis }}</p>
        </div>

        @endif
    </main>
    </div>

    <script>
        @if ($hasData)
        const labels = @json($chartLabels);
        const values = @json($viewType === 'units' ? $chartUnits : $chartSales);
        const predicted = @json($chartPredictedFlags);
        const isUnits = {{ $viewType === 'units' ? 'true' : 'false' }};

        const firstPredicted = predicted.findIndex(p => p);
        const historyData = values.map((v, i) => (firstPredicted === -1 || i < firstPredicted) ? v : null);
        const forecastData = values.map((v, i) => i >= firstPredicted - 1 && firstPredicted !== -1 ? v : null);

        const fmt = v => isUnits
            ? new Intl.NumberFormat('es-VE').format(v)
            : '$ ' + (v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v.toFixed(0));

        new Chart(document.getElementById('forecastChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Historial',
                        data: historyData,
                        borderColor: '#818cf8',
                        backgroundColor: 'rgba(129, 140, 248, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#818cf8',
                    },
                    {
                        label: 'Proyección',
                        data: forecastData,
                        borderColor: '#c084fc',
                        backgroundColor: 'rgba(192, 132, 252, 0.10)',
                        borderDash: [6, 6],
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointStyle: 'rectRot',
                        pointBackgroundColor: '#c084fc',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: '#9ca3af', font: { size: 11 } } },
                    tooltip: {
                        callbacks: { label: ctx => ` ${ctx.dataset.label}: ${fmt(ctx.parsed.y)}` }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255,255,255,0.03)' },
                        ticks: { color: '#6b7280', font: { size: 10 }, maxRotation: 45 }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: isUnits ? 'Unidades' : 'Ventas (USD)', color: '#6b7280', font: { size: 12 } },
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: '#9ca3af', callback: fmt }
                    }
                }
            }
        });
        @endif
    </script>
</body>
</html>
