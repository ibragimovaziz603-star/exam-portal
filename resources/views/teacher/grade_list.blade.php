<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekshirish</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans text-gray-800">
<div class="max-w-3xl mx-auto p-4">
    <a href="{{ route('teacher.manageExam', $exam->exam_code) }}" class="text-blue-600 font-bold"><i class="fas fa-arrow-left mr-2"></i>Testga qaytish</a>

    <div class="bg-white rounded-2xl shadow p-5 my-5 border-b-4 border-green-500 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black">{{ $student->student_name }}</h1>
            <p class="text-sm text-gray-500">{{ $exam->title }}</p>
        </div>
        <div class="text-3xl font-black text-blue-600">
            {{ $student->score_text }}/{{ $student->question_ids ? count($student->question_ids) : $student->total_questions }}
        </div>
    </div>

    @foreach($masalas as $i => $q)
        @php
            $a = $ans->get($q->id);
            // YANGILANDI: endi 'grade' (1,2,3,4,5,0) bo'yicha tekshiriladi
            $g = $a ? $a->grade : null;
            $cls = ($g !== null) ? 'border-green-500' : 'border-yellow-400';
        @endphp
        <a href="{{ route('teacher.gradeEdit', [$exam->exam_code, $student->token, $q->id]) }}"
           class="flex justify-between items-center bg-white rounded-2xl shadow p-5 mb-3 border-l-4 {{ $cls }} hover:shadow-lg transition">
            <div>
                <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full">Masala {{ $i + 1 }}</span>
                <p class="mt-2 text-lg font-bold">{{ $q->question_text }}</p>
            </div>
            <div class="text-right font-black text-lg">
                @if($g !== null)
                    {{-- YANGILANDI: Baho chiroyli badge ko'rinishida chiqadi --}}
                    @include('partials.grade-badge', ['g' => $g])
                @else
                    <span class="text-yellow-600 text-sm"><i class="fas fa-clock mr-1"></i> Tekshirilmagan</span>
                @endif
            </div>
        </a>
    @endforeach
</div>
</body>
</html>