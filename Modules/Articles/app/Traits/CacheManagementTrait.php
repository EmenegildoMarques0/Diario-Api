<?php

namespace Modules\Articles\app\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait para gerenciar a limpeza de cache de endpoints de artigos.
 *
 * Utiliza Cache Tags 'articles' ou limpa chaves específicas se tags não forem suportadas.
 */
trait CacheManagementTrait
{
    /**
     * Limpa o cache associado aos endpoints de listagem de artigos (index e featured).
     */
    protected function flushArticlesCache(): void
    {
        // Verifica se o driver de cache suporta tags (e.g., redis, memcached).
        if (config('cache.default') !== 'file' && config('cache.default') !== 'database') {

            // 💡 Opção 1: Limpeza com Cache Tags (Recomendado)
            // Requer que você tenha implementado Cache::tags(['articles']) nos seus métodos index/featured.
            Cache::tags(['articles'])->flush();
            return;
        }

        // 💡 Opção 2: Limpeza de chaves específicas (Se Cache Tags não for suportado)
        // Limpa as chaves mais críticas (featured e primeira página principal).
        Cache::forget('featured_articles_limit_6');
        Cache::forget('featured_articles_limit_10');
        Cache::forget('articles_index_cat_all_page_1');
    }
}
