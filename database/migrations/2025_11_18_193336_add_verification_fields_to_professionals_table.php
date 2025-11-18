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
        Schema::table('professionals', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('professionals', 'specialty')) {
                $table->string('specialty')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('professionals', 'description')) {
                $table->text('description')->nullable()->after('specialty');
            }
            if (!Schema::hasColumn('professionals', 'portfolio_url')) {
                $table->string('portfolio_url')->nullable()->after('description');
            }
            if (!Schema::hasColumn('professionals', 'active')) {
                $table->boolean('active')->default(true)->after('approved');
            }
            if (!Schema::hasColumn('professionals', 'verified')) {
                $table->boolean('verified')->default(false)->after('active');
            }
            if (!Schema::hasColumn('professionals', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified');
            }
            if (!Schema::hasColumn('professionals', 'total_reviews')) {
                $table->integer('total_reviews')->default(0)->after('reviews_count');
            }
            if (!Schema::hasColumn('professionals', 'total_jobs')) {
                $table->integer('total_jobs')->default(0)->after('total_reviews');
            }
            if (!Schema::hasColumn('professionals', 'certifications')) {
                $table->json('certifications')->nullable()->after('total_jobs');
            }
            if (!Schema::hasColumn('professionals', 'equipment')) {
                $table->json('equipment')->nullable()->after('certifications');
            }
            if (!Schema::hasColumn('professionals', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('equipment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            $table->dropColumn([
                'specialty',
                'description',
                'portfolio_url',
                'active',
                'verified',
                'verified_at',
                'total_reviews',
                'total_jobs',
                'certifications',
                'equipment',
                'admin_notes',
            ]);
        });
    }
};
