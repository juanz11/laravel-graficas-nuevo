<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full p-6">
            <h1 class="text-2xl font-bold mb-6">Iniciar Sesión</h1>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded dark:bg-[#161615] dark:border-[#3E3E3A]" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Contraseña</label>
                    <input type="password" name="password" class="w-full p-2 border rounded dark:bg-[#161615] dark:border-[#3E3E3A]" required>
                </div>
                <button type="submit" class="w-full bg-[#1b1b18] text-white p-2 rounded">Ingresar</button>
            </form>
            <p class="mt-4 text-center">
                ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-[#f53003]">Regístrate</a>
            </p>
        </div>
    </body>
</html>
