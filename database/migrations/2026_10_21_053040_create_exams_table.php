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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Test nomi
            $table->string('exam_code', 8)->unique(); // 8 xonali unikal kod
            $table->string('type')->default('test'); // test, masala, aralash
            $table->boolean('is_started')->default(false); // Ustoz start bosdimi?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};