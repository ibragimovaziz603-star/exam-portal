<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans text-gray-800">
<div class="max-w-3xl mx-auto p-4">
    <a href="{{ route('history', ['tab' => 'created']) }}" class="text-blue-600 font-bold"><i class="fas fa-arrow-left mr-2"></i>Yaratilgan testlar</a>
    <h1 class="text-3xl font-black my-6">{{ $exam->title }}</h1>

    @forelse($exam->studentResults as $r)
        @if($r->finished_at)
            <a href="{{ route('history.participant', [$exam->exam_code, $r->token]) }}"
               class="flex justify-between items-center bg-white rounded-2xl shadow p-5 mb-3 border-l-4 border-blue-500 hover:shadow-lg transition">
                <div>
                    <p class="text-lg font-bold">{{ $r->student_name }}</p>
                    <p class="text-sm text-gray-500">{{ $r->finished_at->format('d.m.Y H:i') }}
                        @if($r->warnings > 0) · <span class="text-red-500">{{ $r->warnings }} ogohlantirish</span> @endif</p>
                </div>
                <div class="text-2xl font-black {{ $r->score >= $r->total_questions / 2 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $r->score }}/{{ $r->total_questions }}
                </div>
            </a>
        @else
            <div class="flex justify-between items-center bg-gray-50 rounded-2xl p-5 mb-3 border-l-4 border-gray-300 text-gray-400">
                <p class="text-lg font-bold">{{ $r->student_name }}</p>
                <p class="text-sm">Yakunlamagan</p>
            </div>
        @endif
    @empty
        <div class="bg-white rounded-2xl p-10 text-center text-gray-400">Hali hech kim qatnashmagan.</div>
    @endforelse
</div>
</body>
</html>