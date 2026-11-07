<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Setting;
use App\Models\StudentResult;
use App\Models\User;
use App\Models\Ad; // <--- Reklama modeli qo'shildi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // Tashriflar statistikasini hisoblash (Bugun, Hafta, Oy, Yil)
        $hits = fn($from) => DB::table('site_hits')->where('created_at', '>=', $from);
        $stat = [];
        foreach ([
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear()
        ] as $k => $from) {
            $stat[$k] = [
                'uv' => $hits($from)->distinct()->count('visitor'),
                'pv' => $hits($from)->count(),
            ];
        }

        // Oxirgi 14 kunlik grafik uchun ma'lumotlar
        $daily = DB::table('site_hits')
            ->selectRaw('date(created_at) as d, count(*) as pv, count(distinct visitor) as uv')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('d')->orderBy('d')->get();

        // Eng ko'p ochilgan sahifalar (oxirgi 30 kun, faqat path mavjudlari)
        $pages = DB::table('site_hits')->select('path', DB::raw('count(*) as c'))
            ->whereNotNull('path')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('path')->orderByDesc('c')->limit(10)->get();

        // Davlatlar bo'yicha tashrifchilar (oxirgi 30 kun)
        $countries = DB::table('site_hits')->select('country', DB::raw('count(distinct visitor) as c'))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('country')
            ->groupBy('country')->orderByDesc('c')->limit(10)->get();

        return view('admin.index', [
            'stat' => $stat,
            'daily' => $daily,
            'pages' => $pages,
            'countries' => $countries,
            'online' => DB::table('site_hits')->where('created_at', '>=', now()->subMinutes(5))->distinct()->count('visitor'),
            'peak' => (int) DB::table('online_snapshots')->where('created_at', '>=', now()->startOfWeek())->max('online'),
            'users' => User::count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'exams' => Exam::count(),
            'attempts' => StudentResult::whereNotNull('finished_at')->count(),
            'latestUsers' => User::latest()->limit(15)->get(),

            // Reklama sozlamalari
            'ads_enabled' => Setting::get('ads_enabled') === '1',
            'ad_head' => Setting::get('ad_head', ''),
            'ad_footer' => Setting::get('ad_footer', ''),
            
            // Karusel uchun reklamalar ro'yxati
            'ads' => Ad::orderBy('sort')->get(),
        ]);
    }

    public function saveAds(Request $request)
    {
        // Reklamani yoqish/o'chirish
        Setting::put('ads_enabled', $request->boolean('ads_enabled') ? '1' : '0');

        // Head va Footer reklama kodlarini saqlash
        Setting::put('ad_head', (string) $request->input('ad_head', ''));
        Setting::put('ad_footer', (string) $request->input('ad_footer', ''));

        return back()->with('success', 'Reklama sozlamalari muvaffaqiyatli saqlandi!');
    }

    // ==========================================
    // YANGI QO'SHILGAN REKLAMA KARUSELI METODLARI
    // ==========================================

    public function adStore(Request $r)
    {
        $r->validate([
            'title'    => 'nullable|string|max:100',
            'link_url' => ['required', 'url', 'regex:/^https?:\/\//i'],
            'image'    => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);
        
        $dir = public_path('ads');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        $name = bin2hex(random_bytes(8)) . '.' . $r->file('image')->guessExtension();
        $r->file('image')->move($dir, $name);

        Ad::create([
            'title' => $r->title,
            'link_url' => $r->link_url,
            'image_path' => 'ads/' . $name,
            'sort' => (int) Ad::max('sort') + 1,
        ]);
        
        return back()->with('success', 'Reklama qo\'shildi!');
    }

    public function adToggle(Ad $ad)
    {
        $ad->update(['active' => !$ad->active]);
        return back();
    }

    public function adDelete(Ad $ad)
    {
        // Rasmni diskdan o'chirish
        @unlink(public_path($ad->image_path));
        
        // Bazadan o'chirish
        $ad->delete();
        
        return back()->with('success', 'Reklama o\'chirildi!');
    }
}