<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estadísticas de Venta - Iniciar Sesión</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN for instant rendering & utility support -->
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
            background: radial-gradient(circle at 50% 50%, #110e25 0%, #070510 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-button {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            box-shadow: 0 4px 20px -2px rgba(168, 85, 247, 0.4);
            transition: all 0.3s ease;
        }
        .glow-button:hover {
            box-shadow: 0 4px 25px 2px rgba(168, 85, 247, 0.6);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 overflow-hidden">
    <!-- Background abstract glowing blobs -->
    <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-indigo-600/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] rounded-full bg-purple-600/10 blur-[120px] pointer-events-none"></div>

    <div class="relative w-full max-w-5xl h-[650px] glass rounded-3xl overflow-hidden shadow-2xl flex flex-col md:flex-row">
        
        <!-- Left Side: Login Form -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-between h-full bg-black/20">
            <!-- Header/Logo Area -->
            <div class="flex items-center space-x-3">
                <div class="bg-white/10 p-2 rounded-xl border border-white/10">
                    <img src="{{ asset('logo.png') }}" class="h-10 w-auto object-contain" alt="SNC Pharma Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <span class="hidden text-white font-bold text-lg tracking-wider">SNC</span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-base tracking-tight">SNC Pharma</h2>
                    <p class="text-xs text-purple-400/80 font-medium">Estadísticas de Venta</p>
                </div>
            </div>

            <!-- Form Area -->
            <div class="my-auto py-6">
                <h1 class="text-3xl font-extrabold text-white tracking-tight mb-2">¡Bienvenido!</h1>
                <p class="text-gray-400 text-sm mb-8">Ingresa tus credenciales para acceder al panel de estadísticas.</p>

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-200 text-sm flex items-start space-x-2">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Usuario o Correo</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" 
                                class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500 transition-all text-sm"
                                placeholder="Escribe 'admin'" required autofocus>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">Contraseña</label>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" 
                                class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500 transition-all text-sm"
                                placeholder="Escribe 'admin'" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center text-sm text-gray-400 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500/40 mr-2 h-4 w-4">
                            Recordar sesión
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-xl text-white font-semibold text-sm glow-button">
                        Ingresar a la Plataforma
                    </button>
                </form>
            </div>

            <!-- Footer Area -->
            <div class="text-xs text-gray-500 text-center md:text-left">
                &copy; 2026 SNC Pharma. Todos los derechos reservados.
            </div>
        </div>

        <!-- Right Side: Graphic Decoration / Marketing -->
        <div class="hidden md:flex w-1/2 bg-gradient-to-tr from-indigo-950 via-purple-950 to-indigo-900 p-12 flex-col justify-between relative overflow-hidden h-full">
            <!-- Decorative gradient shapes -->
            <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-purple-500/20 blur-[80px]"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-indigo-500/20 blur-[60px]"></div>

            <div class="relative z-10">
                <span class="px-3 py-1 bg-white/10 border border-white/20 text-purple-300 font-semibold text-xs rounded-full uppercase tracking-wider">
                    Panel Comercial
                </span>
                <h3 class="text-white text-3xl font-extrabold tracking-tight mt-6 leading-tight">
                    Optimiza tus decisiones con datos en tiempo real
                </h3>
                <p class="text-purple-200/70 text-sm mt-3 leading-relaxed">
                    Monitorea inventario, proyecciones de facturación y rendimiento de ventas por zonas en un solo lugar.
                </p>
            </div>

            <!-- CSS Visual Graph Mockup -->
            <div class="relative z-10 my-auto py-6 flex items-end justify-between space-x-3 h-48 max-w-sm mx-auto w-full">
                <!-- Bar 1 -->
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-lg h-24 relative group transition-all duration-500 hover:scale-105">
                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-purple-900 border border-purple-500/30 text-[10px] text-white py-0.5 px-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">$4.2M</span>
                    </div>
                    <span class="text-[10px] text-purple-300/60 mt-2">Ene</span>
                </div>
                <!-- Bar 2 -->
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-lg h-36 relative group transition-all duration-500 hover:scale-105">
                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-purple-900 border border-purple-500/30 text-[10px] text-white py-0.5 px-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">$6.8M</span>
                    </div>
                    <span class="text-[10px] text-purple-300/60 mt-2">Feb</span>
                </div>
                <!-- Bar 3 -->
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-lg h-28 relative group transition-all duration-500 hover:scale-105 flex justify-center">
                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-purple-900 border border-purple-500/30 text-[10px] text-white py-0.5 px-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">$5.1M</span>
                    </div>
                    <span class="text-[10px] text-purple-300/60 mt-2">Mar</span>
                </div>
                <!-- Bar 4 (Active/Highlight) -->
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-gradient-to-t from-indigo-400 to-pink-500 rounded-t-lg h-44 relative group transition-all duration-500 hover:scale-110 shadow-lg shadow-pink-500/20">
                        <div class="absolute -top-1 right-1/2 translate-x-1/2 w-1.5 h-1.5 bg-white rounded-full animate-ping"></div>
                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-pink-900 border border-pink-500/30 text-[10px] text-white py-0.5 px-1.5 rounded opacity-100 transition-opacity font-semibold shadow-md">$9.4M</span>
                    </div>
                    <span class="text-[10px] text-white font-medium mt-2">Abr</span>
                </div>
            </div>

            <!-- Quote/Feature -->
            <div class="relative z-10 flex items-center space-x-3 text-xs text-purple-300/80 bg-white/5 border border-white/10 p-3 rounded-2xl">
                <svg class="w-5 h-5 text-purple-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Acceso seguro encriptado por token y control estricto de roles.</span>
            </div>
        </div>
    </div>
</body>
</html>
