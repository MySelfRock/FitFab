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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // wood, mdf, mdp, compensado, etc
            $table->decimal('thickness_mm', 5, 2);
            $table->decimal('sheet_width_mm', 8, 2)->default(1220);
            $table->decimal('sheet_height_mm', 8, 2)->default(2440);
            $table->decimal('price_per_sheet', 10, 2)->nullable();
            $table->json('properties')->nullable(); // color, finish, density, etc
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
