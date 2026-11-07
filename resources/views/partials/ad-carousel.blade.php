@php $ads = \App\Models\Ad::where('active', true)->orderBy('sort')->get(); @endphp
@if($ads->isEmpty())
    <p class="text-sm text-gray-400 text-center py-10"></p>
@else
    <!-- Orqa fon o'chirildi, shunda faqat rasmning o'zi ko'rinadi -->
    <div id="adc" class="relative overflow-hidden rounded-xl group w-full h-full" style="min-height:320px;">

        @foreach($ads as $i => $ad)
            <a href="{{ route('ad.go', $ad) }}" target="_blank" rel="noopener sponsored"
                class="adc-slide absolute inset-0 transition-opacity duration-700 ease-in-out {{ $i ? 'opacity-0 pointer-events-none' : 'opacity-100' }}">

                <!-- 1. ORQA FON: xira (blurred) rasm -->
                <img src="{{ asset($ad->image_path) }}" alt="" aria-hidden="true"
                    class="absolute inset-0 w-full h-full object-cover scale-110 blur-md opacity-60">

                <!-- 2. ASOSIY RASM: contain — rasmning hamma joyi ko'rinadi -->
                <img src="{{ asset($ad->image_path) }}" alt="{{ $ad->title }}"
                    class="adc-img absolute inset-0 w-full h-full object-contain object-center">

                <!-- 3. Pastki yarim shaffof panel -->
                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/50 to-transparent pointer-events-none">
                </div>
            </a>
        @endforeach

        <!-- Nuqtalar -->
        @if($ads->count() > 1)
            <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2 z-20">
                @foreach($ads as $i => $ad)
                    <button type="button" data-slide="{{ $i }}"
                        class="adc-dot h-2 rounded-full transition-all duration-500 cursor-pointer {{ $i ? 'w-2 bg-white/60' : 'w-6 bg-white' }}"
                        style="box-shadow: 0 0 6px rgba(0,0,0,0.5)" aria-label="Reklama {{ $i + 1 }}"></button>
                @endforeach
            </div>

            <!-- Oldinga / Orqaga tugmalar -->
            <button type="button"
                class="adc-prev absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-black/30 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-black/50 backdrop-blur-sm">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
            <button type="button"
                class="adc-next absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-black/30 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-black/50 backdrop-blur-sm">
                <i class="fas fa-chevron-right text-sm"></i>
            </button>
        @endif
    </div>

    @if($ads->count() > 1)
        <style>
            /* Ken Burns effekti */
            #adc .adc-img {
                animation: kenburns 8s ease-out forwards;
            }

            @keyframes kenburns {
                0% {
                    transform: scale(1);
                }

                100% {
                    transform: scale(1.08);
                }
            }
        </style>

        <script>
            (function () {
                var root = document.getElementById('adc');
                var slides = root.querySelectorAll('.adc-slide');
                var dots = root.querySelectorAll('.adc-dot');
                var current = 0;
                var timer = null;

                function resetImg(slide) {
                    var img = slide.querySelector('.adc-img');
                    img.style.animation = 'none';
                    void img.offsetWidth;
                    img.style.animation = '';
                }

                function show(n) {
                    slides[current].classList.remove('opacity-100');
                    slides[current].classList.add('opacity-0', 'pointer-events-none');
                    dots[current].classList.remove('w-6', 'bg-white');
                    dots[current].classList.add('w-2', 'bg-white/60');

                    current = (n + slides.length) % slides.length;

                    var slide = slides[current];
                    slide.classList.remove('opacity-0', 'pointer-events-none');
                    slide.classList.add('opacity-100');
                    resetImg(slide);

                    dots[current].classList.remove('w-2', 'bg-white/60');
                    dots[current].classList.add('w-6', 'bg-white');
                }

                function play() { stop(); timer = setInterval(function () { show(current + 1); }, 5000); }
                function stop() { if (timer) clearInterval(timer); }

                play();
                root.addEventListener('mouseenter', stop);
                root.addEventListener('mouseleave', play);

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        show(parseInt(dot.dataset.slide));
                        play();
                    });
                });

                var prev = root.querySelector('.adc-prev');
                var next = root.querySelector('.adc-next');
                if (prev) prev.addEventListener('click', function () { show(current - 1); play(); });
                if (next) next.addEventListener('click', function () { show(current + 1); play(); });
            })();
        </script>
    @endif
@endif