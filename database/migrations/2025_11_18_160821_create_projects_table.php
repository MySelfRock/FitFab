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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('templates');
            $table->foreignId('material_id')->nullable()->constrained('materials');
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('options'); // dims, counts, materialId, etc
            $table->string('status')->default('pending'); // pending, processing, ready, quoted, hired
            $table->decimal('estimated_price', 10, 2)->nullable();
            $table->json('metrics')->nullable(); // total area, waste %, sheets count, etc
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
