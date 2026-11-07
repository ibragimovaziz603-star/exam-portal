<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
            if (!Schema::hasColumn('exams', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(30);
            }
        });
    }

    public function down(): void {}
};