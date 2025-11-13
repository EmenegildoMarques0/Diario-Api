<?php
// database/migrations/2025_10_29_000004_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // ex.: 'welcome', 'new_article'
            $table->morphs('notifiable'); // polimórfico para artigos, categorias, etc.
            $table->json('data'); // detalhes da notificação
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type']);
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
