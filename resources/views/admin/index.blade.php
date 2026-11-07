<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans text-gray-800">
    @php $maxPv = max(1, (int) $daily->max('pv'));
    $names = ['today' => 'Bugun', 'week' => 'Hafta', 'month' => 'Oy', 'year' => 'Yil']; @endphp
    <div class="max-w-6xl mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-black"><i class="fas fa-gauge-high text-blue-600 mr-2"></i>Admin panel</h1>
            <a href="{{ route('home') }}" class="text-blue-600 font-bold"><i class="fas fa-home mr-1"></i>Saytga
                qaytish</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 rounded mb-4">{{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @foreach($names as $k => $label)
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-sm font-bold text-gray-500">{{ $label }}</p>
                    <p class="text-3xl font-black text-blue-600">{{ $stat[$k]['uv'] }}</p>
                    <p class="text-xs text-gray-500">tashrifchi (UV) · {{ $stat[$k]['pv'] }} ko'rish (PV)</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm font-bold text-gray-500">Hozir onlayn</p>
                <p class="text-3xl font-black text-green-600">{{ $online }}</p>
                <p class="text-xs text-gray-500">hafta rekordi: {{ $peak }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm font-bold text-gray-500">Foydalanuvchilar</p>
                <p class="text-3xl font-black">{{ $users }}</p>
                <p class="text-xs text-gray-500">ustoz: {{ $teachers }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm font-bold text-gray-500">Testlar</p>
                <p class="text-3xl font-black">{{ $exams }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm font-bold text-gray-500">Yechilgan testlar</p>
                <p class="text-3xl font-black">{{ $attempts }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-5 mb-6">
            <h2 class="font-black mb-4">Oxirgi 14 kun (ko'rishlar)</h2>
            <div class="flex items-end gap-2 h-40">
                @forelse($daily as $row)
                    <div class="flex-1 flex flex-col items-center justify-end h-full"
                        title="{{ $row->d }}: {{ $row->pv }} ko'rish, {{ $row->uv }} tashrifchi">
                        <span class="text-xs font-bold">{{ $row->pv }}</span>
                        <div class="w-full bg-blue-500 rounded-t"
                            style="height: {{ max(4, round($row->pv / $maxPv * 100)) }}%"></div>
                        <span
                            class="text-[10px] text-gray-500 mt-1">{{ \Illuminate\Support\Str::substr($row->d, 5) }}</span>
                    </div>
                @empty
                    <p class="text-gray-400">Ma'lumot hali yo'q.</p>
                @endforelse
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="font-black mb-3">Eng ko'p ochilgan sahifalar (30 kun)</h2>
                @forelse($pages as $p)
                    <div class="flex justify-between border-b py-1 text-sm"><span
                            class="truncate">{{ $p->path }}</span><b>{{ $p->c }}</b></div>
                @empty <p class="text-gray-400">Ma'lumot yo'q.</p> @endforelse
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="font-black mb-3">Davlatlar (30 kun)</h2>
                @forelse($countries as $c)
                    <div class="flex justify-between border-b py-1 text-sm"><span>{{ $c->country }}</span><b>{{ $c->c }}</b>
                    </div>
                @empty <p class="text-gray-400 text-sm">Davlat ma'lumoti faqat Cloudflare orqali ulanganda chiqadi.</p>
                @endforelse
            </div>
        </div>

        <!-- ========================================== -->
        <!-- FOOTER REKLAMA KARUSELI (YANGI QO'SHILDI)  -->
        <!-- ========================================== -->
        <div class="bg-white rounded-xl shadow p-5 mb-6">
            <h2 class="font-black mb-1"><i class="fas fa-images text-purple-600 mr-2"></i>Footer reklama karuseli</h2>
            <p class="text-sm text-gray-500 mb-4">Rasm tavsiya etilgan o'lcham: 600×300. Bir nechta qo'ysangiz, ular 4
                soniyada almashadi. Bosilsa havolaga o'tadi.</p>

            <form method="POST" action="{{ route('admin.adStore') }}" enctype="multipart/form-data"
                class="grid md:grid-cols-4 gap-3 mb-5">
                @csrf
                <input type="text" name="title" placeholder="Nomi (ixtiyoriy)" class="border rounded-lg p-2">
                <input type="url" name="link_url" placeholder="https://..." required class="border rounded-lg p-2">
                <input type="file" name="image" accept="image/*" required class="border rounded-lg p-2 text-sm">
                <button class="bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg py-2">Qo'shish</button>
            </form>
            @if($errors->any())
                <p class="text-red-600 text-sm mb-3">{{ $errors->first() }}</p>
            @endif

            @forelse($ads as $ad)
                <div class="flex items-center gap-3 border-b py-2">
                    <img src="{{ asset($ad->image_path) }}" class="w-24 h-12 object-cover rounded">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm truncate">{{ $ad->title ?: 'Nomsiz' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $ad->link_url }}</p>
                    </div>
                    <span class="text-sm font-bold text-blue-600">{{ $ad->clicks }} bosish</span>
                    <form method="POST" action="{{ route('admin.adToggle', $ad) }}">@csrf
                        <button
                            class="px-3 py-1 rounded font-bold text-sm {{ $ad->active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">{{ $ad->active ? 'Yoqilgan' : 'O\'chiq' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.adDelete', $ad) }}"
                        onsubmit="return confirm('O\'chirasizmi?')">@csrf @method('DELETE')
                        <button class="text-red-500"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            @empty
                <p class="text-gray-400 text-sm">Hali reklama yo'q.</p>
            @endforelse
        </div>

        <!-- ESKI ADSENSE REKLAMA BLOKI -->
        <div class="bg-white rounded-xl shadow p-5 mb-6">
            <h2 class="font-black mb-1"><i class="fas fa-rectangle-ad text-yellow-500 mr-2"></i>Reklama (AdSense)</h2>
            <p class="text-sm text-gray-500 mb-4">Reklama faqat bosh sahifa va tarix sahifalarida chiqadi. Test yechish,
                ustoz va admin sahifalarida chiqmaydi.</p>
            <form method="POST" action="{{ route('admin.saveAds') }}">
                @csrf
                <label class="flex items-center gap-2 font-bold mb-4">
                    <input type="checkbox" name="ads_enabled" value="1" {{ $ads_enabled ? 'checked' : '' }}
                        class="w-5 h-5"> Reklama yoqilgan
                </label>
                <label class="block text-sm font-bold mb-1">Sahifa boshi kodi (&lt;head&gt;, masalan AdSense asosiy
                    skripti):</label>
                <textarea name="ad_head" rows="4"
                    class="w-full border rounded-lg p-3 font-mono text-xs mb-4">{{ $ad_head }}</textarea>
                <label class="block text-sm font-bold mb-1">Sahifa pastki qismidagi reklama bloki kodi:</label>
                <textarea name="ad_footer" rows="5"
                    class="w-full border rounded-lg p-3 font-mono text-xs mb-4">{{ $ad_footer }}</textarea>
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold px-8 py-3 rounded-lg">Saqlash</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-black mb-3">Oxirgi ro'yxatdan o'tganlar</h2>
            @foreach($latestUsers as $u)
                <div class="flex justify-between border-b py-1 text-sm">
                    <span>{{ $u->name }} <span class="text-gray-400">({{ $u->email }})</span></span>
                    <span class="font-bold text-blue-600 uppercase">{{ $u->role }}</span>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>