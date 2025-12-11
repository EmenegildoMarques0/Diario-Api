<?php

namespace Modules\Articles\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleNewsletterLog extends Model
{
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'article_newsletter_logs';

    /**
     * The attributes that are mass assignable.
     * Define os campos que podem ser preenchidos via `create` ou `fill`.
     */
    protected $fillable = [
        'article_id',
        'user_id',
        'sent_at'
    ];

    /**
     * Opcional: Se a coluna 'updated_at' não é usada na sua migration.
     * protected const UPDATED_AT = null;
     */

    /**
     * Opcional: Para garantir que 'sent_at' seja tratado como objeto Carbon (data/hora).
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // --- RELACIONAMENTOS ---

    /**
     * Relacionamento: O log pertence a um Artigo.
     */
    public function article(): BelongsTo
    {
        // Assume que o modelo Article está no mesmo namespace ou tem um alias
        return $this->belongsTo(Article::class);
    }

    /**
     * Relacionamento: O log sabe quem enviou (o usuário administrador/editor).
     */
    public function sender(): BelongsTo
    {
        // O modelo User geralmente está no namespace global (App\Models\User).
        // Ajuste este namespace se o seu modelo User estiver em outro lugar.
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
