<?php

namespace App\Http\Controllers\Debug; // Corrigido o namespace (ajuste se estiver em Debug

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    /**
     * Display environment variables, optionally read or clear log file content.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function debugEnv(Request $request)
    {
        // Proteger a rota com uma chave secreta
        if ($request->query('secret') !== env('DEBUG_SECRET', 'sua-chave-secreta')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Limpar o cache de configuração para garantir valores atualizados
        \Artisan::call('config:clear');

        // Lista de variáveis de ambiente
        $envVariables = [
            'APP_NAME' => env('APP_NAME'),
            'APP_ENV' => env('APP_ENV'),
            'APP_KEY' => env('APP_KEY'), // Cuidado ao exibir!
            'APP_DEBUG' => env('APP_DEBUG'),
            'APP_URL' => env('APP_URL'),
            'APP_LOCALE' => env('APP_LOCALE'),
            'APP_FALLBACK_LOCALE' => env('APP_FALLBACK_LOCALE'),
            'APP_FAKER_LOCALE' => env('APP_FAKER_LOCALE'),
            'APP_MAINTENANCE_DRIVER' => env('APP_MAINTENANCE_DRIVER'),
            'PHP_CLI_SERVER_WORKERS' => env('PHP_CLI_SERVER_WORKERS'),
            'BCRYPT_ROUNDS' => env('BCRYPT_ROUNDS'),
            'LOG_CHANNEL' => env('LOG_CHANNEL'),
            'LOG_STACK' => env('LOG_STACK'),
            'LOG_DEPRECATIONS_CHANNEL' => env('LOG_DEPRECATIONS_CHANNEL'),
            'LOG_LEVEL' => env('LOG_LEVEL'),
            'DB_CONNECTION' => env('DB_CONNECTION'),
            'DB_HOST' => env('DB_HOST'),
            'DB_PORT' => env('DB_PORT'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
            // 'DB_PASSWORD' => env('DB_PASSWORD'), // Removido para segurança
            'SESSION_DRIVER' => env('SESSION_DRIVER'),
            'SESSION_LIFETIME' => env('SESSION_LIFETIME'),
            'SESSION_ENCRYPT' => env('SESSION_ENCRYPT'),
            'SESSION_PATH' => env('SESSION_PATH'),
            'SESSION_DOMAIN' => env('SESSION_DOMAIN'),
            'BROADCAST_CONNECTION' => env('BROADCAST_CONNECTION'),
            'QUEUE_CONNECTION' => env('QUEUE_CONNECTION'),
            'CACHE_STORE' => env('CACHE_STORE'),
            'MEMCACHED_HOST' => env('MEMCACHED_HOST'),
            'REDIS_CLIENT' => env('REDIS_CLIENT'),
            'REDIS_HOST' => env('REDIS_HOST'),
            'REDIS_PASSWORD' => env('REDIS_PASSWORD'),
            'REDIS_PORT' => env('REDIS_PORT'),
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_SCHEME' => env('MAIL_SCHEME'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            // 'MAIL_PASSWORD' => env('MAIL_PASSWORD'), // Removido para segurança
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
            'VITE_APP_NAME' => env('VITE_APP_NAME'),
            // 'NIGHTWATCH_TOKEN' => env('NIGHTWATCH_TOKEN'), // Removido para segurança
            'NIGHTWATCH_REQUEST_SAMPLE_RATE' => env('NIGHTWATCH_REQUEST_SAMPLE_RATE'),
            'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
            // 'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'), // Removido para segurança
            // 'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY'), // Removido para segurança
            'AWS_BUCKET' => env('AWS_BUCKET'),
            'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
            'AWS_USE_PATH_STYLE_ENDPOINT' => env('AWS_USE_PATH_STYLE_ENDPOINT'),
            'AWS_URL' => env('AWS_URL'),
        ];

        // Mensagem de status para o log
        $logStatus = null;

        // Limpar o arquivo de log, se solicitado
        if ($request->query('clear_log') === 'true') {
            $logStatus = $this->clearLogFile('laravel.log'); // Nome do arquivo de log
        }

        // Ler o conteúdo do arquivo de log, se solicitado
        $logContent = null;
        if ($request->query('show_log') === 'true') {
            $logContent = $this->readLogFile('laravel.log'); // Nome do arquivo de log
        }

        return response()->json([
            'environment' => $envVariables,
            'log_content' => $logContent,
            'log_status' => $logStatus,
        ]);
    }

    /**
     * Read the content of a log file.
     *
     * @param string $filename
     * @return string|null
     */
    protected function readLogFile($filename)
    {
        try {
            // Caminho do arquivo de log
            $logPath = storage_path('logs/' . $filename);

            // Verifica se o arquivo existe
            if (!file_exists($logPath)) {
                return "Log file '{$filename}' does not exist.";
            }

            // Lê o conteúdo do arquivo
            $content = file_get_contents($logPath);

            if ($content === false) {
                return "Failed to read log file '{$filename}'.";
            }

            return $content;
        } catch (\Exception $e) {
            Log::error("Error reading log file '{$filename}': " . $e->getMessage());
            return "Error reading log file: " . $e->getMessage();
        }
    }

    /**
     * Clear the content of a log file.
     *
     * @param string $filename
     * @return string
     */
    protected function clearLogFile($filename)
    {
        try {
            // Caminho do arquivo de log
            $logPath = storage_path('logs/' . $filename);

            // Verifica se o arquivo existe
            if (!file_exists($logPath)) {
                Log::warning("Attempted to clear non-existent log file '{$filename}'.");
                return "Log file '{$filename}' does not exist.";
            }

            // Abre o arquivo em modo de escrita para truncá-lo (limpar conteúdo)
            if (file_put_contents($logPath, '') === false) {
                Log::error("Failed to clear log file '{$filename}'.");
                return "Failed to clear log file '{$filename}'.";
            }

            // Registra a ação de limpeza
            Log::info("Log file '{$filename}' cleared successfully.");

            return "Log file '{$filename}' cleared successfully.";
        } catch (\Exception $e) {
            Log::error("Error clearing log file '{$filename}': " . $e->getMessage());
            return "Error clearing log file: " . $e->getMessage();
        }
    }
}
