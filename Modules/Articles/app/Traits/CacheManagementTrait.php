<?php

namespace Modules\Articles\app\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait para gerenciar a limpeza de cache de endpoints de artigos.
 *
 * Prioriza Cache Tags e oferece uma limpeza manual de chaves mais comuns
 * como fallback para drivers que não suportam tags.
 */
trait CacheManagementTrait
{
    /**
     * Limpa o cache associado aos endpoints de listagem de artigos (index e featured).
     *
     * @return void
     */
    protected function flushArticlesCache(): void
    {
        // Verifica se o driver de cache suporta tags (e.g., redis, memcached).
        $driver = config('cache.default');

        if ($driver !== 'file' && $driver !== 'database') {

            // 💡 Opção 1: Limpeza com Cache Tags (Recomendado e mais eficaz)
            // Requer que você tenha implementado Cache::tags(['articles']) nos seus métodos index/featured.
            Cache::tags(['articles'])->flush();
            return;
        }

        // 💡 Opção 2: Limpeza manual de chaves específicas (Fallback para File/Database)

        // Limpa chaves de Artigos em Destaque
        Cache::forget('featured_articles_limit_6');
        Cache::forget('featured_articles_limit_10');

        // --- INÍCIO DA LIMPEZA EXPANDIDA (510 operações) ---

        // 1. Limpa o cache para a Categoria 'all' nas páginas 1 a 10.
        $this->flushSpecificArticleKeys('all', 1, 10);

        // 2. Limpa o cache para as Categorias Individuais (ID 1 a 50) nas páginas 1 a 10.
        // O cache key é articles_index_cat_1_page_1, articles_index_cat_50_page_10, etc.
        for ($catId = 1; $catId <= 50; $catId++) {
            $this->flushSpecificArticleKeys($catId, 1, 10);
        }

        // *AVISO*: Esta limpeza manual é abrangente. Se você tiver categorias combinadas
        // (ex: articles_index_cat_1,5_page_1), elas NÃO serão limpas.
    }

    /**
     * Helper para limpar um conjunto de chaves de artigos por categoria e range de páginas.
     *
     * @param string|int $categoryIds ID(s) de categoria (ex: 'all', 1)
     * @param int $startPage Página inicial
     * @param int $endPage Página final
     * @return void
     */
    protected function flushSpecificArticleKeys($categoryIds, int $startPage = 1, int $endPage = 10): void
    {
        for ($i = $startPage; $i <= $endPage; $i++) {
            $cacheKey = "articles_index_cat_{$categoryIds}_page_{$i}";
            Cache::forget($cacheKey);
        }
    }
}
