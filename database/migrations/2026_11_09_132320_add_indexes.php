<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_results', function (Blueprint $t) {
            $t->index('exam_id');
            $t->index('token');
            $t->index('user_id');
        });
        Schema::table('student_answers', function (Blueprint $t) {
            $t->index('student_result_id');
            $t->index('question_id');
        });
        Schema::table('questions', fn(Blueprint $t) => $t->index('exam_id'));
        Schema::table('options', fn(Blueprint $t) => $t->index('question_id'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
