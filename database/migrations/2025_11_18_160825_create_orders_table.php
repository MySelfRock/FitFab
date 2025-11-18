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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('professional_id')->nullable()->constrained('professionals')->onDelete('set null');
            $table->foreignId('offer_id')->nullable()->constrained('offers')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid, in_progress, completed, cancelled, refunded
            $table->string('payment_provider')->nullable(); // stripe, pagseguro, etc
            $table->string('payment_provider_id')->nullable();
            $table->json('payment_metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
