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
        Schema::create('resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category');
            $table->string('content_rating')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('summary')->nullable();
            $table->longText('description');
            $table->timestamp('published_at')->nullable()->index();
            $table->json('tags')->nullable();
            $table->json('platforms')->nullable();
            $table->json('basic_info')->nullable();
            $table->json('files')->nullable();
            $table->json('screenshots')->nullable();
            $table->json('comments_preview')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('downloads_count')->default(0);
            $table->unsignedInteger('favorites_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->decimal('rating_value', 3, 1)->nullable();
            $table->string('rating_breakdown_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
