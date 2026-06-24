<?php
/**
 * API Proxy — forwards SMS prediction requests to the external API
 * Avoids CORS issues by making the request server-side via PHP.
 */

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

// Parse and validate JSON body
$rawBody = file_get_contents('php://input');

// Reject oversized payloads (max 5KB)
if (strlen($rawBody) > 5120) {
    http_response_code(413);
    echo json_encode(['error' => 'Payload too large.']);
    exit;
}

$body = json_decode($rawBody, true);

if (!$body || !isset($body['sms']) || !is_string($body['sms']) || trim($body['sms']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Field "sms" is required and must be a non-empty string.']);
    exit;
}

$smsText = trim($body['sms']);

// Enforce max length (1000 chars matches frontend limit)
if (mb_strlen($smsText, 'UTF-8') > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'SMS text exceeds maximum length of 1000 characters.']);
    exit;
}

// Load .env configuration
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Forward to external API
$apiUrl = $_ENV['API_URL'] ?? 'http://localhost/predict';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['sms' => $smsText]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    // Prevent SSRF — disable redirects
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_MAXREDIRS      => 0,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $curlError) {
    http_response_code(502);
    // Don't leak internal curl error details to the client
    echo json_encode(['error' => 'Gagal terhubung ke server prediksi. Silakan coba lagi nanti.']);
    exit;
}

// Validate the response is valid JSON with expected structure
$decoded = json_decode($response, true);

if (!is_array($decoded)) {
    http_response_code(502);
    echo json_encode(['error' => 'Respons dari server prediksi tidak valid.']);
    exit;
}

// Whitelist only expected fields to prevent injection from upstream
$safeResponse = [
    'prediction'   => isset($decoded['prediction']) && is_string($decoded['prediction'])
                        ? htmlspecialchars($decoded['prediction'], ENT_QUOTES, 'UTF-8')
                        : null,
    'confidence'   => isset($decoded['confidence']) && is_numeric($decoded['confidence'])
                        ? round((float) $decoded['confidence'], 2)
                        : null,
    'clean_text'   => isset($decoded['clean_text']) && is_string($decoded['clean_text'])
                        ? htmlspecialchars($decoded['clean_text'], ENT_QUOTES, 'UTF-8')
                        : null,
    'lexicon_score' => isset($decoded['lexicon_score']) && is_numeric($decoded['lexicon_score'])
                        ? (int) $decoded['lexicon_score']
                        : null,
];

http_response_code(200);
echo json_encode($safeResponse, JSON_UNESCAPED_UNICODE);
