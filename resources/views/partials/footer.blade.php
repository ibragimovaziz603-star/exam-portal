<!-- FOOTER QISMI -->
<footer class="bg-gray-100 border-t border-gray-200 mt-auto">
    <!-- Yuqori qism (padding yanada kichraytirildi) -->
    <div class="w-full px-6 lg:px-12 py-6">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            
            <!-- 1. Logotip va Aloqa -->
            <div class="md:col-span-4 lg:col-span-3">
                <h2 class="text-2xl font-black text-blue-600 mb-2 tracking-tighter">EXAM PORTAL</h2>
                <p class="text-gray-500 text-xs mb-1">Qo'llab-quvvatlash xizmati:</p>
                <p class="text-lg font-bold text-gray-800 mb-2">+998 (95) 148 66 84</p>
                <p class="text-gray-600 text-xs mb-1 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-gray-400"></i> Toshkent sh., Chilonzor tumani
                </p>
                <p class="text-gray-600 text-xs flex items-center gap-2">
                    <i class="fas fa-envelope text-gray-400"></i> info@examportal.uz
                </p>
            </div>

            <!-- 2. Katta Reklama Karuseli (p-1 o'rniga p-2 qilindi, ramka kattaroq) -->
            <div class="md:col-span-8 lg:col-span-9 w-full bg-white rounded-2xl shadow-sm border border-gray-200 p-2">
                @include('partials.ad-carousel')
            </div>

        </div>
    </div>

    <!-- Pastki qism (yanada ixcham) -->
    <div class="border-t border-gray-200 bg-gray-50">
        <div class="w-full px-6 lg:px-12 py-3 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-gray-500">© {{ date('Y') }} Exam Portal. Barcha huquqlar himoyalangan.</p>
            
            <div class="flex items-center gap-3">
                <a href="https://t.me/avazbek0754" target="_blank" title="Telegram"
                    class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-white hover:bg-[#229ED9] hover:border-[#229ED9] shadow-sm transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fab fa-telegram-plane text-xs"></i>
                </a>
                <a href="https://instagram.com/kqyuuiii" target="_blank" title="Instagram"
                    class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-white hover:bg-gradient-to-tr hover:from-yellow-400 hover:via-pink-500 hover:to-purple-600 hover:border-transparent shadow-sm transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fab fa-instagram text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</footer>