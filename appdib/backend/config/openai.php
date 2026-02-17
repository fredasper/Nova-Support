<?php

function getOpenAIConfig(): array {
    $apiKey = getenv('OPENAI_API_KEY') ?: '';
    $model = getenv('OPENAI_MODEL') ?: 'gpt-4o-mini';

    $localPath = __DIR__ . '/openai.local.php';
    if (is_file($localPath)) {
        $local = require $localPath;
        if (is_array($local)) {
            $apiKey = trim((string) ($local['api_key'] ?? $apiKey));
            $model = trim((string) ($local['model'] ?? $model));
        }
    }

    return [
        'api_key' => $apiKey,
        'model' => $model
    ];
}
