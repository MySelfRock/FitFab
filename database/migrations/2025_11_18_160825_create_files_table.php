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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->morphs('fileable'); // polymorphic relation (project_id, order_id, etc)
            $table->string('path'); // S3 path
            $table->string('filename');
            $table->string('type'); // pdf, svg, dxf, csv
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // file size in bytes
            $table->json('meta')->nullable(); // additional metadata
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
