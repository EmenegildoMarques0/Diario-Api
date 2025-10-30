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
        // 1. Tabela: users (com modelagem detalhada + SoftDeletes)
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT
            $table->string('name', 150);
            $table->string('username', 80)->unique()->nullable();
            $table->string('email', 255)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable(); // NULL para social login
            $table->enum('role', ['reader', 'editor', 'admin'])->default('reader');
            $table->text('bio')->nullable();
            $table->string('avatar_url', 1024)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // deleted_at DATETIME NULL

            // Índices para performance
            $table->index('username');
            $table->index('email');
            $table->index('role');
        });

        // 2. Tabela: password_reset_tokens (padrão Laravel)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Tabela: sessions (padrão Laravel para sessões web)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
