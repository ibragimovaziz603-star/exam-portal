<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $exists = \DB::selectOne("select 1 from pg_indexes where indexname = 'exams_exam_code_unique'");
        if (!$exists) {
            Schema::table('exams', function (Blueprint $t) {
                $t->unique('exam_code');
            });
        }
    }
    public function down(): void
    {
    }
};