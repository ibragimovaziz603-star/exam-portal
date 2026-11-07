<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $t) {
            if (!Schema::hasColumn('questions', 'reference_solution')) $t->text('reference_solution')->nullable();
        });
        Schema::table('student_answers', function (Blueprint $t) {
            if (!Schema::hasColumn('student_answers', 'code_answer')) $t->text('code_answer')->nullable();
            if (!Schema::hasColumn('student_answers', 'ai_verdict'))  $t->string('ai_verdict')->nullable();
            if (!Schema::hasColumn('student_answers', 'ai_feedback')) $t->text('ai_feedback')->nullable();
        });
    }
    public function down(): void {}
};