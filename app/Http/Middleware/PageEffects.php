<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class PageEffects
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!($response instanceof \Illuminate\Http\Response))
            return $response;
        if (!str_contains((string) $response->headers->get('Content-Type'), 'text/html'))
            return $response;

        $html = $response->getContent();
        if (!is_string($html))
            return $response;

        $pos = strrpos($html, '</body>');
        if ($pos === false)
            return $response;

        // ================= 1. REKLAMALARNI TAYYORLASH =================
        $adHead = $adFooter = '';

        if (
            Setting::get('ads_enabled') === '1'
            && !$request->is('student/take-exam/*', 'student/waiting/*', 'teacher*', 'admin*', 'login', 'register')
        ) {
            $adHead = (string) Setting::get('ad_head', '');
            $adFooter = (string) Setting::get('ad_footer', '');
        }

        // ================= 2. ANIMATSIYA VA HOVER EFFEKTLARI =================
        $isWelcome = $request->is('/') || $request->routeIs('home');
        $isAuth = $request->is('login') || $request->is('register');
        $isStudentJoin = $request->is('student/join');
        $isTeacher = $request->is('teacher');

        $addAnimation = $isWelcome || $isAuth || $isStudentJoin || $isTeacher;

        $css = <<<'CSS'
<style id="fx-css">
/* ================= ANIMATSIYALAR ================= */
@keyframes fxFadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fxFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fxScaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

html.fx-on .w-1\/3 { animation: fxFadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; }
html.fx-on .w-2\/3 { animation: fxFadeIn 1.2s ease-out both; }
html.fx-on nav a { animation: fxFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
html.fx-on nav a:nth-child(1) { animation-delay: 0.1s; }
html.fx-on nav a:nth-child(2) { animation-delay: 0.15s; }
html.fx-on nav a:nth-child(3) { animation-delay: 0.2s; }
html.fx-on nav a:nth-child(4) { animation-delay: 0.25s; }
html.fx-on h1, html.fx-on h2 { animation: fxScaleIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 0.2s; }
html.fx-on p { animation: fxFadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 0.3s; }
html.fx-on .flex.justify-center a { animation: fxScaleIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }

html.fx-on form, html.fx-on .bg-white.rounded-xl, html.fx-on .bg-white.rounded-2xl, html.fx-on .bg-white.shadow {
    animation: fxScaleIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
}
html.fx-on .max-w-md, html.fx-on .max-w-3xl, html.fx-on .max-w-7xl { animation: fxFadeUp 0.7s ease-out both; }
html.fx-on .bg-white.rounded-lg.shadow-sm { animation: fxFadeUp 0.6s ease-out both; animation-delay: 0.1s; }

/* ================= HOVER EFFEKTLARI ================= */
button, a {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
button:not(:disabled):hover, a:hover {
    transform: translateY(-3px) scale(1.02) !important;
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    filter: brightness(1.05);
}
button:not(:disabled):active, a:active {
    transform: scale(0.96) !important;
    box-shadow: none !important;
}

input, select, textarea { transition: all 0.3s ease !important; }
input:focus, select:focus, textarea:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border-color: #3b82f6 !important;
}

/* ================= REKLAMA O'RAMLARI ================= */
#ad-head-wrap, #ad-footer-wrap { width: 100%; }
#ad-head-wrap { margin-top: 1rem; margin-bottom: 0.5rem; }
#ad-footer-wrap { margin-top: 1rem; margin-bottom: 0.5rem; }

#ad-head-wrap > div,
#ad-footer-wrap > div {
    width: 100%;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0.75rem 1rem;
}

#ad-head-wrap > div > div,
#ad-footer-wrap > div > div,
#ad-head-wrap > div > iframe,
#ad-footer-wrap > div > iframe,
#ad-head-wrap ins.adsbygoogle,
#ad-footer-wrap ins.adsbygoogle {
    max-width: 100% !important;
    width: 100% !important;
    display: block !important;
    margin: 0 auto !important;
}

#ad-head-wrap img,
#ad-footer-wrap img {
    max-width: 100% !important;
    max-height: 280px;
    height: auto;
    object-fit: contain;
    display: block;
    margin: 0 auto;
    border-radius: 12px;
}

#ad-head-wrap a,
#ad-footer-wrap a {
    display: inline-flex !important;
}

/* Saytga kirganda silliq skroll */
html { scroll-behavior: smooth; }
</style>
CSS;

        $js = '<script id="fx-js">(function(){'
            . ($addAnimation ? 'var h=document.documentElement;h.classList.add("fx-on");setTimeout(function(){h.classList.remove("fx-on")},2000);' : '')
            // Sahifa yuklanganda asosiy qismga (pastga) skroll qilish, reklamani chetlab o'tish
            . 'if (window.location.hash === "") {'
            . '  var mainContent = document.querySelector(".h-screen") || document.querySelector("main") || document.querySelector(".max-w-3xl");'
            . '  if (mainContent) { setTimeout(function() { window.scrollTo({ top: mainContent.offsetTop, behavior: "instant" }); }, 50); }'
            . '}'
            . '})();</script>';

        // ================= 3. TEPA REKLAMASI =================
        if ($adHead !== '') {
            $adHeadBox = '<div id="ad-head-wrap"><div>'
                . $adHead .
                '</div></div>';

            $bodyPos = stripos($html, '<body');
            if ($bodyPos !== false) {
                $bodyClosePos = strpos($html, '>', $bodyPos);
                if ($bodyClosePos !== false) {
                    $html = substr_replace($html, $adHeadBox, $bodyClosePos + 1, 0);
                    $pos = strrpos($html, '</body>');
                }
            }
        }

        // ================= 4. PASTKI REKLAMA =================
        if ($adFooter !== '') {
            $adFooter = '<div id="ad-footer-wrap"><div>'
                . $adFooter .
                '</div></div>';
        }

        // ================= 5. HAMMASINI BIRLASHTIRIB QO'SHISH =================
        $contentToInject = $adFooter . $css . $js;

        if (!($request->is('student/take-exam/*'))) {
            $response->setContent(substr_replace($html, $contentToInject, $pos, 0));
        }

        return $response;
    }
}