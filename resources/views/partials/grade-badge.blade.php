@php
    $map = [
        5 => ['bg-green-500 text-white', 'T'],
        4 => ['bg-amber-800 text-white', '4'],
        3 => ['bg-yellow-400 text-gray-900', '3'],
        2 => ['bg-pink-400 text-white', '2'],
        1 => ['bg-red-500 text-white', '1'],
        0 => ['bg-red-800 text-white', 'F'],
    ];
    [$gcls, $gtxt] = $map[$g] ?? ['bg-gray-300 text-gray-700', '?'];
@endphp
<span class="inline-block min-w-[2.5rem] text-center font-black px-3 py-1 rounded-lg {{ $gcls }}">{{ $gtxt }}</span>