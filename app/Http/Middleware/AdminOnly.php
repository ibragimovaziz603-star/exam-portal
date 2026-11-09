<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response; // <--- BU QATOR QO'SHILDI

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admin YOKI Teacher bo'lsa ruxsat berish
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'teacher'])) {
            abort(403, 'Ruxsat yo\'q');
        }
        return $next($request);
    }
}