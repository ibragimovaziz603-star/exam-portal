<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masalani tekshirish</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-white font-sans text-gray-800">
@php
    $code = $answer->code_answer ?? '';
    $guess = str_contains($code, '<') ? 'html' : ((str_contains($code, 'print(') || str_contains($code, 'input(')) ? 'python' : 'js');
@endphp
<div class="h-screen flex flex-col">

    <div class="flex items-center justify-between gap-3 px-4 py-3 bg-gray-800 text-white">
        <a href="{{ route('teacher.gradeList', [$exam->exam_code, $student->token]) }}" class="bg-gray-600 hover:bg-gray-500 font-bold px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Orqaga
        </a>
        <span class="font-black truncate">{{ $student->student_name }}</span>
        <div class="flex items-center gap-2">
            <select id="lang" class="text-gray-800 font-bold rounded-lg px-3 py-2">
                <option value="html" {{ $guess === 'html' ? 'selected' : '' }}>HTML / CSS</option>
                <option value="js" {{ $guess === 'js' ? 'selected' : '' }}>JavaScript</option>
                <option value="python" {{ $guess === 'python' ? 'selected' : '' }}>Python</option>
            </select>
            <button type="button" onclick="runCode()" class="bg-blue-500 hover:bg-blue-600 font-black px-5 py-2 rounded-lg"><i class="fas fa-play mr-1"></i>Run</button>

            <div class="relative">
                <button type="button" id="btn-m" onclick="document.getElementById('menu').classList.toggle('hidden')" class="font-black px-5 py-2 rounded-lg text-lg bg-gray-500 text-white">Ball ▾</button>
                <div id="menu" class="hidden absolute right-0 mt-1 bg-white text-gray-800 rounded-xl shadow-xl overflow-hidden z-50">
                    @foreach([5, 4, 3, 2, 1] as $g)
                        <button type="button" onclick="setGrade({{ $g }})" class="block w-full px-10 py-2 font-black text-left hover:bg-gray-100">{{ $g }}</button>
                    @endforeach
                </div>
            </div>

            <button type="button" id="btn-t" onclick="setGrade(5)" class="font-black px-5 py-2 rounded-lg text-lg bg-gray-500 text-white">T</button>
            <button type="button" id="btn-f" onclick="setGrade(0)" class="font-black px-5 py-2 rounded-lg text-lg bg-gray-500 text-white">F</button>
        </div>
    </div>

    <div class="px-5 py-4 bg-yellow-50 border-b">
        <p class="text-xs font-bold text-gray-500 mb-1">MASALA SHARTI</p>
        <p class="text-lg font-bold whitespace-pre-line">{{ $question->question_text }}</p>
        @if($question->reference_solution)
            <p class="text-xs font-bold text-gray-500 mt-2">NAMUNA YECHIM: <code class="font-mono">{{ $question->reference_solution }}</code></p>
        @endif
    </div>

    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 min-h-0">
        <div class="flex flex-col bg-gray-900 min-h-0 border-r border-gray-700">
            <div class="px-4 py-2 bg-gray-800 text-gray-300 text-sm"><i class="fas fa-user-graduate mr-2"></i>O'quvchi kodi (o'zgartirib sinash mumkin, saqlanmaydi)</div>
            <textarea id="code" spellcheck="false" class="flex-1 w-full p-4 font-mono text-sm bg-gray-900 text-green-200 outline-none resize-none">{{ $code }}</textarea>
        </div>
        <div class="flex flex-col min-h-0">
            <div class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-bold"><i class="fas fa-desktop mr-2"></i>Natija</div>
            <iframe id="out" sandbox="allow-scripts" class="flex-1 w-full bg-white"></iframe>
            <pre id="pyout" class="hidden flex-1 w-full bg-white p-4 text-sm overflow-auto whitespace-pre-wrap"></pre>
        </div>
    </div>
</div>

<script>
    var VERDICT_URL = "{{ route('teacher.gradeSet', $answer->id) }}";
    var BACK_URL = "{{ route('teacher.gradeList', [$exam->exam_code, $student->token]) }}";
    var CSRF = "{{ csrf_token() }}";
    var current = @json($answer->grade);
    var COL = {
        5: 'bg-green-500 text-white', 4: 'bg-amber-800 text-white', 3: 'bg-yellow-400 text-gray-900',
        2: 'bg-pink-400 text-white', 1: 'bg-red-500 text-white', 0: 'bg-red-800 text-white'
    };

    function paint() {
        var base = 'font-black px-5 py-2 rounded-lg text-lg ', off = 'bg-gray-500 text-white';
        document.getElementById('btn-t').className = base + (current === 5 ? COL[5] + ' ring-4 ring-green-300' : off);
        document.getElementById('btn-f').className = base + (current === 0 ? COL[0] + ' ring-4 ring-red-300' : off);
        var m = document.getElementById('btn-m');
        if (current >= 1 && current <= 4) { m.className = base + COL[current] + ' ring-4 ring-white'; m.textContent = current + ' ▾'; }
        else { m.className = base + off; m.textContent = 'Ball ▾'; }
    }
    paint();

    function setGrade(g) {
        document.getElementById('menu').classList.add('hidden');
        fetch(VERDICT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ grade: g })
        }).then(function (r) { return r.json(); }).then(function () {
            current = g; paint();
            setTimeout(function () { location.href = BACK_URL; }, 400);
        });
    }

    var pyPromise = null;
    function getPy() {
        if (!pyPromise) {
            pyPromise = new Promise(function (resolve, reject) {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/pyodide.js';
                s.onload = function () { loadPyodide().then(resolve, reject); };
                s.onerror = function () { pyPromise = null; reject(new Error('Python yuklanmadi')); };
                document.head.appendChild(s);
            });
        }
        return pyPromise;
    }

    async function runCode() {
        var lang = document.getElementById('lang').value;
        var code = document.getElementById('code').value;
        var frame = document.getElementById('out');
        var pre = document.getElementById('pyout');

        if (lang === 'python') {
            frame.classList.add('hidden'); pre.classList.remove('hidden');
            pre.textContent = 'Python yuklanmoqda...';
            try {
                var py = await getPy();
                var out = '';
                py.setStdout({ batched: function (s) { out += s + '\n'; } });
                py.setStderr({ batched: function (s) { out += s + '\n'; } });
                py.setStdin({ stdin: function () { var v = prompt('input() uchun qiymat:'); return (v === null ? '' : v) + '\n'; } });
                await py.runPythonAsync(code);
                pre.textContent = out || '(natija yo\'q)';
            } catch (e) { pre.textContent = String(e.message || e); }
            return;
        }

        pre.classList.add('hidden'); frame.classList.remove('hidden');
        if (lang === 'js') {
            var open = '<scr' + 'ipt>', close = '</scr' + 'ipt>';
            frame.srcdoc = '<pre id="o" style="font-family:monospace;white-space:pre-wrap"></pre>' + open
                + 'var o=document.getElementById("o");'
                + 'console.log=function(){o.textContent+=Array.prototype.join.call(arguments," ")+"\\n"};'
                + 'window.onerror=function(m){o.textContent+="Xato: "+m+"\\n"};'
                + code + close;
        } else {
            frame.srcdoc = code;
        }
    }
    runCode();
</script>
</body>
</html>