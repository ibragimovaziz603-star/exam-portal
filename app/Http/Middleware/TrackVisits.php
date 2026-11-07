<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Davlatni aniqlash
        $country = $request->header('CF-IPCountry'); // Cloudflare bo'lsa
        if (!$country) {
            $country = session('geo_country');
            if ($country === null) {
                $country = self::lookup($request->ip());
                session(['geo_country' => $country ?: '']);
            }
        }

        // 2. Bazaga yozish
        if (!$request->is('teacher/live-status/*', 'student/ping/*', 'bot-stats', 'lang/*', 'admin*', 'up', 'go/*')) {
            DB::table('site_hits')->insert([
                'path' => $request->path(),
                'country' => $country ?: null,
                'visitor' => md5($request->ip() . $request->userAgent()), // Tashrif buyuruvchini aniqlash uchun
                'created_at' => now(),
                // 'updated_at' => now(), // <--- BU QATOR OLIB TASHLANDI
            ]);
        }

        return $next($request);
    }

    /**
     * IP orqali davlatni aniqlash (Kesh bilan)
     */
    private static function lookup(?string $ip): ?string
    {
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        return Cache::remember('geo_' . $ip, now()->addDays(30), function () use ($ip) {
            try {
                $r = Http::withoutVerifying()
                    ->connectTimeout(2)->timeout(2)
                    ->get('https://api.country.is/' . $ip);
                $c = $r->successful() ? $r->json('country') : null;
                return is_string($c) && strlen($c) === 2 ? $c : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}