<?php

function getAIConfig(): array {
    $provider = getenv('AI_PROVIDER') ?: 'groq';
    $apiKey = getenv('GROQ_API_KEY') ?: '';
    $model = getenv('GROQ_MODEL') ?: 'llama-3.1-8b-instant';

    $localPath = __DIR__ . '/ai.local.php';
    if (is_file($localPath)) {
        $local = require $localPath;
        if (is_array($local)) {
            $provider = trim((string) ($local['provider'] ?? $provider));
            $apiKey = trim((string) ($local['api_key'] ?? $apiKey));
            $model = trim((string) ($local['model'] ?? $model));
        }
    }

    return [
        'provider' => $provider,
        'api_key' => $apiKey,
        'model' => $model
    ];
}
