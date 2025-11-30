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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // Cria: notifiable_type + notifiable_id + índice
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Apenas o índice de read_at (o outro já existe!)
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
