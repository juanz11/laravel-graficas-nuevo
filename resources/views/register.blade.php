<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registro</title>
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full p-6">
            <h1 class="text-2xl font-bold mb-6">Registro</h1>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2">Nombre</label>
                    <input type="text" name="name" class="w-full p-2 border rounded dark:bg-[#161615] dark:border-[#3E3E3A]" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded dark:bg-[#161615] dark:border-[#3E3E3A]" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Contraseña</label>
                    <input type="password" name="password" class="w-full p-2 border rounded dark:bg-[#161615] dark:border-[#3E3E3A]" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="w-full p-2 border rounded dark:bg-[#161615] dark:border-[#3E3E3A]" required>
                </div>
                <button type="submit" class="w-full bg-[#1b1b18] text-white p-2 rounded">Registrarse</button>
            </form>
            <p class="mt-4 text-center">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-[#f53003]">Inicia Sesión</a>
            </p>
        </div>
    </body>
</html>
