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
        Schema::table('student_results', function (Blueprint $table) {
            $table->integer('warnings')->default(0); // Ogohlantirishlar soni
        });
    }

    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            $table->dropColumn('warnings');
        });
    }
};
