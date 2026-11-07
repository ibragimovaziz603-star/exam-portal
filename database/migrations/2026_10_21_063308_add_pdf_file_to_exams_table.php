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
        Schema::table('exams', function (Blueprint $table) {
            // PDF fayl manzilini saqlash uchun ustun qo'shamiz. 
            // nullable() - PDF yuklamasa ham test ishlayveradi (ixtiyoriy).
            $table->string('pdf_file')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Agar orqaga qaytarilsa, bu ustunni o'chiradi
            $table->dropColumn('pdf_file');
        });
    }
};