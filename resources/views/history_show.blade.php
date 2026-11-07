<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $result->exam->title ?? '' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 font-sans text-gray-800">
    @php
        $ans = $result->answers->keyBy('question_id');
        $qs = $result->assignedQuestions();
    @endphp
    <div class="max-w-3xl mx-auto p-4">
        <a href="{{ $back }}" class="text-blue-600 font-bold"><i class="fas fa-arrow-left mr-2"></i>Orqaga</a>

        <div class="bg-white rounded-2xl shadow p-5 my-5 border-b-4 border-blue-600 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black">{{ $result->exam->title ?? '' }}</h1>
                <p class="text-sm text-gray-500"><i class="fas fa-user-circle mr-1"></i>{{ $result->student_name }} ·
                    {{ optional($result->finished_at)->format('d.m.Y H:i') }}</p>
            </div>
            <div class="text-3xl font-black text-blue-600">{{ $result->score_text }}/{{ $result->total_questions }}
            </div>
        </div>

        @foreach($qs as $i => $q)
            @php $a = $ans->get($q->id); @endphp

            @if($q->type === 'masala')
                @php
                    $g = $a->grade ?? null;
                    if ($g === null && $a) {
                        if ($a->ai_verdict === 'togri')
                            $g = 5;
                        elseif ($a->ai_verdict === 'xato')
                            $g = 0;
                    }
                @endphp

                @if($g === null)
                    <div class="bg-white rounded-2xl shadow mb-4 p-5 border-l-4 border-yellow-400">
                        <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full">Masala</span>
                        <p class="mt-3 font-bold text-gray-700">{{ $i + 1 }}-masala</p>
                        <p class="text-sm text-yellow-700 mt-1"><i class="fas fa-hourglass-half mr-1"></i>Ustoz tekshirmoqda. Natija
                            keyinroq ko'rinadi.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl shadow mb-4 overflow-hidden">
                        <div class="p-5 border-b bg-gray-50 flex justify-between items-start gap-3">
                            <div>
                                <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full">Masala</span>
                                <p class="mt-3 text-lg font-bold whitespace-pre-line">{{ $i + 1 }}. {{ $q->question_text }}</p>
                            </div>
                            <div class="text-xl">@include('partials.grade-badge', ['g' => $g])</div>
                        </div>
                        <div class="p-5">
                            <p class="text-sm font-bold text-gray-500 mb-2">Yozgan kodingiz:</p>
                            <pre
                                class="bg-gray-900 text-green-200 rounded-xl p-4 text-sm overflow-x-auto"><code>{{ $a->code_answer ?? '' }}</code></pre>
                        </div>
                    </div>
                @endif
            @else
                @php $picked = $chosen[$q->id] ?? null; @endphp
                <div class="bg-white rounded-2xl shadow p-6 mb-4">
                    <div class="flex gap-4 mb-4">
                        <div
                            class="w-10 h-10 shrink-0 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center">
                            {{ $i + 1 }}</div>
                        <p class="text-lg font-bold pt-1">{{ $q->question_text }}</p>
                    </div>
                    <div class="space-y-3 ml-14">
                        @foreach($q->options as $k => $opt)
                            @php
                                $isPicked = $picked == $opt->id;
                                $isCorrect = (bool) $opt->is_correct;
                                $cls = $isCorrect ? 'border-green-500 bg-green-50 text-green-800'
                                    : ($isPicked ? 'border-red-500 bg-red-50 text-red-800' : 'border-gray-200 text-gray-600');
                            @endphp
                            <div class="flex items-center gap-4 border-2 rounded-xl p-4 {{ $cls }}">
                                <input type="radio" disabled {{ $isPicked ? 'checked' : '' }} class="w-5 h-5">
                                <span class="font-bold">{{ chr(65 + $k) }})</span>
                                <span class="text-lg flex-1">{{ $opt->option_text }}</span>
                                @if($isCorrect) <i class="fas fa-check-circle text-green-500"></i>
                                @elseif($isPicked) <i class="fas fa-times-circle text-red-500"></i> @endif
                            </div>
                        @endforeach
                    </div>
                    @if(!$picked)
                        <p class="ml-14 mt-3 text-sm text-gray-400 italic">Javob berilmagan</p>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</body>

</html>