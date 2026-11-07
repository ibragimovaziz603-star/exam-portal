<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Agar ustunlar yo'q bo'lsa qo'shadi
            if (!Schema::hasColumn('exams', 'pick_tests')) {
                $table->integer('pick_tests')->nullable();
            }
            if (!Schema::hasColumn('exams', 'pick_masalas')) {
                $table->integer('pick_masalas')->nullable();
            }
        });

        // Student_result jadvaliga question_ids ustunini qo'shish
        Schema::table('student_results', function (Blueprint $table) {
            if (!Schema::hasColumn('student_results', 'question_ids')) {
                $table->json('question_ids')->nullable();
            }
        });

        // Student_answers jadvaliga grade ustunini qo'shish
        Schema::table('student_answers', function (Blueprint $table) {
            if (!Schema::hasColumn('student_answers', 'grade')) {
                $table->integer('grade')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['pick_tests', 'pick_masalas']);
        });
        Schema::table('student_results', function (Blueprint $table) {
            $table->dropColumn('question_ids');
        });
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn('grade');
        });
    }
};