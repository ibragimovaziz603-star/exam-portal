<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mening Tarixim</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans text-gray-800">
<div class="max-w-3xl mx-auto p-4">
    <a href="{{ route('home') }}" class="text-blue-600 font-bold"><i class="fas fa-arrow-left mr-2"></i>Bosh sahifa</a>
    <h1 class="text-3xl font-black my-6">Mening Tarixim</h1>

    <div class="flex gap-3 mb-6">
        <a href="{{ route('history', ['tab' => 'joined']) }}"
           class="px-5 py-3 rounded-xl font-bold {{ $tab === 'joined' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600' }}">
            <i class="fas fa-pen mr-2"></i>Qatnashgan testlarim
        </a>
        @if(auth()->user()->role == 'teacher')
        <a href="{{ route('history', ['tab' => 'created']) }}"
           class="px-5 py-3 rounded-xl font-bold {{ $tab === 'created' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600' }}">
            <i class="fas fa-chalkboard-teacher mr-2"></i>Yaratilgan testlar
        </a>
        @endif
    </div>

    @if($tab === 'created')
        @forelse($created as $e)
            <a href="{{ route('history.exam', $e->exam_code) }}"
               class="flex justify-between items-center bg-white rounded-2xl shadow p-5 mb-3 border-l-4 border-green-500 hover:shadow-lg transition">
                <div>
                    <p class="text-xl font-bold">{{ $e->title }}</p>
                    <p class="text-sm text-gray-500">{{ $e->created_at->format('d.m.Y H:i') }} · {{ $e->questions_count }} ta savol</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black text-blue-600">{{ $e->studentResults->count() }}</p>
                    <p class="text-xs text-gray-500">qatnashchi</p>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400">Hali test yaratmagansiz.</div>
        @endforelse
    @else
        @forelse($joined as $r)
            <a href="{{ route('history.show', $r->token) }}"
               class="flex justify-between items-center bg-white rounded-2xl shadow p-5 mb-3 border-l-4 border-blue-500 hover:shadow-lg transition">
                <div>
                    <p class="text-xl font-bold">{{ $r->exam->title ?? '—' }}</p>
                    <p class="text-sm text-gray-500">{{ $r->finished_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="text-2xl font-black {{ $r->score >= $r->total_questions / 2 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $r->score }}/{{ $r->total_questions }}
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400">Hali birorta test yechmagansiz.</div>
        @endforelse
    @endif
</div>
</body>
</html>