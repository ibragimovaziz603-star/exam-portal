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
        if (!Schema::hasTable('ads')) {
            Schema::create('ads', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('image_path');
                $table->string('link_url');
                $table->boolean('active')->default(true);
                $table->integer('sort')->default(0);
                $table->integer('clicks')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
