<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('published_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->string('slug')->unique();
            $table->string('title', 150);
            $table->text('excerpt')->nullable();
            $table->longText('content');

            // Publicação
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->boolean('is_featured')->default(false);

            // Métricas
            $table->unsignedBigInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['is_published', 'published_at']);
            $table->index('is_featured');
            $table->index('slug');
            $table->index('author_id');
            $table->index('published_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
