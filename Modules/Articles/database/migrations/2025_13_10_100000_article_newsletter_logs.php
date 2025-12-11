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
        Schema::create('article_newsletter_logs', function (Blueprint $table) {
            $table->id();

            // 1. CHAVE ESTRANGEIRA DO ARTIGO (ID do Artigo Enviado)
            $table->unsignedBigInteger('article_id')->unique();
            $table->foreign('article_id')
                  ->references('id')
                  ->on('articles')
                  ->onDelete('cascade'); // Se o artigo for deletado, o log também é

            // 2. CHAVE ESTRANGEIRA DO REMETENTE (ID do Usuário Admin/Editor que enviou)
            // É 'nullable' para permitir que o log seja criado mesmo se o usuário for deletado (set null)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // Se o usuário que enviou for deletado, o campo fica null

            // 3. REGISTRO DE TEMPO
            $table->timestamp('sent_at')->useCurrent(); // Opcional: registra a hora exata do envio/disparo
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_newsletter_logs');
    }
};
