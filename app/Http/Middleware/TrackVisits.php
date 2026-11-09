<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrackVisits
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (
            !$request->isMethod('GET') || $request->expectsJson()
            || $request->is('bot-stats', 'lang/*', 'admin*', 'up', 'go/*', 'student/*', 'teacher/*', 'history*')
        ) {
            return $response;
        }

        // Har sessiya uchun 10 daqiqada faqat 1 marta yoziladi
        $sid = $request->session()->getId();
        if (Cache::add('hit_' . $sid, 1, 600)) {
            $country = $request->header('CF-IPCountry') ?: null;
            DB::table('site_hits')->insert([
                'visitor' => $sid,
                'path' => '/' . ltrim($request->path(), '/'),
                'country' => $country,
                'created_at' => now(),
            ]);
        }
        return $response;
    }
}