<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Natija</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center font-sans">
    <div class="bg-white p-10 rounded-xl shadow-2xl max-w-md w-full text-center border-t-8 border-green-500">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Yakunlandi!</h1>
        <p class="text-gray-500 mb-6">{{ $student->student_name }}, javoblaringiz qabul qilindi.</p>
        
        <div class="bg-gray-50 p-6 rounded-lg mb-6 border">
            <p class="text-sm text-gray-500 uppercase tracking-wide">Sizning natijangiz</p>
            <p class="text-5xl font-black text-blue-600 my-2">{{ $student->score }} <span class="text-2xl text-gray-400">/ {{ $student->total_questions }}</span></p>
            <p class="text-sm text-gray-500">To'g'ri javoblar soni</p>
        </div>

        @if($student->exam->type == 'masala' || $student->exam->type == 'aralash')
        <div class="bg-yellow-50 text-yellow-700 p-3 rounded mb-6 text-sm">
            ⚠️ Sizning yozma javoblaringiz (masalalar) ustoz tomonidan tekshiriladi va baholanadi.
        </div>
        @endif

        <a href="{{ route('student.join') }}" class="text-blue-600 hover:underline font-medium">Asosiy sahifaga qaytish</a>
    </div>
</body>
</html>