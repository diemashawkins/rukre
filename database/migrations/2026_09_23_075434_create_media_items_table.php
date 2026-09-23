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
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->index();
            $table->string('disk', 64);
            $table->text('path');
            $table->char('path_hash', 40)->unique();
            $table->string('title');
            $table->string('collection')->nullable()->index();
            $table->string('creator')->nullable();
            $table->string('album')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('track_number')->nullable();
            $table->text('description')->nullable();
            $table->string('extension', 16);
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('duration')->nullable();
            $table->string('cover_path')->nullable();
            $table->timestamp('file_modified_at')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
