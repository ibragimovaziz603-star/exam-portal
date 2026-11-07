<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <!-- Har 3 sekundda sahifani avtomatik yangilaydi -->
    <meta http-equiv="refresh" content="3"> 
    <title>Kutilmoqda...</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col items-center justify-center font-sans">
    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500 mb-6"></div>
    <h1 class="text-3xl font-bold mb-2">Kuting...</h1>
    <p class="text-gray-400">Ustoz testni boshlashini kuting. Sahifa avtomatik yangilanadi.</p>
    <div class="mt-8 bg-gray-800 p-4 rounded border border-gray-700">
        <p>Ism: <span class="font-bold text-white">{{ $student->student_name }}</span></p>
        <p>Test: <span class="text-blue-400">{{ $student->exam->title }}</span></p>
    </div>
</body>
</html>