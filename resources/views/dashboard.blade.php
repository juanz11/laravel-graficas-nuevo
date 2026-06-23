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
                        <span class="text-[10px] text-purple-400 font-semibold block uppercase tracking-wider -mt-1">Comercial e Inventario</span>
                    </div>
                </div>

                <!-- User Info & Action -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:flex items-center space-x-2 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></div>
                        <span class="text-xs text-gray-300 font-medium">Administrador: <strong class="text-white">admin</strong></span>
                    </div>
                    
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                        class="text-xs font-semibold px-4 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-200 hover:text-white rounded-xl transition-all flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Estadísticas de Venta</h1>
            <p class="text-gray-400 text-sm mt-1">Monitoreo comercial consolidado de productos farmacéuticos y facturación.</p>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Card 1 -->
            <div class="glass-card rounded-2xl p-6 glow-indigo">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Ventas Totales (Mes)</span>
                    <span class="p-2 rounded-lg bg-green-500/10 text-green-400 text-xs font-bold">+18.2%</span>
                </div>
                <h3 class="text-2xl font-extrabold text-white tracking-tight">$482,910 USD</h3>
                <p class="text-xs text-gray-500 mt-2">Comparado con $408,550 el mes pasado</p>
            </div>
            
            <!-- Card 2 -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Pedidos Despachados</span>
                    <span class="p-2 rounded-lg bg-blue-500/10 text-blue-400 text-xs font-bold">1,824 u.</span>
                </div>
                <h3 class="text-2xl font-extrabold text-white tracking-tight">96.8% Eficacia</h3>
                <p class="text-xs text-gray-500 mt-2">Promedio de entrega: 24 horas</p>
            </div>

            <!-- Card 3 -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Rotación de Inventario</span>
                    <span class="p-2 rounded-lg bg-yellow-500/10 text-yellow-400 text-xs font-bold">Óptima</span>
                </div>
                <h3 class="text-2xl font-extrabold text-white tracking-tight">4.2 Semanas</h3>
                <p class="text-xs text-gray-500 mt-2">Días de stock en almacén central</p>
            </div>

            <!-- Card 4 -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Clientes Activos</span>
                    <span class="p-2 rounded-lg bg-purple-500/10 text-purple-350 text-xs font-bold">+4.5%</span>
                </div>
                <h3 class="text-2xl font-extrabold text-white tracking-tight">342 Farmacias</h3>
                <p class="text-xs text-gray-500 mt-2">98% con recompras recurrentes</p>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Chart (Main Sales Trend - 2 cols) -->
            <div class="glass-card rounded-2xl p-6 lg:col-span-2">
                <h3 class="text-base font-bold text-white mb-4">Facturación Mensual (Último Semestre)</h3>
                <div class="h-80 w-full relative">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Right Chart (Sales by Category - 1 col) -->
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-base font-bold text-white mb-4">Distribución por Línea de Producto</h3>
                <div class="h-80 w-full relative flex items-center justify-center">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Table Grid -->
        <div class="glass-card rounded-2xl overflow-hidden mb-8">
            <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Top 5 Medicamentos más Vendidos (Este Mes)</h3>
                <span class="px-3 py-1 bg-white/5 rounded-full text-xs text-gray-400 font-medium">Actualizado hace 5m</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 bg-white/[0.01] text-xs font-bold uppercase tracking-wider text-purple-400">
                            <th class="px-6 py-4">Medicamento / Presentación</th>
                            <th class="px-6 py-4">Categoría</th>
                            <th class="px-6 py-4 text-right">Unidades Vendidas</th>
                            <th class="px-6 py-4 text-right">Ingresos generados</th>
                            <th class="px-6 py-4 text-center">Disponibilidad Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-gray-300">
                        <tr class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-4 font-semibold text-white">Amoxicilina + Ácido Clavulánico 875/125mg</td>
                            <td class="px-6 py-4">Antibióticos</td>
                            <td class="px-6 py-4 text-right">15,400 u.</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-450">$123,200.00</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-500/10 text-green-400">Suficiente</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-4 font-semibold text-white">Ibuprofeno 600mg (Caja x 30 caps)</td>
                            <td class="px-6 py-4">Analgésicos / Antiinflamatorios</td>
                            <td class="px-6 py-4 text-right">24,150 u.</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-450">$72,450.00</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-500/10 text-green-400">Suficiente</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-4 font-semibold text-white">Atorvastatina 20mg (Caja x 30 tab)</td>
                            <td class="px-6 py-4">Cardiovascular</td>
                            <td class="px-6 py-4 text-right">9,800 u.</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-450">$98,000.00</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-yellow-500/10 text-yellow-400">Crítico</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-4 font-semibold text-white">Vitamina C 1g Efervescente (Tubo x 10)</td>
                            <td class="px-6 py-4">Multivitamínicos</td>
                            <td class="px-6 py-4 text-right">18,300 u.</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-450">$54,900.00</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-500/10 text-green-400">Suficiente</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/[0.01] transition-colors">
                            <td class="px-6 py-4 font-semibold text-white">Omeprazol 20mg (Caja x 28 caps)</td>
                            <td class="px-6 py-4">Gastrointestinal</td>
                            <td class="px-6 py-4 text-right">14,200 u.</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-450">$35,500.00</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-500/10 text-red-400">Agotado</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="glass-card mt-auto border-t border-white/5 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-gray-500">
            &copy; 2026 SNC Pharma. Todos los derechos reservados. | Sistema de Monitoreo de Ventas
        </div>
    </footer>

    <!-- Interactive Charts Initialization -->
    <script>
        // Chart.js Theme Customization
        Chart.defaults.color = '#9ca3af';
        Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';

        // 1. Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesGradient = salesCtx.createLinearGradient(0, 0, 0, 300);
        salesGradient.addColorStop(0, 'rgba(168, 85, 247, 0.4)');
        salesGradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Nov 2025', 'Dic 2025', 'Ene 2026', 'Feb 2026', 'Mar 2026', 'Abr 2026'],
                datasets: [{
                    label: 'Facturación Mensual ($)',
                    data: [310000, 350000, 420000, 390000, 440000, 482910],
                    borderColor: '#a855f7',
                    borderWidth: 3,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    fill: true,
                    backgroundColor: salesGradient,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + (value / 1000) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // 2. Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Antibióticos', 'Analgésicos', 'Cardiovascular', 'Multivitamínicos', 'Gastrointestinal'],
                datasets: [{
                    data: [32, 23, 20, 15, 10],
                    backgroundColor: [
                        '#6366f1',
                        '#a855f7',
                        '#ec4899',
                        '#f59e0b',
                        '#10b981'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
