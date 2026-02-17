<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/ai.php';

const OUT_OF_SCOPE_REPLY = "Sorry, I can't answer that. Please ask about student concerns.";

function uniqueWordsFromText(string $text): array {
    $parts = preg_split('/[^a-z0-9]+/i', strtolower($text));
    if (!is_array($parts)) {
        return [];
    }

    $stop = [
        'the','and','for','with','from','that','this','your','you','our','are','can','how','what',
        'where','when','who','why','about','into','have','has','had','was','were','will','would',
        'their','there','here','they','them','its','use','using','other','more','than'
    ];

    $out = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '' && strlen($p) >= 3 && !in_array($p, $stop, true)) {
            $out[] = $p;
        }
    }

    return array_values(array_unique($out));
}

function loadProjectScopeData(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $keywords = [
        'nova', 'student', 'school', 'campus',
        'enroll', 'enrollment', 'register', 'registration', 'add subject', 'drop subject', 'subject', 'subjects',
        'class', 'classes', 'course', 'courses', 'schedule', 'section',
        'tuition', 'payment', 'finance', 'fees', 'balance', 'assessment',
        'gatepass', 'gate pass', 'clearance', 'document', 'records', 'registrar',
        'office', 'offices', 'kins', 'csld', 'css', 'clinic', 'supplies',
        'profile', 'account', 'student id', 'program', 'year level',
        'survey', 'faq', 'announcement', 'support',
        'requirements', 'policy', 'policies',
        'computer lab', 'health center', 'equipment', 'materials', 'faculty', 'administrator'
    ];

    $offices = [];
    $officesPath = __DIR__ . '/../../../html/offices.html';

    if (is_file($officesPath)) {
        $html = file_get_contents($officesPath);
        if ($html !== false) {
            $nameMatches = [];
            $titleMatches = [];
            $descMatches = [];

            preg_match_all('/<h3>\s*([^<]+)\s*<\/h3>/i', $html, $nameMatches);
            preg_match_all('/<p class="office-title">\s*([^<]+)\s*<\/p>/i', $html, $titleMatches);
            preg_match_all('/<p class="office-description">\s*([^<]+)\s*<\/p>/i', $html, $descMatches);

            $names = $nameMatches[1] ?? [];
            $titles = $titleMatches[1] ?? [];
            $descs = $descMatches[1] ?? [];

            $count = min(count($names), count($titles), count($descs));
            for ($i = 0; $i < $count; $i++) {
                $name = trim(strip_tags((string) $names[$i]));
                $title = trim(strip_tags((string) $titles[$i]));
                $description = trim(strip_tags((string) $descs[$i]));

                if ($name === '') {
                    continue;
                }

                $offices[] = [
                    'name' => $name,
                    'title' => $title,
                    'description' => $description
                ];

                $keywords[] = strtolower($name);
                $keywords[] = strtolower($title);
                $keywords[] = strtolower($description);
            }
        }
    }

    $keywordTokens = [];
    foreach ($keywords as $k) {
        foreach (uniqueWordsFromText($k) as $token) {
            $keywordTokens[] = $token;
        }

        $plain = strtolower(trim((string) $k));
        if ($plain !== '' && strpos($plain, ' ') !== false) {
            $keywordTokens[] = $plain;
        }
    }

    $keywordTokens = array_values(array_unique(array_filter($keywordTokens, function ($item) {
        return $item !== '';
    })));

    $cache = [
        'offices' => $offices,
        'keywords' => $keywordTokens
    ];

    return $cache;
}

function isClearlyUnrelated(string $message): bool {
    $text = strtolower($message);

    $unrelatedKeywords = [
        'favorite food', 'favorite movie', 'favorite song', 'favorite color',
        'boyfriend', 'girlfriend', 'dating', 'love life',
        'crypto price', 'bitcoin price', 'stock price',
        'nba score', 'nfl score', 'sports betting',
        'celebrity gossip', 'horoscope', 'zodiac',
        'recipe', 'cooking', 'dinner', 'lunch', 'breakfast',
        'video game', 'gaming tips', 'pokemon',
        'joke', 'meme', 'roast me'
    ];

    foreach ($unrelatedKeywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return true;
        }
    }

    // If it mentions any project/school terms, treat as related.
    $scope = loadProjectScopeData();
    foreach ($scope['keywords'] as $keyword) {
        $keyword = strtolower(trim((string) $keyword));
        if ($keyword === '') {
            continue;
        }

        if (strpos($keyword, ' ') !== false) {
            if (strpos($text, $keyword) !== false) {
                return false;
            }
            continue;
        }

        if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text)) {
            return false;
        }
    }

    // Default to related unless clearly non-school.
    return false;
}

function localStudentSupportReply(string $message): string {
    $text = strtolower($message);

    if (strpos($text, 'profile') !== false || strpos($text, 'student id') !== false || strpos($text, 'program') !== false || strpos($text, 'year level') !== false || strpos($text, 'account') !== false) {
        return 'For profile/account concerns, please check your Profile page details (Student ID, email, program, and year level). If any information is incorrect, contact the Registrar Office for updates.';
    }

    if (strpos($text, 'tuition') !== false || strpos($text, 'payment') !== false || strpos($text, 'finance') !== false) {
        return 'For tuition and payment concerns, please proceed to the Finance Office. They can help with assessment, balances, and payment processing.';
    }

    if (strpos($text, 'add subject') !== false || strpos($text, 'add course') !== false || strpos($text, 'drop subject') !== false || strpos($text, 'kins') !== false) {
        return 'For adding or adjusting subjects/courses, please go to KINS. They handle enrollment-related subject adjustments.';
    }

    if (strpos($text, 'gatepass') !== false || strpos($text, 'gate pass') !== false || strpos($text, 'css') !== false) {
        return 'For gatepass concerns, please go to CSS. They assist with gatepass and student support requests.';
    }

    if (strpos($text, 'record') !== false || strpos($text, 'document') !== false || strpos($text, 'registrar') !== false) {
        return 'For academic records and official documents, please visit the Registrar Office.';
    }

    if (strpos($text, 'clinic') !== false || strpos($text, 'health') !== false) {
        return 'For health-related concerns, please visit the Clinic.';
    }

    $scope = loadProjectScopeData();
    foreach ($scope['offices'] as $office) {
        $name = strtolower($office['name']);
        if ($name !== '' && strpos($text, $name) !== false) {
            return $office['name'] . ' - ' . $office['title'] . '. ' . $office['description'];
        }
    }

    return 'I can help with student concerns. Please ask about profile, enrollment, tuition/payment, offices, schedules, requirements, or gatepass.';
}

function saveChatLog(PDO $pdo, ?int $userId, string $userMessage, string $botReply): void {
    $insert = $pdo->prepare('INSERT INTO chat_logs (user_id, user_message, bot_reply) VALUES (:user_id, :user_message, :bot_reply)');
    $insert->execute([
        'user_id' => $userId,
        'user_message' => $userMessage,
        'bot_reply' => $botReply
    ]);
}

function requestGeminiReply(string $message, string $systemPrompt, array $config): ?string {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($config['model']) . ':generateContent?key=' . rawurlencode($config['api_key']);

    $payload = [
        'systemInstruction' => [
            'parts' => [
                ['text' => $systemPrompt]
            ]
        ],
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $message]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.2,
            'maxOutputTokens' => 300
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($responseBody === false) {
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $responseData = json_decode($responseBody, true);
    if ($httpCode < 200 || $httpCode >= 300 || !is_array($responseData)) {
        return null;
    }

    $parts = $responseData['candidates'][0]['content']['parts'] ?? [];
    $textOut = '';
    if (is_array($parts)) {
        foreach ($parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                $textOut .= $part['text'];
            }
        }
    }

    $textOut = trim($textOut);
    return $textOut === '' ? null : $textOut;
}

function requestOpenAICompatibleReply(string $baseUrl, string $message, string $systemPrompt, array $config): ?string {
    $payload = [
        'model' => $config['model'],
        'messages' => [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'temperature' => 0.2,
        'max_tokens' => 300
    ];

    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['api_key']
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($responseBody === false) {
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $responseData = json_decode($responseBody, true);
    if ($httpCode < 200 || $httpCode >= 300 || !is_array($responseData)) {
        return null;
    }

    $reply = trim((string) ($responseData['choices'][0]['message']['content'] ?? ''));
    return $reply === '' ? null : $reply;
}

function requestOpenAIReply(string $message, string $systemPrompt, array $config): ?string {
    return requestOpenAICompatibleReply('https://api.openai.com/v1/chat/completions', $message, $systemPrompt, $config);
}

function requestGroqReply(string $message, string $systemPrompt, array $config): ?string {
    return requestOpenAICompatibleReply('https://api.groq.com/openai/v1/chat/completions', $message, $systemPrompt, $config);
}

function requestAIReply(string $message, string $systemPrompt, array $config): ?string {
    $provider = strtolower((string) ($config['provider'] ?? 'groq'));

    if ($provider === 'openai') {
        return requestOpenAIReply($message, $systemPrompt, $config);
    }

    if ($provider === 'gemini') {
        return requestGeminiReply($message, $systemPrompt, $config);
    }

    return requestGroqReply($message, $systemPrompt, $config);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$email = trim($input['email'] ?? '');

if ($message === '') {
    http_response_code(422);
    echo json_encode(['error' => 'message is required']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId && $email !== '') {
    $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $userStmt->execute(['email' => $email]);
    $user = $userStmt->fetch();
    if ($user) {
        $userId = (int) $user['id'];
    }
}

if (isClearlyUnrelated($message)) {
    saveChatLog($pdo, $userId, $message, OUT_OF_SCOPE_REPLY);
    echo json_encode([
        'reply' => OUT_OF_SCOPE_REPLY,
        'in_scope' => false,
        'source' => 'scope_guard'
    ]);
    exit;
}

$config = getAIConfig();
if (($config['api_key'] ?? '') === '') {
    $reply = localStudentSupportReply($message);
    saveChatLog($pdo, $userId, $message, $reply);
    echo json_encode([
        'reply' => $reply,
        'in_scope' => true,
        'source' => 'local'
    ]);
    exit;
}

$scope = loadProjectScopeData();
$officeLines = [];
foreach ($scope['offices'] as $office) {
    $officeLines[] = '- ' . $office['name'] . ': ' . $office['title'] . ' - ' . $office['description'];
}
$officeContext = implode("\n", $officeLines);

$systemPrompt = "You are Nova, student support assistant.\n"
    . "Answer student concerns related to school and campus life, including profile/account, enrollment, academics, offices, payments, requirements, policies, and student services.\n"
    . "Do not answer unrelated personal entertainment/lifestyle topics.\n"
    . "Use these offices from the project when relevant:\n"
    . $officeContext . "\n"
    . "If the question is unrelated to student concerns, reply exactly with: \"" . OUT_OF_SCOPE_REPLY . "\"";

$reply = requestAIReply($message, $systemPrompt, $config);
if ($reply !== null) {
    $reply = str_ireplace('AppDib', 'our campus', $reply);
}

if ($reply === null) {
    $reply = localStudentSupportReply($message);
    $source = 'local';
} else {
    $source = strtolower((string) ($config['provider'] ?? 'groq'));
}

saveChatLog($pdo, $userId, $message, $reply);

echo json_encode([
    'reply' => $reply,
    'in_scope' => true,
    'source' => $source
]);
