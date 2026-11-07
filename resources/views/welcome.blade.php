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

    <div class="lg:flex lg:min-h-screen">

        <!-- Qoraytirish (telefonda menyu ochilganda) -->
        <div id="ovl" onclick="menu(false)" class="hidden fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <!-- Menyu: telefonda yon panel, kompyuterda chap ustun -->
        <aside id="side"
            class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] -translate-x-full transition-transform duration-300 bg-white shadow-2xl flex flex-col justify-between p-6 overflow-y-auto lg:static lg:translate-x-0 lg:w-1/3 lg:max-w-none lg:p-10 lg:z-10 lg:min-h-screen">
            <div>
                <div class="flex items-center justify-between mb-6 lg:mb-10">
                    <h1 class="text-2xl lg:text-3xl font-black text-blue-600 tracking-tighter">EXAM PORTAL</h1>
                    <button type="button" onclick="menu(false)" class="lg:hidden text-gray-500 text-2xl px-2"><i
                            class="fas fa-times"></i></button>
                </div>
                <nav class="space-y-3">
                    <a href="{{ route('home') }}"
                        class="block px-4 py-3 bg-blue-50 text-blue-700 font-bold rounded-lg border-l-4 border-blue-600">Bosh
                        sahifa</a>
                    @auth
                        <a href="{{ route('history') }}"
                            class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">📊
                            Mening Tarixim</a>
                        @if(auth()->user()->role == 'teacher')
                            <a href="{{ route('teacher.dashboard') }}"
                                class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">📝
                                Test Yaratish (Ustoz)</a>
                        @endif
                        @if(auth()->user()->role == 'admin')
                            <a href="{{ route('admin.index') }}"
                                class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">🛠
                                Admin panel</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                            class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">🔑
                            Tizimga Kirish (Login)</a>
                        <a href="{{ route('register') }}"
                            class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-blue-600 font-medium rounded-lg transition">✍️
                            Ro'yxatdan O'tish (Sign Up)</a>
                    @endauth
                </nav>
            </div>

            <div class="flex flex-col gap-4 pt-6 mt-6 border-t">
                @auth
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff"
                            class="w-11 h-11 rounded-full shadow shrink-0" alt="">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            <p class="text-xs font-bold text-blue-500 uppercase">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2 px-4 rounded-lg border border-red-200 transition flex items-center justify-center gap-2">
                            <i class="fas fa-sign-out-alt"></i> Chiqish
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Mehmon&background=ccc&color=fff"
                            class="w-11 h-11 rounded-full shadow" alt="">
                        <div>
                            <p class="font-bold text-gray-800">Mehmon</p>
                            <p class="text-xs text-gray-500">Tizimga kirmagan</p>
                        </div>
                    </div>
                @endauth
            </div>
        </aside>

        <!-- O'ng qism -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Telefon paneli -->
            <header
                class="lg:hidden sticky top-0 z-30 bg-white shadow flex items-center justify-between gap-2 px-3 py-2">
                <button type="button" onclick="menu(true)"
                    class="bg-gray-100 rounded-xl w-11 h-11 text-gray-700 text-xl"><i class="fas fa-bars"></i></button>
                <span class="font-black text-blue-600 tracking-tighter text-lg">EXAM PORTAL</span>
                <div class="scale-90 origin-right">@include('partials.lang-switcher')</div>
            </header>

            <main
                class="relative flex-1 bg-blue-600 flex items-center justify-center overflow-hidden min-h-[70vh] lg:min-h-screen">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=2070&auto=format&fit=crop"
                    class="absolute inset-0 w-full h-full object-cover opacity-20" alt="">

                <div class="hidden lg:block absolute top-4 right-4 z-20 bg-white rounded-xl shadow-lg p-2">
                    @include('partials.lang-switcher')
                </div>

                <div class="relative z-10 text-center px-5 py-10 sm:px-10 w-full max-w-3xl">
                    <h2
                        class="text-[clamp(1.75rem,6vw,3.25rem)] font-extrabold text-white mb-4 leading-tight break-words">
                        Bilimingizni Sinab Ko'ring!</h2>
                    <p class="text-blue-100 text-[clamp(.9rem,2.6vw,1.125rem)] mb-8 max-w-lg mx-auto">Ustozlar test
                        yaratadi, o'quvchilar 8 xonali kod orqali kirib o'z bilimlarini sinovdan o'tkazadilar.</p>

                    <div
                        class="flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-3 sm:gap-4 max-w-xs sm:max-w-none mx-auto">
                        @auth
                            <a href="{{ route('student.join') }}"
                                class="bg-white text-blue-600 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-gray-100 transition">Testga
                                Qo'shilish (Join)</a>
                            @if(auth()->user()->role == 'teacher')
                                <a href="{{ route('teacher.dashboard') }}"
                                    class="bg-blue-700 border border-blue-400 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-blue-800 transition">Test
                                    Yaratish (Create)</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                                class="bg-white text-blue-600 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-gray-100 transition">Tizimga
                                Kirish</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="bg-blue-700 border border-blue-400 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-blue-800 transition">Ro'yxatdan
                                    O'tish</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('partials.footer')

    <script>
        function menu(open) {
            document.getElementById('side').classList.toggle('-translate-x-full', !open);
            document.getElementById('ovl').classList.toggle('hidden', !open);
        }
        window.addEventListener('resize', function () { if (innerWidth >= 1024) menu(false); });
    </script>
</body>

</html>