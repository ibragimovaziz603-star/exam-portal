<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Portal - Asosiy Sahifa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 font-sans flex flex-col min-h-screen">

    <!-- Ekran balandligini to'ldiruvchi asosiy qism (Menyu va Rasm) -->
    <div class="h-screen w-full flex overflow-hidden relative flex-shrink-0">

        <!-- Til tanlash -->
        <div class="absolute top-4 right-4 z-50 bg-white rounded-xl shadow-lg p-2">
            @include('partials.lang-switcher')
        </div>

        <!-- Chap tomon: Menyu -->
        <div class="w-1/3 bg-white shadow-2xl flex flex-col justify-between p-10 z-10 h-full">
            <div>
                <h1 class="text-3xl font-black text-blue-600 mb-10 tracking-tighter">EXAM PORTAL</h1>

                <nav class="space-y-4">
                    <a href="{{ route('home') }}"
                        class="block px-4 py-3 bg-blue-50 text-blue-700 font-bold rounded-lg border-l-4 border-blue-600">
                        Bosh sahifa
                    </a>

                    @auth
                        <a href="{{ route('history') }}"
                            class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">
                            📊 Mening Tarixim
                        </a>

                        @if(auth()->user()->role == 'teacher')
                            <a href="{{ route('teacher.dashboard') }}"
                                class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">
                                📝 Test Yaratish (Ustoz)
                            </a>
                        @endif

                        <!-- YANGI QO'SHILDI: ADMIN PANEL -->
                        @if(auth()->user()->role == 'admin')
                            <a href="{{ route('admin.index') }}"
                                class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-red-600 font-medium rounded-lg transition border-l-4 border-transparent hover:border-red-500">
                                🛠 Admin panel
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                            class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">
                            🔑 Tizimga Kirish (Login)
                        </a>
                        <a href="{{ route('register') }}"
                            class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">
                            ✍️ Ro'yxatdan O'tish (Sign Up)
                        </a>
                    @endauth
                </nav>
            </div>

            <div class="flex flex-col gap-4 pt-6 border-t">
                @auth
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff"
                            alt="User" class="w-12 h-12 rounded-full shadow">
                        <div>
                            <p class="font-bold text-gray-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            <p class="text-xs font-bold text-blue-500 mt-1 uppercase">{{ auth()->user()->role }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2 px-4 rounded-lg border border-red-200 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            Chiqish
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name=Mehmon&background=ccc&color=fff" alt="Guest"
                            class="w-12 h-12 rounded-full shadow">
                        <div>
                            <p class="font-bold text-gray-800">Mehmon</p>
                            <p class="text-xs text-gray-500">Tizimga kirmagan</p>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        <!-- O'ng tomon: Rasm -->
        <div class="w-2/3 bg-blue-600 relative flex items-center justify-center h-full">
            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=2070&auto=format&fit=crop"
                class="absolute inset-0 w-full h-full object-cover opacity-20" alt="Background">

            <div class="relative z-10 text-center px-10">
                <h2 class="text-5xl font-extrabold text-white mb-4 leading-tight">Bilimingizni Sinab Ko'ring!</h2>
                <p class="text-blue-100 text-lg mb-8 max-w-lg mx-auto">Ustozlar test yaratadi, o'quvchilar 8 xonali kod
                    orqali kirib o'z bilimlarini sinovdan o'tkazadilar.</p>

                <div class="flex justify-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('student.join') }}"
                                class="bg-white text-blue-600 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-gray-100 transition transform hover:-translate-y-1">
                                Testga Qo'shilish (Join)
                            </a>

                            @if(auth()->user()->role == 'teacher')
                                <a href="{{ route('teacher.dashboard') }}"
                                    class="bg-blue-700 border border-blue-500 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-blue-800 transition transform hover:-translate-y-1">
                                    Test Yaratish (Create)
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                                class="bg-white text-blue-600 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-gray-100 transition transform hover:-translate-y-1">
                                Tizimga Kirish
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="bg-blue-700 border border-blue-500 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-blue-800 transition transform hover:-translate-y-1">
                                    Ro'yxatdan O'tish
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER QO'SHILDI -->
    @include('partials.footer')

</body>

</html>