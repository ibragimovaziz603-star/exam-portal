<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('exams', 'user_id')) {
            Schema::table('exams', function (Blueprint $t) {
                $t->unsignedBigInteger('user_id')->nullable();
            });
        }
    }
    public function down(): void {}
};