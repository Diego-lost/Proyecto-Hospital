<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Ejecuta DxGPT /api/diagnose vía Web PubSub (script Node en scripts/dxgpt-diagnose.mjs).
 */
final class DxGptDiagnoseClient
{
    /**
     * @return array<string, mixed>
     */
    public function diagnose(string $description, string $model): array
    {
        $script = base_path('scripts/dxgpt-diagnose.mjs');
        if (! is_file($script)) {
            throw new RuntimeException('dxgpt_script_missing');
        }

        $nodeModules = base_path('scripts/node_modules');
        if (! is_dir($nodeModules)) {
            throw new RuntimeException('dxgpt_node_modules_missing');
        }

        $timeout = max(60, (int) config('ai.timeout') + 45);
        $payloadFile = $this->writeDescriptionPayload($description);

        try {
            $output = $this->runNodeScript($script, $payloadFile, $model, $timeout);
        } finally {
            @unlink($payloadFile);
        }

        $decoded = json_decode($this->stripBom(trim($output)), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('invalid_response');
        }

        if (($decoded['result'] ?? null) === 'error') {
            throw new RuntimeException('dxgpt_api_error');
        }

        return $decoded;
    }

    private function writeDescriptionPayload(string $description): string
    {
        $path = storage_path('app/dxgpt-'.Str::uuid()->toString().'.txt');
        file_put_contents($path, $description);

        return $path;
    }

    private function runNodeScript(string $script, string $payloadFile, string $model, int $timeoutSeconds): string
    {
        $node = $this->nodeBinary();
        $scriptName = basename($script);
        $dir = base_path('scripts');
        $env = [
            'DXGPT_BASE_URL' => rtrim((string) config('ai.dxgpt_base_url'), '/'),
            'DXGPT_SUBSCRIPTION_KEY' => (string) config('ai.dxgpt_subscription_key'),
            'DXGPT_MODEL' => $model,
            'DXGPT_TIMEZONE' => (string) config('ai.dxgpt_timezone', 'America/Lima'),
        ];

        $envPrefix = '';
        foreach ($env as $key => $value) {
            if (PHP_OS_FAMILY === 'Windows') {
                $envPrefix .= 'set '.escapeshellarg($key).'='.escapeshellarg($value).' && ';
            } else {
                $envPrefix .= escapeshellarg($key).'='.escapeshellarg($value).' ';
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $outputFile = storage_path('app/dxgpt-out-'.Str::uuid()->toString().'.json');
            $ps = 'Set-Location '.str_replace("'", "''", $dir).'; '
                .$this->powershellEnv($env)
                .'& '.("'".str_replace("'", "''", $node)."'").' '
                .str_replace("'", "''", $scriptName)
                .' @'.str_replace("'", "''", $payloadFile)
                .' | Out-File -Encoding utf8 '.str_replace("'", "''", $outputFile);
            $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -Command '.escapeshellarg($ps);
            exec($cmd, $execOut, $exitCode);
            $output = is_file($outputFile) ? (string) file_get_contents($outputFile) : '';
            @unlink($outputFile);
            if ($exitCode !== 0 || trim($output) === '') {
                $err = trim(implode("\n", $execOut));
                if (str_contains($err, 'WebPubSub timeout') || str_contains($output.$err, 'timeout')) {
                    throw new RuntimeException('dxgpt_timeout');
                }
                throw new RuntimeException('dxgpt_process_error');
            }

            return trim($output);
        }

        $cmd = 'cd '.escapeshellarg($dir).' && '.$envPrefix
            .escapeshellarg($node).' '.escapeshellarg($scriptName).' @'.escapeshellarg($payloadFile);

        $result = Process::timeout($timeoutSeconds)->run($cmd);

        if (! $result->successful()) {
            $err = trim($result->errorOutput()) ?: trim($result->output());
            if (str_contains($err, 'WebPubSub timeout') || str_contains($err, 'timeout')) {
                throw new RuntimeException('dxgpt_timeout');
            }
            if (str_contains($err, 'Missing DXGPT_SUBSCRIPTION_KEY')) {
                throw new RuntimeException('dxgpt_auth_error');
            }
            throw new RuntimeException('dxgpt_process_error');
        }

        return trim($result->output());
    }

    private function powershellEnv(array $env): string
    {
        $parts = [];
        foreach ($env as $key => $value) {
            $parts[] = '$env:'.str_replace("'", "''", $key).'='."'".str_replace("'", "''", $value)."'";
        }

        return implode('; ', $parts).'; ';
    }

    private function nodeBinary(): string
    {
        $configured = trim((string) env('NODE_BINARY', ''));
        if ($configured !== '' && (is_file($configured) || $configured === 'node')) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $programFiles = 'C:\\Program Files\\nodejs\\node.exe';
            if (is_file($programFiles)) {
                return $programFiles;
            }
        }

        return 'node';
    }

    private function stripBom(string $text): string
    {
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            return substr($text, 3);
        }

        return $text;
    }
}
