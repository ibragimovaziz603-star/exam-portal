<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_results', function (Blueprint $table) {
            $table->id();
            
            // Qaysi testga tegishli ekani (Ustoz panelida o'quvchilar sonini sanash uchun shart!)
            $table->foreignId('exam_id')->constrained()->onDelete('cascade'); 
            
            // O'quvchining ismi
            $table->string('student_name'); 
            
            // Testdan olgan bahosi va jami savollar
            $table->integer('score')->default(0);
            $table->integer('total_questions')->default(0);
            
            // Masala uchun ustunlar (oldin qo'shgan edik, lekin shu yerda ham bo'lsa yaxshi)
            $table->text('masala_answer')->nullable();
            $table->integer('masala_score')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};