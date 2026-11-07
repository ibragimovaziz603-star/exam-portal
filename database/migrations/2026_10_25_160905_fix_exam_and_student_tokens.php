<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $t) {
            if (!Schema::hasColumn('exams', 'started_at')) $t->timestamp('started_at')->nullable();
        });

        Schema::table('student_results', function (Blueprint $t) {
            if (!Schema::hasColumn('student_results', 'token'))       $t->string('token', 8)->nullable()->unique();
            if (!Schema::hasColumn('student_results', 'warnings'))    $t->integer('warnings')->default(0);
            if (!Schema::hasColumn('student_results', 'finished_at')) $t->timestamp('finished_at')->nullable();
        });

        // Eski o'quvchilarga ham kod berish
        foreach (DB::table('student_results')->whereNull('token')->get() as $r) {
            DB::table('student_results')->where('id', $r->id)->update(['token' => Str::lower(Str::random(8))]);
        }
    }

    public function down(): void {}
};