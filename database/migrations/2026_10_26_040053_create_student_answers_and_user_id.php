<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('student_answers')) {
            Schema::create('student_answers', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('student_result_id');
                $t->unsignedBigInteger('question_id');
                $t->unsignedBigInteger('option_id')->nullable();
                $t->timestamps();
                $t->index('student_result_id');
            });
        }

        if (!Schema::hasColumn('student_results', 'user_id')) {
            Schema::table('student_results', function (Blueprint $t) {
                $t->unsignedBigInteger('user_id')->nullable();
            });
        }
    }

    public function down(): void {}
};