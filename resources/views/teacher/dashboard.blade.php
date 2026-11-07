<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustoz Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<!-- min-h-screen va flex-col qo'shildi -->

<body class="bg-gray-100 font-sans antialiased text-gray-800 min-h-screen flex flex-col">

    <!-- h-screen va flex-shrink-0 (Ekran to'liq egallanadi, footer pastga tushadi) -->
    <div class="h-screen w-full flex-shrink-0 flex flex-col overflow-y-auto">

        <!-- Tepa menyu (nav) -->
        <nav class="bg-white shadow-sm border-b border-gray-200 flex-shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <i class="fas fa-chalkboard-teacher text-blue-600 text-2xl"></i>
                    <h1 class="text-xl font-bold text-gray-900">Ustoz Paneli</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ auth()->user()->name ?? 'Ustoz' }}</span>
                    <a href="{{ route('home') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 transition">
                        <i class="fas fa-home"></i> Asosiy sahifa
                    </a>
                </div>
            </div>
        </nav>

        <!-- Asosiy kontent (flex-1 orqali qolgan bo'sh joyni egallaydi) -->
        <div class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6 shadow-sm flex items-center gap-3"
                    role="alert">
                    <i class="fas fa-check-circle text-xl"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6 shadow-sm">
                    @foreach($errors->all() as $error)
                        <p><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Yangi test yaratish formasi -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-500 text-lg"></i>
                    <h2 class="text-lg font-bold text-gray-800">Yangi Test Yaratish</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('teacher.createExam') }}" method="POST"
                        class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        @csrf
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Test nomi</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                placeholder="Masalan: Matematika 1-chorak"
                                class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:border-blue-500 focus:outline-none transition"
                                required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Turi</label>
                            <select name="type"
                                class="w-full border-gray-300 rounded-lg shadow-sm p-3 border bg-white focus:border-blue-500 focus:outline-none transition">
                                <option value="test">Test</option>
                                <option value="masala">Masala</option>
                                <option value="mixed">Aralash (test + masala)</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vaqt (minut)</label>
                            <input type="number" name="duration_minutes" min="1" max="120"
                                value="{{ old('duration_minutes', 30) }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm p-3 border focus:border-blue-500 focus:outline-none transition"
                                required>
                        </div>
                        <div class="md:col-span-3 flex items-end">
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow transition transform hover:-translate-y-1">
                                <i class="fas fa-plus"></i> Yaratish
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Testlar ro'yxati -->
            <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-list text-gray-500"></i>
                <h2 class="text-lg font-bold text-gray-800">Mening Testlarim</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($exams as $exam)
                    <div
                        class="bg-white rounded-xl shadow-md hover:shadow-lg transition duration-300 border border-gray-100 flex flex-col justify-between overflow-hidden {{ $exam->is_started ? 'opacity-75 bg-gray-50' : '' }}">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-gray-900 truncate pr-2">{{ $exam->title }}</h3>
                                <span
                                    class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-blue-200">TEST</span>
                            </div>

                            <div
                                class="bg-gray-50 rounded-lg p-3 mb-4 border border-gray-200 flex justify-between items-center">
                                <span class="text-sm text-gray-600 font-medium">Kirish kodi:</span>
                                <span
                                    class="font-mono text-red-600 font-bold text-lg tracking-widest bg-white px-3 py-1 rounded border">{{ $exam->exam_code }}</span>
                            </div>

                            <div class="text-sm text-gray-500 flex items-center gap-2 mb-2">
                                <i class="fas fa-question-circle"></i> Savollar: {{ $exam->questions->count() }} ta
                            </div>
                            <div class="text-sm text-gray-500 flex items-center gap-2 mb-2">
                                <i class="fas fa-clock"></i> Vaqti: {{ $exam->duration_minutes }} minut
                            </div>
                            <div class="text-sm text-gray-500 flex items-center gap-2 mb-2">
                                <i class="fas fa-users"></i> Qatnashchilar: {{ $exam->studentResults->count() }} ta
                            </div>
                        </div>

                        <div class="bg-gray-100 px-4 py-3 border-t border-gray-200 flex flex-wrap gap-2">
                            <a href="{{ route('teacher.manageExam', $exam->exam_code) }}"
                                class="flex-1 text-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 rounded-lg transition text-sm shadow-sm min-w-[100px]">
                                <i class="fas fa-eye mr-1"></i> Ko'rish
                            </a>

                            @if(!$exam->is_started)
                                <form action="{{ route('teacher.startExam', $exam->exam_code) }}" method="POST"
                                    class="flex-1 min-w-[100px]">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded-lg shadow-sm transition text-sm flex justify-center items-center gap-1">
                                        <i class="fas fa-play"></i> START
                                    </button>
                                </form>
                            @else
                                <div
                                    class="flex-1 text-center bg-red-100 text-red-700 font-bold py-2 rounded-lg border border-red-200 text-sm flex justify-center items-center gap-1 min-w-[100px]">
                                    <i class="fas fa-lock"></i> Tugatilgan
                                </div>
                            @endif

                            <form action="{{ route('teacher.destroyExam', $exam->exam_code) }}" method="POST"
                                class="flex-1 min-w-[100px]"
                                onsubmit="return confirm('Rostdan ham bu testni o\'chirmoqchimisiz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full bg-gray-800 hover:bg-black text-white font-bold py-2 rounded-lg shadow-sm transition text-sm flex justify-center items-center gap-1">
                                    <i class="fas fa-trash"></i> O'chirish
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-10 rounded-xl shadow-sm border border-gray-100 text-center">
                        <div class="text-gray-400 text-5xl mb-4"><i class="fas fa-folder-open"></i></div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Hozircha testlar yo'q</h3>
                        <p class="text-gray-500">Yuqoridagi forma orqali birinchi testingizni yarating.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- FOOTER QO'SHILDI -->
    @include('partials.footer')

</body>

</html>