<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 font-sans text-gray-800">
    @php
        $tests = $questions->filter(fn($q) => $q->type !== 'masala')->values();
        $masalas = $questions->filter(fn($q) => $q->type === 'masala')->values();
        $mixed = $tests->count() > 0 && $masalas->count() > 0;
    @endphp

    <!-- Ogohlantirish -->
    <div id="warning-box"
        class="hidden fixed top-3 left-1/2 -translate-x-1/2 z-[60] w-full max-w-xl bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-xl">
        <p class="font-black"><i class="fas fa-exclamation-triangle mr-2"></i>DIQQAT!</p>
        <p id="warning-text"></p>
    </div>

    <div class="max-w-3xl mx-auto p-4">

        <div class="bg-white rounded-2xl shadow p-5 mb-4 border-b-4 border-blue-600 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black">{{ $exam->title }}</h1>
                <p class="text-sm text-gray-500"><i class="fas fa-user-circle mr-1"></i>{{ $student->student_name }}</p>
            </div>
            <div class="bg-red-500 text-white px-5 py-3 rounded-xl font-black text-2xl">
                <i class="fas fa-clock mr-2"></i><span class="timer-el">--:--</span>
            </div>
        </div>

        @if($mixed)
            <div class="flex gap-3 mb-4">
                <button type="button" id="tab-test" onclick="showTab('test')"
                    class="flex-1 py-3 rounded-xl font-black text-lg bg-blue-600 text-white shadow">
                    <i class="fas fa-list-check mr-2"></i>Testlar ({{ $tests->count() }})
                </button>
                <button type="button" id="tab-masala" onclick="showTab('masala')"
                    class="flex-1 py-3 rounded-xl font-black text-lg bg-white text-gray-700 shadow">
                    <i class="fas fa-code mr-2"></i>Masalalar ({{ $masalas->count() }})
                </button>
            </div>
        @endif

        <form id="exam-form" action="{{ route('student.submitExam', $student->token) }}" method="POST">
            @csrf

            <!-- TESTLAR -->
            <div id="sec-test" class="{{ $tests->count() ? '' : 'hidden' }}">
                @foreach($tests as $i => $q)
                    <div class="bg-white rounded-2xl shadow p-6 mb-4">
                        <div class="flex gap-4 mb-4">
                            <div
                                class="w-10 h-10 shrink-0 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center">
                                {{ $i + 1 }}
                            </div>
                            <p class="text-lg font-bold pt-1">{{ $q->question_text }}</p>
                        </div>
                        <div class="space-y-3 ml-14">
                            @foreach($q->options->shuffle($student->id * 1000 + $q->id) as $opt)
                                <label
                                    class="flex items-center gap-4 border-2 rounded-xl p-4 cursor-pointer hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}" class="w-5 h-5">
                                    <span class="text-lg">{{ $opt->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- MASALALAR RO'YXATI -->
            <div id="sec-masala" class="{{ $tests->count() ? 'hidden' : '' }}">
                @foreach($masalas as $i => $q)
                    <button type="button" id="card-{{ $q->id }}" onclick="openEditor({{ $q->id }})"
                        class="w-full text-left bg-white rounded-2xl shadow p-5 mb-4 border-l-4 border-green-500 hover:shadow-lg transition flex justify-between items-center">
                        <div>
                            <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full">Masala
                                {{ $i + 1 }}</span>
                            <p class="mt-3 text-lg font-bold whitespace-pre-line">{{ $q->question_text }}</p>
                            <p id="status-{{ $q->id }}" class="mt-2 text-sm text-gray-400">Hali yozilmagan</p>
                        </div>
                        <i id="icon-{{ $q->id }}" class="fas fa-chevron-right text-2xl text-green-500"></i>
                    </button>
                @endforeach
            </div>

            <!-- MUHARRIRLAR -->
            @foreach($masalas as $i => $q)
                <div id="ed-{{ $q->id }}" class="hidden fixed inset-0 z-40 bg-white flex flex-col">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 bg-gray-800 text-white">
                        <button type="button" onclick="closeEditor({{ $q->id }})"
                            class="bg-gray-600 hover:bg-gray-500 font-bold px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Orqaga
                        </button>
                        <span class="font-black">Masala {{ $i + 1 }}</span>
                        <div class="flex items-center gap-3">
                            <span class="bg-red-500 px-3 py-1 rounded-lg font-black"><i class="fas fa-clock mr-1"></i><span
                                    class="timer-el">--:--</span></span>
                            <select id="lang-{{ $q->id }}" onchange="onLangChange({{ $q->id }})"
                                class="text-gray-800 font-bold rounded-lg px-3 py-2">
                                <option value="python" selected>Python</option>
                                <option value="html">HTML / CSS</option>
                                <option value="js">JavaScript</option>
                            </select>
                            <button type="button" id="run-{{ $q->id }}" onclick="runCode({{ $q->id }})"
                                class="bg-green-500 hover:bg-green-600 font-black px-6 py-2 rounded-lg">
                                <i class="fas fa-play mr-1"></i> Run
                            </button>
                            <button type="button" id="done-{{ $q->id }}" onclick="markDone({{ $q->id }})"
                                class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-black px-6 py-2 rounded-lg">
                                <i class="fas fa-check mr-1"></i> Topdim
                            </button>
                        </div>
                    </div>

                    <div class="px-5 py-4 bg-yellow-50 border-b">
                        <p class="text-xs font-bold text-gray-500 mb-1">MASALA SHARTI</p>
                        <p class="text-lg font-bold whitespace-pre-line">{{ $q->question_text }}</p>
                    </div>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 min-h-0">
                        <div class="flex flex-col bg-gray-900 min-h-0 border-r border-gray-700">
                            <div class="px-4 py-2 bg-gray-800 text-gray-300 text-sm"><i class="fas fa-code mr-2"></i>Kodni
                                shu yerga yozing</div>
                            <textarea id="code-{{ $q->id }}" name="codes[{{ $q->id }}]" spellcheck="false"
                                class="code-area flex-1 w-full p-4 font-mono text-sm bg-gray-900 text-green-200 outline-none resize-none"></textarea>
                        </div>
                        <div class="flex flex-col min-h-0">
                            <div class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-bold"><i
                                    class="fas fa-desktop mr-2"></i>Natija</div>
                            <iframe id="out-{{ $q->id }}" sandbox="allow-scripts" class="flex-1 w-full bg-white"></iframe>
                            <pre id="pyout-{{ $q->id }}"
                                class="hidden flex-1 w-full bg-white p-4 text-sm overflow-auto whitespace-pre-wrap"></pre>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="text-center pb-10">
                <button type="submit"
                    class="bg-green-500 hover:bg-green-600 text-white font-black py-4 px-12 rounded-full shadow-lg text-xl">
                    <i class="fas fa-paper-plane mr-2"></i> Testni Yakunlash
                </button>
                @if($masalas->count())
                    <p class="text-sm text-gray-500 mt-3">Masalalaringizni ustoz tekshiradi.</p>
                @endif
            </div>
        </form>
    </div>

    <script>
        // ============ HIMOYA: "Orqaga" tugmasini bloklash ============
        var EXAM_KEY = 'exam_finished_{{ $student->token }}';
        var submitted = false;

        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function () {
            window.history.pushState(null, null, window.location.href);

            if (submitted) {
                alert("Siz testni allaqachon tugatgansiz. Qayta kirish mumkin emas!");
                window.location.href = "{{ route('student.result', $student->token) }}";
            } else {
                alert("Diqqat! Testni tugatmaguningizcha bu sahifadan orqaga qaytish mumkin emas. Testni Yakunlash tugmasini bosing.");
            }
        });

        if (sessionStorage.getItem(EXAM_KEY) === '1') {
            submitted = true;
            window.location.href = "{{ route('student.result', $student->token) }}";
        }

        // ============ Asosiy kod ============
        var form = document.getElementById('exam-form');
        var done = {};
        var CSRF = "{{ csrf_token() }}";
        var WARN_URL = "{{ route('student.warning', $student->token) }}";
        var left = {{ (int) $remaining }};

        function submitNow() {
            if (!submitted) {
                submitted = true;
                sessionStorage.setItem(EXAM_KEY, '1');
                form.submit();
            }
        }

        form.addEventListener('submit', function () {
            sessionStorage.setItem(EXAM_KEY, '1');
        });

        function tick() {
            var m = Math.floor(left / 60), s = left % 60;
            var txt = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            document.querySelectorAll('.timer-el').forEach(function (el) { el.innerText = txt; });
            if (left <= 0) { submitNow(); return; }
            left--;
        }
        tick();
        setInterval(tick, 1000);

        function showTab(which) {
            document.getElementById('sec-test').classList.toggle('hidden', which !== 'test');
            document.getElementById('sec-masala').classList.toggle('hidden', which !== 'masala');
            var a = 'flex-1 py-3 rounded-xl font-black text-lg shadow ';
            document.getElementById('tab-test').className = a + (which === 'test' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700');
            document.getElementById('tab-masala').className = a + (which === 'masala' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700');
        }

        // ============ STARTERS: Python mutlaqo bo'sh ============
        var STARTERS = {
            python: '', // Bo'sh! Hech qanday tayyor kod yo'q
            html: '<!DOCTYPE html>\n<html>\n<head>\n<title>Page Title</title>\n</head>\n<body>\n\n</body>\n</html>',
            js: '<!DOCTYPE html>\n<html>\n<body>\n\n<scr' + 'ipt>\n\n</scr' + 'ipt>\n\n</body>\n</html>'
        };
        var lastStarter = {};

        // DOMContentLoaded da default kod yozilmaydi (olib tashlandi)

        function onLangChange(id) {
            var ta = document.getElementById('code-' + id);
            var lang = document.getElementById('lang-' + id).value;
            var cur = ta.value.trim();
            var prev = lastStarter[id];
            if (cur === '' || (prev !== undefined && cur === prev.trim())) {
                ta.value = STARTERS[lang];
                lastStarter[id] = STARTERS[lang];
            }
        }

        function openEditor(id) {
            if (done[id]) return;
            var ta = document.getElementById('code-' + id);
            // Default kod yozilmaydi, faqat ochadi
            document.getElementById('ed-' + id).classList.remove('hidden');
        }

        function closeEditor(id) {
            document.getElementById('ed-' + id).classList.add('hidden');
            if (done[id]) return;
            var st = document.getElementById('status-' + id);
            var code = document.getElementById('code-' + id).value.trim();
            // Starter tekshiruvi olib tashlandi (bo'sh bo'lgani uchun)
            var empty = code === '';
            st.innerText = empty ? 'Hali yozilmagan' : 'Yozilgan (hali topshirilmagan)';
            st.className = 'mt-2 text-sm ' + (empty ? 'text-gray-400' : 'text-yellow-600 font-bold');
        }

        function markDone(id) {
            var code = document.getElementById('code-' + id).value.trim();
            
            // Majburiy tekshiruv olib tashlandi (kod bo'sh bo'lsa ham yopish mumkin)
            if (!code) {
                if (!confirm('Siz hech qanday kod yozmadingiz. Baribir topshirasizmi?')) return;
            } else {
                if (!confirm('Javobni topshirasizmi? Keyin o\'zgartirib bo\'lmaydi.')) return;
            }

            done[id] = true;
            document.getElementById('code-' + id).readOnly = true;
            document.getElementById('ed-' + id).classList.add('hidden');

            var st = document.getElementById('status-' + id);
            st.innerText = code ? 'Topshirildi ✓ (o\'zgartirib bo\'lmaydi)' : 'Topshirildi (bo\'sh)';
            st.className = 'mt-2 text-sm font-bold ' + (code ? 'text-green-600' : 'text-red-500');
            document.getElementById('card-' + id).classList.add('opacity-70', 'cursor-not-allowed');
            document.getElementById('icon-' + id).className = 'fas fa-lock text-2xl text-gray-400';
        }

        function runCode(id) {
            var lang = document.getElementById('lang-' + id).value;
            var code = document.getElementById('code-' + id).value;
            var frame = document.getElementById('out-' + id);
            var pre = document.getElementById('pyout-' + id);

            if (lang === 'python') {
                frame.classList.add('hidden');
                pre.classList.remove('hidden');
                runPython(code, pre);
                return;
            }
            pre.classList.add('hidden');
            frame.classList.remove('hidden');

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

        var pyPromise = null;
        function getPy() {
            if (!pyPromise) {
                pyPromise = new Promise(function (resolve, reject) {
                    var s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/pyodide.js';
                    s.onload = function () { loadPyodide().then(resolve, reject); };
                    s.onerror = function () { pyPromise = null; reject(new Error('Python yuklanmadi (internetni tekshiring)')); };
                    document.head.appendChild(s);
                });
            }
            return pyPromise;
        }
        async function runPython(code, box) {
            box.textContent = 'Python yuklanmoqda (birinchi marta biroz kutiladi)...';
            try {
                var py = await getPy();
                var out = '';
                py.setStdout({ batched: function (s) { out += s + '\n'; } });
                py.setStderr({ batched: function (s) { out += s + '\n'; } });
                py.setStdin({
                    stdin: function () {
                        var v = prompt('input() uchun qiymat kiriting:');
                        return (v === null ? '' : v) + '\n';
                    }
                });
                await py.runPythonAsync(code);
                box.textContent = out || '(natija yo\'q)';
            } catch (e) {
                box.textContent = String(e.message || e);
            }
        }

        document.querySelectorAll('.code-area').forEach(function (t) {
            t.addEventListener('keydown', function (e) {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    var s = t.selectionStart;
                    t.value = t.value.substring(0, s) + '  ' + t.value.substring(t.selectionEnd);
                    t.selectionStart = t.selectionEnd = s + 2;
                }
            });
        });

        @if($masalas->count() === 1 && $tests->count() === 0)
            openEditor({{ $masalas[0]->id }});
        @endif

        // ============ Ogohlantirish tizimi ============
        var MAX = 3, lastWarn = 0, hideTimer = null;
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState !== 'hidden' || submitted) return;
            if (Date.now() - lastWarn < 1500) return;
            lastWarn = Date.now();

            fetch(WARN_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    var box = document.getElementById('warning-box');
                    var text = document.getElementById('warning-text');
                    box.classList.remove('hidden');
                    clearTimeout(hideTimer);
                    if (d.warnings >= MAX) {
                        text.innerText = 'Siz 3 marta testdan chiqdingiz. Test avtomatik yakunlanmoqda...';
                        setTimeout(submitNow, 1500);
                    } else {
                        text.innerText = 'Testdan chiqib ketish taqiqlanadi. Sizda ' + (MAX - d.warnings) + ' ta imkoniyat qoldi!';
                        hideTimer = setTimeout(function () { box.classList.add('hidden'); }, 3000);
                    }
                });
        });

        setInterval(function () {
            fetch(WARN_URL.replace('/warning/', '/ping/'), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (r.status === 404) { submitted = true; location.href = "{{ route('home') }}"; } })
                .catch(function () { });
        }, 10000);
    </script>
</body>

</html>