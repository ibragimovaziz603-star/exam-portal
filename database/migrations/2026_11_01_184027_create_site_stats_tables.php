<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_hits', function (Blueprint $t) {
            $t->id();
            $t->string('visitor', 64)->index();
            $t->timestamp('created_at')->index();
        });
        Schema::create('online_snapshots', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('online');
            $t->timestamp('created_at')->index();
        });
    }
    public function down(): void {}
};