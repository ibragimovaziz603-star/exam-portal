<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', 'uz');
        if (!in_array($locale, ['uz', 'ru', 'en'])) $locale = 'uz';
        app()->setLocale($locale);

        $response = $next($request);

        if ($locale === 'uz' || !($response instanceof \Illuminate\Http\Response)) return $response;
        $html = $response->getContent();
        if (!is_string($html) || !str_contains($html, '</body>')) return $response;

        $file = lang_path($locale . '.json');
        if (!is_file($file)) return $response;
        $dict = json_decode(file_get_contents($file), true) ?: [];
        if (!$dict) return $response;

        // Uzun iboralar avval almashtirilsin
        uksort($dict, fn ($a, $b) => strlen($b) <=> strlen($a));

        // Faqat teglar orasidagi ko'rinadigan matn almashadi.
        // <input name="...">, class, value, script, style ga tegilmaydi.
        $parts = preg_split('/(<script\b.*?<\/script>|<style\b.*?<\/style>|<[^>]+>)/is', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $i => $part) {
            if ($part === '' || $part[0] === '<') continue;
            $parts[$i] = strtr($part, $dict);
        }

        $response->setContent(implode('', $parts));
        return $response;
    }
}