<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-blue-600 to-blue-800 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <a href="{{ route('home') }}"
            class="inline-flex items-center gap-2 text-white font-bold mb-4 bg-white/15 hover:bg-white/25 rounded-full px-4 py-2 transition">
            <i class="fas fa-arrow-left"></i> Bosh sahifa
        </a>

        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-black text-blue-600 tracking-tighter mb-1">EXAM PORTAL</h1>
            <p class="text-gray-500 mb-6">Hisobingizga kiring</p>

            @if(session('status'))
                <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full border-2 border-gray-200 focus:border-blue-500 outline-none rounded-xl p-3 mb-1 text-base">
                @error('email')
                <p class="text-red-600 text-sm mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-bold text-gray-700 mb-1 mt-3">Parol</label>
                <!-- Parol maydoni (Universal skript o'zi ko'z qo'shadi) -->
                <input type="password" name="password" required autocomplete="current-password"
                    class="w-full border-2 border-gray-200 focus:border-blue-500 outline-none rounded-xl p-3 text-base">
                @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror

                <div class="flex items-center justify-between mt-4 mb-5 text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input type="checkbox" name="remember" checked class="w-4 h-4"> Eslab qolish
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Parolni
                            unutdingizmi?</a>
                    @endif
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-xl text-lg transition">Kirish</button>
            </form>

            @if(Route::has('register'))
                <p class="text-center text-gray-500 mt-5 text-sm">Hisobingiz yo'qmi?
                    <a href="{{ route('register') }}" class="text-blue-600 font-bold hover:underline">Ro'yxatdan o'tish</a>
                </p>
            @endif
        </div>
    </div>

    <!-- Universal parol ko'zi skripti -->
    @include('partials.password-eye')
</body>

</html>