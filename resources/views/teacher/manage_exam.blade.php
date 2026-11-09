<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testni Boshqarish</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans antialiased text-gray-800">

    <nav class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.dashboard') }}" class="text-gray-500 hover:text-blue-600 transition"><i
                        class="fas fa-arrow-left text-lg"></i></a>
                <h1 class="text-xl font-bold text-gray-900">Testni Boshqarish</h1>
            </div>
            <div class="flex items-center gap-4 bg-blue-50 px-4 py-2 rounded-lg border border-blue-100">
                <span class="text-sm text-gray-600 font-medium">Kirish kodi:</span>
                <span class="font-mono text-red-600 font-bold text-lg tracking-widest">{{ $exam->exam_code }}</span>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">

        @if(session('success'))
            <div
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 rounded mb-4 shadow-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- PDF YUKLASH TUGMASI -->
        <div class="mb-4">
            <label
                class="cursor-pointer inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition">
                <i class="fas fa-file-pdf mr-2"></i> PDF yuklash
                <input type="file" accept="application/pdf" class="hidden" onchange="pdfPicked(this)">
            </label>
        </div>

        @php
            $totT = $exam->questions->where('type', '!=', 'masala')->count();
            $totM = $exam->questions->where('type', 'masala')->count();
            $pickT = $exam->pick_tests ?? $totT;
            $pickM = $exam->pick_masalas ?? $totM;
        @endphp

        <!-- SAVOL SONINI TANLASH TUGMASI -->
        <div class="mb-4">
            <button type="button" onclick="pickOpen()"
                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition">
                <i class="fas fa-shuffle mr-2"></i> Savol soni: <span id="pick-label">{{ $pickT }}/{{ $pickM }}</span>
            </button>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="font-bold text-gray-600">Turi:</span>
            @foreach(['test' => 'Test', 'masala' => 'Masala', 'mixed' => 'Aralash'] as $k => $label)
                @php
                    $lose = $k === 'test' ? $totM : ($k === 'masala' ? $totT : 0);
                    $msg = $lose > 0
                        ? "Diqqat! {$lose} ta " . ($k === 'test' ? 'masala' : 'test savol') . " o'chib ketadi. Davom etasizmi?"
                        : "Test turi \"{$label}\" ga o'zgartirilsinmi?";
                    $isCur = ($exam->type ?? 'test') === $k;
                @endphp
                <form method="POST" action="{{ route('teacher.changeType', $exam->exam_code) }}"
                    onsubmit="return confirm({{ Js::from($msg) }})">
                    @csrf
                    <input type="hidden" name="type" value="{{ $k }}">
                    <button type="submit" {{ ($isCur || $exam->is_started) ? 'disabled' : '' }}
                        class="px-5 py-2 rounded-lg font-bold shadow {{ $isCur ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} {{ (!$isCur && $exam->is_started) ? 'opacity-50 cursor-not-allowed' : '' }}">
                        {{ $label }}
                    </button>
                </form>
            @endforeach
        </div>

        <!-- QATNASHGANLAR TUGMASI + MODAL -->
        <div class="mb-4">
            <input type="checkbox" id="modal-toggle" class="hidden peer">
            <label for="modal-toggle"
                class="cursor-pointer inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition">
                <i class="fas fa-users mr-2"></i> Qatnashganlar (<span
                    id="student-count">{{ $exam->studentResults->count() }}</span> ta)
            </label>

            <div
                class="fixed inset-0 bg-black bg-opacity-80 hidden peer-checked:flex items-center justify-center z-50 p-4">
                <div
                    class="bg-gray-900 rounded-2xl w-full max-w-6xl h-[90vh] p-6 relative border-4 border-gray-700 overflow-y-auto">
                    <label for="modal-toggle"
                        class="absolute top-4 right-4 text-gray-400 hover:text-white bg-gray-800 p-2 rounded-full cursor-pointer">
                        <i class="fas fa-times text-2xl"></i>
                    </label>
                    <h2 class="text-3xl font-black text-white mb-8 border-b border-gray-700 pb-4">
                        <i class="fas fa-users text-blue-500 mr-3"></i> Qatnashganlar
                    </h2>
                    <div id="students-grid" class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10"></div>
                </div>
            </div>
        </div>

        <!-- JONLI EKRAN -->
        <div class="bg-gray-900 p-8 rounded-2xl shadow-2xl mb-6 border-4 border-gray-700 text-center">
            <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-4">
                <h3 class="text-2xl font-black text-white"><i
                        class="fas fa-satellite-dish text-red-500 animate-pulse mr-2"></i> Jonli Ekran</h3>
                <span class="text-sm font-bold text-gray-400 uppercase">
                    {{ ['test' => 'Test', 'masala' => 'Masala', 'mixed' => 'Aralash'][$exam->type] ?? 'Test' }}
                </span>
            </div>

            @if(!$exam->is_started)
                <p class="text-gray-400 text-lg font-bold uppercase mb-2">Test Yechish Vaqti</p>
                <div
                    class="bg-green-500 text-white px-12 py-4 rounded-2xl font-black text-6xl inline-block shadow-[0_0_30px_rgba(34,197,94,0.8)]">
                    {{ $exam->duration_minutes }}:00
                </div>
            @else
                <p class="text-red-400 text-lg font-bold uppercase mb-2">Test davom etmoqda</p>
                <div
                    class="bg-red-600 text-white px-12 py-4 rounded-2xl font-black text-6xl inline-block shadow-[0_0_30px_rgba(220,38,38,0.8)]">
                    <i class="fas fa-clock mr-3"></i><span id="exam-timer">--:--</span>
                </div>
            @endif

            <div class="mt-8">
                @if(!$exam->is_started)
                    <form action="{{ route('teacher.startExam', $exam->exam_code) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white font-black py-4 px-20 rounded-full shadow-lg transition text-3xl">
                            <i class="fas fa-play-circle mr-2"></i> START
                        </button>
                    </form>
                @else
                    <form action="{{ route('teacher.finishExam', $exam->exam_code) }}" method="POST"
                        onsubmit="return confirm('Testni yakunlamoqchimisiz?');">
                        @csrf
                        <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white font-black py-3 px-10 rounded-full shadow-lg transition text-xl">
                            <i class="fas fa-stop-circle mr-2"></i> TESTNI TUGATISH
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- SAVOL QO'SHISH -->
        @php
            $type = $exam->type ?? 'test';
            $showTest = in_array($type, ['test', 'mixed']);
            $showMasala = in_array($type, ['masala', 'mixed']);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div class="lg:col-span-7">
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">

                    @if($type === 'mixed')
                        <div class="flex gap-2 mb-5">
                            <button type="button" id="btn-test" onclick="showForm('test')"
                                class="px-5 py-2 rounded-lg font-bold bg-blue-600 text-white">Test savol</button>
                            <button type="button" id="btn-masala" onclick="showForm('masala')"
                                class="px-5 py-2 rounded-lg font-bold bg-gray-200 text-gray-700">Masala</button>
                        </div>
                    @endif

                    @if($showTest)
                        <div id="form-test">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i
                                    class="fas fa-plus-circle text-blue-500 mr-2"></i>Yangi Test Savol</h3>
                            <form action="{{ route('teacher.addQuestion', $exam->exam_code) }}" method="POST">
                                @csrf
                                <input type="hidden" name="kind" value="test">
                                <div class="mb-4">
                                    <textarea name="question_text" class="w-full border-gray-300 rounded-lg p-4 border"
                                        rows="2" required placeholder="Savol matni..."></textarea>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    @for($i = 0; $i < 4; $i++)
                                        <div class="flex items-center gap-3 mb-2 bg-white p-2 rounded border">
                                            <input type="radio" name="correct_option" value="{{ $i }}" required>
                                            <span class="font-bold">{{ chr(65 + $i) }})</span>
                                            <input type="text" name="options[]" placeholder="Variant..."
                                                class="w-full border-none focus:ring-0" required>
                                        </div>
                                    @endfor
                                </div>
                                <button type="submit"
                                    class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg">Savolni Saqlash</button>
                            </form>
                        </div>
                    @endif

                    @if($showMasala)
                        <div id="form-masala" class="{{ $type === 'mixed' ? 'hidden' : '' }}">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i
                                    class="fas fa-code text-green-600 mr-2"></i>Yangi Masala</h3>
                            <form action="{{ route('teacher.addQuestion', $exam->exam_code) }}" method="POST">
                                @csrf
                                <input type="hidden" name="kind" value="masala">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Masala sharti:</label>
                                <textarea name="question_text" class="w-full border-gray-300 rounded-lg p-4 border mb-4"
                                    rows="4" required
                                    placeholder="Masalan: Sahifada qizil rangli 'Salom' yozuvini chiqaring..."></textarea>

                                <label class="block text-sm font-medium text-gray-700 mb-1">Namuna yechim (ixtiyoriy, AI
                                    tekshirganda yordam beradi):</label>
                                <textarea name="reference_solution"
                                    class="w-full border-gray-300 rounded-lg p-4 border mb-4 font-mono text-sm" rows="5"
                                    placeholder="<h1 style='color:red'>Salom</h1>"></textarea>

                                <button type="submit"
                                    class="w-full bg-green-600 text-white font-bold py-3 rounded-lg">Masalani
                                    Saqlash</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-md p-4 border border-gray-100">
                    <h3 class="text-md font-bold mb-2 border-b pb-2">Kiritilgan savollar</h3>
                    <div class="space-y-2 max-h-[500px] overflow-y-auto">
                        @foreach($exam->questions->sortBy(fn($q) => $q->type === 'masala' ? 1 : 0)->values() as $index => $q)
                            <div class="bg-gray-50 p-3 rounded-lg text-sm border-2 border-gray-400">
                                <div class="flex justify-between items-start gap-2">
                                    <p class="font-bold">
                                        {{ $index + 1 }}. {{ $q->question_text }}
                                        @if($q->type === 'masala') <span
                                            class="ml-1 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Masala</span>
                                        @endif
                                    </p>
                                    <form action="{{ route('teacher.destroyQuestion', [$exam->exam_code, $q->id]) }}"
                                        method="POST" onsubmit="return confirm('Bu savolni o\'chirasizmi?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 shrink-0"
                                            title="O'chirish"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                                @if($q->type !== 'masala')
                                    @foreach($q->options as $opt)
                                        <div class="{{ $opt->is_correct ? 'text-green-600 font-bold' : 'text-gray-600' }}">
                                            {{ $opt->option_text }}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CSRF = '{{ csrf_token() }}';

        function showForm(which) {
            document.getElementById('form-test').classList.toggle('hidden', which !== 'test');
            document.getElementById('form-masala').classList.toggle('hidden', which !== 'masala');
            document.getElementById('btn-test').className = 'px-5 py-2 rounded-lg font-bold ' + (which === 'test' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700');
            document.getElementById('btn-masala').className = 'px-5 py-2 rounded-lg font-bold ' + (which === 'masala' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700');
        }

        @if($exam->is_started)
            let left = {{ $exam->remainingSeconds() }};
            const timerEl = document.getElementById('exam-timer');
            function tick() {
                const m = Math.floor(left / 60), s = left % 60;
                timerEl.innerText = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                if (left > 0) left--;
            }
            tick();
            setInterval(tick, 1000);
        @endif

        const grid = document.getElementById('students-grid');
        const countEl = document.getElementById('student-count');

        function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

        function render(students) {
            countEl.innerText = students.length;
            if (students.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center text-gray-500 italic text-xl py-10">Hali hech kim qo\'shilmadi...</div>';
                return;
            }
            const base = "{{ url('/teacher/grade/' . $exam->exam_code) }}";
            grid.innerHTML = students.map(s => {
                const warn = s.warnings > 0;
                const canGrade = s.finished && s.masala;
                let border = 'border-gray-600';
                if (warn) border = 'border-red-500 shadow-[0_0_20px_rgba(239,68,68,0.9)]';
                else if (canGrade) border = 'border-green-500 shadow-[0_0_20px_rgba(34,197,94,0.7)]';

                return `
        <div data-id="${s.id}" class="student-card relative bg-gray-800 rounded-xl p-4 flex flex-col items-center border-2 transition-all cursor-pointer ${border}">
            ${warn ? `<div class="absolute -top-3 -right-3 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full">${Math.min(s.warnings, 3)}/3</div>` : ''}
            <button type="button" data-kick="${s.id}" title="Chiqarish"
    class="absolute top-2 left-2 w-7 h-7 rounded-full bg-gray-700 hover:bg-red-600 text-gray-300 hover:text-white text-sm">
    <i class="fas fa-user-slash"></i>
</button>
            <div class="w-16 h-16 rounded-full mb-3 flex items-center justify-center text-2xl font-black text-white ${warn ? 'bg-red-500' : 'bg-blue-500'}">
                ${esc(s.name.charAt(0).toUpperCase())}
            </div>
            <p class="text-white font-bold text-lg text-center w-full truncate">${esc(s.name)}</p>
            <div class="mt-2 font-bold ${warn ? 'text-red-300' : 'text-gray-400'}">${s.score}/${s.total}</div>
            ${s.finished ? '<div class="mt-1 text-xs text-green-400 font-bold">Yakunladi</div>' : ''}
            ${canGrade ? `<a href="${base}/${s.token}" class="mt-3 w-full text-center bg-green-500 hover:bg-green-600 text-white font-black py-2 rounded-lg text-sm">
                <i class="fas fa-check-double mr-1"></i>Tekshirish${s.pending > 0 ? ' (' + s.pending + ')' : ' ✓'}</a>` : ''}
        </div>`;
            }).join('');
        }

        function load() {
            fetch("{{ route('teacher.liveStatus', $exam->exam_code) }}", { headers: { 'Accept': 'application/json' } })
                .then(r => r.json()).then(d => render(d.students)).catch(() => { });
        }
        load();
        setInterval(load, 6000);

        grid.addEventListener('click', (e) => {
            if (e.target.closest('a')) return;

            const kick = e.target.closest('[data-kick]');
            if (kick) {
                e.stopPropagation();
                if (!confirm('Bu o\'quvchini testdan chiqarasizmi?')) return;
                fetch('/teacher/kick/' + kick.dataset.kick, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                }).then(load);
                return;
            }

            const card = e.target.closest('.student-card');
            if (!card) return;
            fetch(`/teacher/clear-warning/${card.dataset.id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            }).then(load);
        });
    </script>

    <!-- PDF OYNASI VA SKRIPTI -->
    <div id="pdf-modal" class="hidden fixed inset-0 z-[70] bg-gray-100 flex flex-col">
        <div class="flex items-center justify-between px-5 py-3 bg-gray-800 text-white">
            <span class="font-black text-lg"><i class="fas fa-file-pdf mr-2 text-red-400"></i>PDF dan olingan savollar
                (<span id="pdf-count">0</span>)</span>
            <button type="button" onclick="pdfClose()"
                class="bg-gray-600 hover:bg-gray-500 font-bold px-4 py-2 rounded-lg"><i
                    class="fas fa-times mr-1"></i>Yopish</button>
        </div>
        <div id="pdf-scroll" class="flex-1 overflow-y-auto p-5">
            <div id="pdf-list" class="max-w-3xl mx-auto"></div>
        </div>
        <div class="p-4 bg-white border-t text-center">
            <p class="text-sm text-gray-500 mb-2">Har bir test savolda to'g'ri javob doirasini bosing (yashil bo'ladi).
            </p>
            <button type="button" id="pdf-save" onclick="pdfSave()"
                class="bg-green-500 hover:bg-green-600 text-white font-black py-3 px-16 rounded-full text-xl shadow">
                <i class="fas fa-check mr-2"></i>Tamom
            </button>
        </div>
    </div>

    <!-- SAVOL SONINI TANLASH MODAL -->
    <div id="pick-modal"
        class="hidden fixed inset-0 z-[70] bg-black bg-opacity-70 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-xl p-6">
            <h3 class="text-xl font-black mb-1">Har o'quvchiga nechta savol tushsin?</h3>
            <p class="text-sm text-gray-500 mb-4">Savollar har o'quvchiga tasodifiy tanlanadi va aralash tartibda
                beriladi.</p>
            <div id="pick-body"></div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="pickClose()" class="flex-1 bg-gray-200 font-bold py-3 rounded-lg">Bekor
                    qilish</button>
                <button type="button" onclick="pickSave()"
                    class="flex-1 bg-green-500 text-white font-black py-3 rounded-lg">Saqlash</button>
            </div>
        </div>
    </div>

    <!-- SKRIPTLAR -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        var pdfItems = [];
        var IMPORT_URL = "{{ route('teacher.importQuestions', $exam->exam_code) }}";
        var IMPORT_CSRF = "{{ csrf_token() }}";

        function pesc(t) { var d = document.createElement('div'); d.textContent = (t == null ? '' : t); return d.innerHTML; }

        async function pdfPicked(input) {
            var file = input.files[0];
            input.value = '';
            if (!file) return;
            try {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                var pdf = await pdfjsLib.getDocument({ data: await file.arrayBuffer() }).promise;
                var lines = [];
                for (var p = 1; p <= pdf.numPages; p++) {
                    var content = await (await pdf.getPage(p)).getTextContent();
                    var rows = [];
                    content.items.forEach(function (it) {
                        if (!it.str || !it.str.trim()) return;
                        var y = it.transform[5], x = it.transform[4], row = null;
                        for (var i = 0; i < rows.length; i++) { if (Math.abs(rows[i].y - y) < 3) { row = rows[i]; break; } }
                        if (!row) { row = { y: y, items: [] }; rows.push(row); }
                        row.items.push({ x: x, s: it.str });
                    });
                    rows.sort(function (a, b) { return b.y - a.y; });
                    rows.forEach(function (r) {
                        r.items.sort(function (a, b) { return a.x - b.x; });
                        lines.push(r.items.map(function (i) { return i.s; }).join(' '));
                    });
                }
                pdfItems = parseQuestions(lines.join('\n'));
            } catch (e) {
                alert('PDF o\'qilmadi: ' + (e.message || e));
                return;
            }
            if (!pdfItems.length) {
                alert('PDF dan savol topilmadi. Savollar "1) ...", "2) ..." ko\'rinishida raqamlangan bo\'lishi va PDF matnli (rasm emas) bo\'lishi kerak.');
                return;
            }
            pdfRender();
            document.getElementById('pdf-modal').classList.remove('hidden');
        }

        function parseQuestions(text) {
            text = text.replace(/\u00a0/g, ' ');
            var re = /(?:^|\s)(\d{1,3})\s*[\)\.]\s+/g, m, expected = 1, starts = [];
            while ((m = re.exec(text)) !== null) {
                if (parseInt(m[1], 10) === expected) {
                    starts.push({ start: m.index, end: m.index + m[0].length });
                    expected++;
                }
            }
            var items = [];
            starts.forEach(function (st, i) {
                var block = text.slice(st.end, i + 1 < starts.length ? starts[i + 1].start : text.length).trim();
                items.push(parseBlock(block));
            });
            return items;
        }

        function parseBlock(b) {
            var letters = ['A', 'B', 'C', 'D', 'E'], pos = [], from = 0;
            for (var i = 0; i < letters.length; i++) {
                var r = new RegExp('(^|\\s)' + letters[i] + '\\s*\\)');
                var mm = r.exec(b.slice(from));
                if (!mm) break;
                var s = from + mm.index + mm[1].length;
                var e = from + mm.index + mm[0].length;
                pos.push({ s: s, e: e });
                from = e;
            }
            if (pos.length >= 2) {
                var stem = b.slice(0, pos[0].s).replace(/\s+/g, ' ').trim();
                var opts = pos.map(function (p, k) {
                    return b.slice(p.e, k + 1 < pos.length ? pos[k + 1].s : b.length).replace(/\s+/g, ' ').trim();
                });
                return { type: 'test', text: stem, options: opts, correct: autoCorrect(stem, opts) };
            }
            return { type: 'masala', text: b.replace(/[ \t]+/g, ' ').trim(), options: [], correct: -1 };
        }

        function autoCorrect(stem, opts) {
            if (!/^[\d\s+\-*\/xX×().,:]+$/.test(stem)) return -1;
            try {
                var expr = stem.replace(/[xX×]/g, '*').replace(/:/g, '/').replace(/,/g, '.');
                var val = Function('"use strict"; return (' + expr + ')')();
                for (var i = 0; i < opts.length; i++) {
                    if (parseFloat(opts[i].replace(',', '.')) === val) return i;
                }
            } catch (e) { }
            return -1;
        }

        function pdfRender() {
            var box = document.getElementById('pdf-scroll'), top = box.scrollTop;
            document.getElementById('pdf-count').innerText = pdfItems.length;
            var L = ['A', 'B', 'C', 'D', 'E'];
            document.getElementById('pdf-list').innerHTML = pdfItems.map(function (it, i) {
                var head = '<div class="flex justify-between items-center mb-3">'
                    + '<div class="flex items-center gap-2"><span class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center">' + (i + 1) + '</span>'
                    + (it.type === 'masala' ? '<span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full">Masala</span>' : '<span class="text-xs font-bold bg-blue-100 text-blue-700 px-3 py-1 rounded-full">Test</span>') + '</div>'
                    + '<button type="button" data-act="del" data-i="' + i + '" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button></div>';
                var body = '<textarea data-f="text" data-i="' + i + '" rows="' + (it.type === 'masala' ? 4 : 2) + '" class="w-full border-2 border-gray-300 rounded-lg p-3 font-bold mb-3">' + pesc(it.text) + '</textarea>';
                if (it.type === 'test') {
                    body += it.options.map(function (o, k) {
                        var ok = it.correct === k;
                        return '<div class="flex items-center gap-3 mb-2 border-2 rounded-xl p-2 ' + (ok ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-white') + '">'
                            + '<button type="button" data-act="ok" data-i="' + i + '" data-k="' + k + '" class="w-7 h-7 shrink-0 rounded-full border-2 flex items-center justify-center ' + (ok ? 'bg-green-500 border-green-500 text-white' : 'border-gray-400 bg-white') + '">' + (ok ? '<i class="fas fa-check text-xs"></i>' : '') + '</button>'
                            + '<span class="font-bold text-gray-500">' + L[k] + ')</span>'
                            + '<input data-f="opt" data-i="' + i + '" data-k="' + k + '" value="' + pesc(o).replace(/"/g, '&quot;') + '" class="flex-1 outline-none bg-transparent">'
                            + '</div>';
                    }).join('');
                }
                return '<div class="bg-white rounded-2xl shadow border-2 border-gray-400 p-5 mb-4">' + head + body + '</div>';
            }).join('');
            box.scrollTop = top;
        }

        document.getElementById('pdf-list').addEventListener('click', function (e) {
            var b = e.target.closest('[data-act]');
            if (!b) return;
            var i = parseInt(b.dataset.i, 10);
            if (b.dataset.act === 'del') { pdfItems.splice(i, 1); }
            else if (b.dataset.act === 'ok') { pdfItems[i].correct = parseInt(b.dataset.k, 10); }
            pdfRender();
        });
        document.getElementById('pdf-list').addEventListener('input', function (e) {
            var f = e.target.dataset.f;
            if (!f) return;
            var i = parseInt(e.target.dataset.i, 10);
            if (f === 'text') pdfItems[i].text = e.target.value;
            if (f === 'opt') pdfItems[i].options[parseInt(e.target.dataset.k, 10)] = e.target.value;
        });

        function pdfClose() {
            if (pdfItems.length && !confirm('Yopilsa, savollar saqlanmaydi. Yopilsinmi?')) return;
            document.getElementById('pdf-modal').classList.add('hidden');
            pdfItems = [];
        }

        function pdfSave() {
            if (!pdfItems.length) { alert('Savol yo\'q.'); return; }
            for (var i = 0; i < pdfItems.length; i++) {
                var it = pdfItems[i];
                if (!it.text.trim()) { alert((i + 1) + '-savol matni bo\'sh.'); return; }
                if (it.type === 'test' && it.correct < 0) { alert((i + 1) + '-savolda to\'g\'ri javob belgilanmagan.'); return; }
            }
            var btn = document.getElementById('pdf-save');
            btn.disabled = true; btn.innerText = 'Saqlanmoqda...';
            fetch(IMPORT_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': IMPORT_CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ items: pdfItems })
            }).then(function (r) {
                if (!r.ok) throw new Error('Server xatosi ' + r.status);
                return r.json();
            }).then(function () { location.reload(); })
                .catch(function (e) { alert('Saqlanmadi: ' + e.message); btn.disabled = false; btn.innerText = 'Tamom'; });
        }

        /* =========================================
           SAVOL SONINI TANLASH SKRIPTI (PICK)
           ========================================= */
        var PICK = {
            test: { total: {{ $totT }}, val: {{ $pickT }} },
            masala: { total: {{ $totM }}, val: {{ $pickM }} }
        };
        var PICK_URL = "{{ route('teacher.setPick', $exam->exam_code) }}";
        var PICK_CSRF = "{{ csrf_token() }}";

        function pickLabel() { document.getElementById('pick-label').innerText = PICK.test.val + '/' + PICK.masala.val; }

        function pickDraw() {
            var h = '';
            [['test', 'Test savollar'], ['masala', 'Masalalar']].forEach(function (k) {
                var p = PICK[k[0]];
                if (!p.total) return;
                var chips = [], n;
                if (p.total > 50) { for (n = 5; n <= p.total; n += 5) chips.push(n); }
                else { for (n = 1; n <= Math.min(9, p.total); n++) chips.push(n); }

                h += '<div class="mb-5"><div class="flex items-center justify-between mb-2">'
                    + '<span class="font-black">' + k[1] + ' <span class="text-gray-400 font-bold">(jami ' + p.total + ')</span></span>'
                    + '<div class="flex items-center gap-2">'
                    + '<button type="button" data-k="' + k[0] + '" data-d="-1" class="w-10 h-10 rounded-lg bg-gray-200 font-black text-xl">−</button>'
                    + '<span class="w-12 text-center text-2xl font-black">' + p.val + '</span>'
                    + '<button type="button" data-k="' + k[0] + '" data-d="1" class="w-10 h-10 rounded-lg bg-gray-200 font-black text-xl">+</button>'
                    + '</div></div><div class="flex flex-wrap gap-2">';
                chips.forEach(function (c) {
                    h += '<button type="button" data-k="' + k[0] + '" data-v="' + c + '" class="px-3 py-1 rounded-lg font-bold '
                        + (p.val === c ? 'bg-purple-600 text-white' : 'bg-gray-100') + '">' + c + '</button>';
                });
                h += '<button type="button" data-k="' + k[0] + '" data-v="' + p.total + '" class="px-3 py-1 rounded-lg font-bold '
                    + (p.val === p.total ? 'bg-purple-600 text-white' : 'bg-gray-100') + '">Hammasi</button></div></div>';
            });
            if (!h) h = '<p class="text-gray-500">Avval savollar qo\'shing.</p>';
            document.getElementById('pick-body').innerHTML = h;
        }

        document.getElementById('pick-body').addEventListener('click', function (e) {
            var b = e.target.closest('button[data-k]');
            if (!b) return;
            var p = PICK[b.dataset.k];
            if (b.dataset.d) p.val = Math.min(p.total, Math.max(1, p.val + parseInt(b.dataset.d, 10)));
            if (b.dataset.v) p.val = parseInt(b.dataset.v, 10);
            pickDraw();
        });

        function pickOpen() { pickDraw(); document.getElementById('pick-modal').classList.remove('hidden'); }
        function pickClose() { document.getElementById('pick-modal').classList.add('hidden'); }

        function pickSave() {
            fetch(PICK_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': PICK_CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    pick_tests: PICK.test.total ? PICK.test.val : null,
                    pick_masalas: PICK.masala.total ? PICK.masala.val : null
                })
            }).then(function (r) { if (!r.ok) throw new Error(r.status); pickLabel(); pickClose(); })
                .catch(function (e) { alert('Saqlanmadi: ' + e.message); });
        }
    </script>
</body>

</html>