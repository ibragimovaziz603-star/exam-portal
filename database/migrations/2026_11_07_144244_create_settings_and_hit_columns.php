<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });
        
        Schema::table('site_hits', function (Blueprint $t) {
            $t->string('path')->nullable();
            $t->string('country', 4)->nullable();
        });
    }
    
    public function down(): void
    {
        Schema::table('site_hits', function (Blueprint $t) {
            $t->dropColumn(['path', 'country']);
        });
        Schema::dropIfExists('settings');
    }
};