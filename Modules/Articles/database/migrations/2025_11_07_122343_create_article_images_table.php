<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->string('path'); // ex: articles/123/cover.jpg
            $table->boolean('is_cover')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['article_id', 'is_cover']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_images');
    }
};
