<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('student_results', 'masala_answer')) {
            Schema::table('student_results', function (Blueprint $table) {
                $table->text('masala_answer')->nullable();
            });
        }
    }

    public function down(): void {}
};