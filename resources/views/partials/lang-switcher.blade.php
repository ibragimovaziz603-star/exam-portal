@php $cur = app()->getLocale(); @endphp
<div class="flex items-center gap-2">
    @foreach(['uz' => 'uz', 'ru' => 'ru', 'en' => 'gb'] as $loc => $flag)
        <a href="{{ route('lang', $loc) }}" title="{{ strtoupper($loc) }}"
           class="rounded overflow-hidden border-2 transition {{ $cur === $loc ? 'border-blue-600 scale-110 shadow' : 'border-transparent opacity-60 hover:opacity-100' }}">
            <img src="https://flagcdn.com/w40/{{ $flag }}.png" alt="{{ $loc }}" class="w-8 h-6 object-cover">
        </a>
    @endforeach
</div>