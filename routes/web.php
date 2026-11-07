<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\HistoryController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Til almashtirish
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['uz', 'ru', 'en'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang');

// Tarix (tartib muhim: aniq yo'llar avval, {token} oxirida)
Route::middleware('auth')->group(function () {
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/history/exam/{code}', [HistoryController::class, 'examShow'])->name('history.exam');
    Route::get('/history/exam/{code}/{token}', [HistoryController::class, 'participantShow'])->name('history.participant');
    Route::get('/history/{token}', [HistoryController::class, 'show'])->name('history.show');
});

// ==========================================
// USTOZ UCHUN (8 xonali exam_code orqali)
// ==========================================
Route::middleware(['auth'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::post('/create-exam', [TeacherController::class, 'createExam'])->name('createExam');

    Route::get('/manage-exam/{code}', [TeacherController::class, 'manageExam'])->name('manageExam');
    Route::post('/manage-exam/{code}/add-question', [TeacherController::class, 'addQuestion'])->name('addQuestion');
    Route::post('/start-exam/{code}', [TeacherController::class, 'startExam'])->name('startExam');
    Route::post('/finish-exam/{code}', [TeacherController::class, 'finishExam'])->name('finishExam');
    Route::delete('/exams/{code}', [TeacherController::class, 'destroyExam'])->name('destroyExam');

    Route::get('/live-status/{code}', [TeacherController::class, 'liveStatus'])->name('liveStatus');
    Route::post('/clear-warning/{student_id}', [TeacherController::class, 'clearWarning'])->name('clearWarning');
    Route::delete('/kick/{student_id}', [TeacherController::class, 'kickStudent'])->name('kickStudent');

    Route::get('/grade/{code}/{token}', [TeacherController::class, 'gradeList'])->name('gradeList');
    Route::get('/grade/{code}/{token}/{qid}', [TeacherController::class, 'gradeEdit'])->name('gradeEdit');
    Route::post('/grade-answer/{answer}', [TeacherController::class, 'gradeSet'])->name('gradeSet');

    Route::delete('/question/{code}/{id}', [TeacherController::class, 'destroyQuestion'])->name('destroyQuestion');
    Route::post('/import-questions/{code}', [TeacherController::class, 'importQuestions'])->name('importQuestions');
    Route::post('/pick-count/{code}', [TeacherController::class, 'setPick'])->name('setPick');
    Route::post('/change-type/{code}', [TeacherController::class, 'changeType'])->name('changeType');
});

// ==========================================
// O'QUVCHI UCHUN (8 xonali token orqali)
// ==========================================
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/join', [StudentController::class, 'joinPage'])->name('join');
    Route::post('/join', [StudentController::class, 'joinExam'])->name('joinExam');
    Route::get('/waiting/{token}', [StudentController::class, 'waitingRoom'])->name('waiting');
    Route::get('/take-exam/{token}', [StudentController::class, 'takeExam'])->name('takeExam');
    Route::post('/submit-exam/{token}', [StudentController::class, 'submitExam'])->name('submitExam');
    Route::get('/result/{token}', [StudentController::class, 'result'])->name('result');
    Route::post('/warning/{token}', [StudentController::class, 'addWarning'])->name('warning');
    Route::get('/ping/{token}', function ($token) {
        \App\Models\StudentResult::where('token', $token)->firstOrFail();
        return response()->json(['ok' => true]);
    })->name('ping');
});

// ==========================================
// ADMIN PANEL VA REKLAMA BOSHQARUVI
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminController::class, 'index'])->name('index');
    Route::post('/ads', [\App\Http\Controllers\AdminController::class, 'saveAds'])->name('saveAds');

    // Reklama karuseli uchun yangi marshrutlar
    Route::post('/ad', [\App\Http\Controllers\AdminController::class, 'adStore'])->name('adStore');
    Route::post('/ad/{ad}/toggle', [\App\Http\Controllers\AdminController::class, 'adToggle'])->name('adToggle');
    Route::delete('/ad/{ad}', [\App\Http\Controllers\AdminController::class, 'adDelete'])->name('adDelete');
});

// Reklama bosilganda hisoblash va yo'naltirish (hamma uchun ochiq)
Route::get('/go/{ad}', function (\App\Models\Ad $ad) {
    $ad->increment('clicks');
    return redirect()->away($ad->link_url);
})->name('ad.go');

// Telegram bot uchun statistika
Route::get('/bot-stats', function (\Illuminate\Http\Request $r) {
    if (!env('BOT_TOKEN') || !hash_equals(env('BOT_TOKEN'), (string) $r->header('X-Bot-Token')))
        abort(403);

    $periods = [
        'today' => now()->startOfDay(),
        'week' => now()->startOfWeek(),
        'month' => now()->startOfMonth(),
        'year' => now()->startOfYear(),
    ];
    $out = [];
    foreach ($periods as $k => $from) {
        $out['visitors'][$k] = \DB::table('site_hits')->where('created_at', '>=', $from)->distinct()->count('visitor');
        $out['registered'][$k] = \App\Models\User::where('created_at', '>=', $from)->count();
    }
    $out['registered']['total'] = \App\Models\User::count();
    $out['online_now'] = \DB::table('site_hits')->where('created_at', '>=', now()->subMinutes(5))->distinct()->count('visitor');
    $out['max_online_week'] = (int) \DB::table('online_snapshots')->where('created_at', '>=', now()->startOfWeek())->max('online');
    $out['total_visitors'] = \DB::table('site_hits')->distinct()->count('visitor');
    foreach (['week' => now()->startOfWeek(), 'month' => now()->startOfMonth(), 'year' => now()->startOfYear()] as $k => $from) {
        $out['max_online'][$k] = (int) \DB::table('online_snapshots')->where('created_at', '>=', $from)->max('online');
    }
    $out['max_online']['total'] = (int) \DB::table('online_snapshots')->max('online');
    $out['peaks'] = \DB::table('online_snapshots')->orderByDesc('online')->orderByDesc('created_at')
        ->limit(5)->get(['online', 'created_at']);

    return response()->json($out);
});

require __DIR__ . '/auth.php';