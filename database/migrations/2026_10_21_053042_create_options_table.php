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
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            
            // Qaysi savolga tegishli ekanligi (SHART!)
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            
            // Variant matni (A, B, C, D)
            $table->string('option_text');
            
            // To'g'ri javobmi?
            $table->boolean('is_correct')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};