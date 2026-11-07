<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testga Kirish</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<!-- body da min-h-screen va flex-col bo'lishi shart -->

<body class="bg-blue-50 flex flex-col min-h-screen font-sans">

    <!-- h-screen va w-full orqali ekran bo'yicha to'liq cho'ziladi. 
         overflow-hidden qo'shildi, shunda ichidagi narsalar tashqariga chiqmaydi -->
    <div class="h-screen w-full flex-shrink-0 flex items-center justify-center p-4 relative overflow-hidden">

        <!-- Forma joylashgan oq karta -->
        <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-md border-t-4 border-blue-500">
            <a href="{{ route('home') }}"
                class="text-blue-500 hover:underline text-sm mb-4 inline-block transition hover:-translate-x-1">
                <i class="fas fa-arrow-left mr-1"></i> Asosiy sahifaga qaytish
            </a>
            <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">🎓 O'quvchi Imtihoni</h1>
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('student.joinExam') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Ustoz bergan 8 xonali kod:</label>
                    <input type="text" name="exam_code" placeholder="12345678"
                        class="w-full border-2 border-gray-300 p-3 rounded-lg text-center text-xl font-mono tracking-widest focus:border-blue-500 focus:outline-none transition"
                        maxlength="8" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Ismingiz:</label>
                    <input type="text" name="name" placeholder="Ali"
                        class="w-full border border-gray-300 p-3 rounded-lg focus:border-blue-500 focus:outline-none transition"
                        required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-1">Familiyangiz:</label>
                    <input type="text" name="surname" placeholder="Valiyev"
                        class="w-full border border-gray-300 p-3 rounded-lg focus:border-blue-500 focus:outline-none transition"
                        required>
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-md transition transform hover:-translate-y-1">
                    Qo'shilish (Join)
                </button>
            </form>
        </div>
    </div>

    <!-- FOOTER: h-screen li divdan tashqarida, shuning uchun faqat pastga tushganda ko'rinadi -->
    @include('partials.footer')

</body>

</html>