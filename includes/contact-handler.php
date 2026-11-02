<?php
declare(strict_types=1);

// Business inquiry backend: validation, stateless signed form token and PostgreSQL storage.
// The form is enabled only when APP_SECRET and DATABASE_URL are configured.

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
    // Local development reads .env; hosted environments set real environment variables.
    if (is_file(APP_ROOT . '/.env')) {
        foreach (file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            if (preg_match('/^[A-Z_]+$/', trim($key)) && getenv(trim($key)) === false) {
                putenv(trim($key) . '=' . trim(trim($value), "\"'"));
            }
        }
    }
}

const INQUIRY_TYPES = [
    'distribution' => '유통·입점 제안',
    'export' => '해외 수출·수입',
    'bulk' => '대량 구매·단체 주문',
    'research' => '공동 연구·제품 개발',
    'partnership' => '브랜드·사업 제휴',
    'other' => '기타 비즈니스 문의',
];

function unicode_length(string $value): int
{
    return preg_match_all('/./us', $value) ?: 0;
}

function inquiry_value(array $input, string $key): string
{
    return isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
}

function inquiry_types(array $input): array
{
    $types = $input['inquiry_type'] ?? [];
    if (!is_array($types)) {
        return [];
    }
    return array_values(array_unique(array_filter($types, fn($type) => is_string($type) && isset(INQUIRY_TYPES[$type]))));
}

function single_line_valid(string $value, int $min, int $max): bool
{
    $length = unicode_length($value);
    return $length >= $min && $length <= $max && !preg_match('/[\r\n\x00]/', $value);
}

// Returns field id => message. Field ids match the form's error elements.
function validate_inquiry(array $input): array
{
    $errors = [];
    $rawTypes = $input['inquiry_type'] ?? [];
    if (!inquiry_types($input) || !is_array($rawTypes) || count($rawTypes) > count(INQUIRY_TYPES)) {
        $errors['business-inquiry-type'] = '문의 유형을 하나 이상 선택해 주세요.';
    }
    if (!single_line_valid(inquiry_value($input, 'company'), 1, 100)) {
        $errors['business-company'] = '회사·기관명을 확인해 주세요.';
    }
    if (!single_line_valid(inquiry_value($input, 'contact_name'), 1, 50)) {
        $errors['business-name'] = '담당자명을 확인해 주세요.';
    }
    $email = inquiry_value($input, 'email');
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n\x00]/', $email)) {
        $errors['business-email'] = '이메일 주소를 확인해 주세요.';
    }
    $phone = inquiry_value($input, 'phone');
    if ($phone !== '' && !preg_match('/^[0-9+()\-.\s]{4,30}$/', $phone)) {
        $errors['business-phone'] = '연락처를 확인해 주세요.';
    }
    $website = inquiry_value($input, 'website');
    if ($website !== '' && (strlen($website) > 2048 || !filter_var($website, FILTER_VALIDATE_URL) || !preg_match('~^https?://~i', $website))) {
        $errors['business-website'] = 'https://를 포함한 홈페이지 주소를 입력해 주세요.';
    }
    if (!single_line_valid(inquiry_value($input, 'subject'), 1, 150)) {
        $errors['business-subject'] = '문의 제목을 확인해 주세요.';
    }
    $message = inquiry_value($input, 'message');
    if (unicode_length($message) < 1 || unicode_length($message) > 3000 || str_contains($message, "\0")) {
        $errors['business-message'] = '제안 내용을 확인해 주세요.';
    }
    if (inquiry_value($input, 'privacy_consent') !== '1') {
        $errors['business-consent'] = '개인정보 수집·이용 동의를 확인해 주세요.';
    }
    return $errors;
}

// ---------------------------------------------------------------------------
// Stateless form token: hex(issued-at, 8) . nonce(32 hex) . hmac-sha256(64 hex).
// No server-side session, so it works on serverless hosts.
// ---------------------------------------------------------------------------
function app_secret(): string
{
    $secret = getenv('APP_SECRET') ?: '';
    return strlen($secret) >= 32 ? $secret : '';
}

function issue_inquiry_token(?int $now = null): string
{
    $secret = app_secret();
    if ($secret === '') {
        return '';
    }
    $issued = str_pad(dechex($now ?? time()), 8, '0', STR_PAD_LEFT);
    $nonce = bin2hex(random_bytes(16));
    return $issued . $nonce . hash_hmac('sha256', $issued . $nonce, $secret);
}

// Returns the nonce when the token is genuine, fresh and the honeypot is empty.
function verify_inquiry_token(array $input, ?int $now = null): ?string
{
    $token = inquiry_value($input, 'form_token');
    $secret = app_secret();
    if ($secret === '' || !preg_match('/^[a-f0-9]{104}$/', $token) || inquiry_value($input, 'fax') !== '') {
        return null;
    }
    $issued = substr($token, 0, 8);
    $nonce = substr($token, 8, 32);
    if (!hash_equals(hash_hmac('sha256', $issued . $nonce, $secret), substr($token, 40))) {
        return null;
    }
    $age = ($now ?? time()) - (int)hexdec($issued);
    return $age >= 3 && $age <= 7200 ? $nonce : null;
}

// ---------------------------------------------------------------------------
// PostgreSQL storage (DATABASE_URL, e.g. Supabase session pooler).
// ---------------------------------------------------------------------------
function db_connection(): ?\PDO
{
    static $pdo = null;
    static $failed = false;
    if ($pdo instanceof \PDO) {
        return $pdo;
    }
    $url = getenv('DATABASE_URL') ?: '';
    if ($failed || $url === '' || !extension_loaded('pdo_pgsql')) {
        return null;
    }
    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) {
        $failed = true;
        return null;
    }
    parse_str($parts['query'] ?? '', $query);
    $sslmode = preg_match('/^[a-z-]+$/', (string)($query['sslmode'] ?? '')) ? $query['sslmode'] : 'require';
    $dsn = 'pgsql:host=' . $parts['host'] . ';port=' . ($parts['port'] ?? 5432) . ';dbname=' . ltrim($parts['path'] ?? '/postgres', '/') . ';sslmode=' . $sslmode;
    try {
        $pdo = new \PDO($dsn, rawurldecode($parts['user'] ?? ''), rawurldecode($parts['pass'] ?? ''), [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 5,
        ]);
        return $pdo;
    } catch (\Throwable $exception) {
        $failed = true;
        error_log('Inquiry database connection failed (' . get_class($exception) . ').');
        return null;
    }
}

function inquiry_ready(): bool
{
    return app_secret() !== '' && (getenv('DATABASE_URL') ?: '') !== '' && extension_loaded('pdo_pgsql');
}

function client_ip(): string
{
    // Vercel sets X-Forwarded-For; the first entry is the client.
    $forwarded = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    $ip = trim(explode(',', $forwarded)[0]);
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : (string)($_SERVER['REMOTE_ADDR'] ?? '');
}

function ip_hash(string $ip): string
{
    return hash_hmac('sha256', $ip, app_secret());
}

// Single-use token and 5 attempts per hour per bucket (an IP, or an admin login bucket).
function rate_allowed(\PDO $pdo, string $bucket, string $nonce = ''): bool
{
    try {
        $hash = ip_hash($bucket);
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM contact_attempts WHERE created_at < now() - interval '1 day'");
        if ($nonce !== '') {
            $exists = $pdo->prepare('SELECT 1 FROM contact_attempts WHERE nonce = :nonce LIMIT 1');
            $exists->execute(['nonce' => $nonce]);
            if ($exists->fetchColumn()) {
                $pdo->rollBack();
                return false;
            }
        }
        $count = $pdo->prepare("SELECT count(*) FROM contact_attempts WHERE ip_hash = :ip AND created_at > now() - interval '1 hour'");
        $count->execute(['ip' => $hash]);
        if ((int)$count->fetchColumn() >= 5) {
            $pdo->rollBack();
            return false;
        }
        $insert = $pdo->prepare('INSERT INTO contact_attempts (ip_hash, nonce) VALUES (:ip, :nonce)');
        $insert->execute(['ip' => $hash, 'nonce' => $nonce !== '' ? $nonce : null]);
        $pdo->commit();
        return true;
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Rate check failed (' . get_class($exception) . ').');
        return false;
    }
}

function store_inquiry(\PDO $pdo, array $input, string $ip): bool
{
    try {
        $statement = $pdo->prepare('INSERT INTO business_inquiries
            (inquiry_types, company, contact_name, email, phone, website, subject, message, ip_hash, user_agent)
            VALUES (:types, :company, :contact_name, :email, :phone, :website, :subject, :message, :ip_hash, :user_agent)');
        $statement->execute([
            // Values come from the INQUIRY_TYPES allow-list, so a plain array literal is safe.
            'types' => '{' . implode(',', inquiry_types($input)) . '}',
            'company' => inquiry_value($input, 'company'),
            'contact_name' => inquiry_value($input, 'contact_name'),
            'email' => inquiry_value($input, 'email'),
            'phone' => inquiry_value($input, 'phone'),
            'website' => inquiry_value($input, 'website'),
            'subject' => inquiry_value($input, 'subject'),
            'message' => inquiry_value($input, 'message'),
            'ip_hash' => ip_hash($ip),
            'user_agent' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512),
        ]);
        return true;
    } catch (\Throwable $exception) {
        error_log('Inquiry storage failed (' . get_class($exception) . ').');
        return false;
    }
}

function json_response(int $status, array $body): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
}

// POST /api/contact
function handle_inquiry_request(): void
{
    header('X-Content-Type-Options: nosniff');
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        json_response(405, ['ok' => false]);
        return;
    }
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . $host, PHP_URL_HOST)) {
        json_response(403, ['ok' => false, 'message' => '허용되지 않은 요청입니다.']);
        return;
    }
    $pdo = inquiry_ready() ? db_connection() : null;
    if (!$pdo instanceof \PDO) {
        json_response(503, ['ok' => false, 'message' => '지금은 온라인 문의를 받을 수 없습니다. partner@jejubellabell.com으로 보내주세요.']);
        return;
    }
    $input = $_POST;
    $nonce = verify_inquiry_token($input);
    if ($nonce === null) {
        json_response(400, ['ok' => false, 'message' => '페이지를 새로고침한 뒤 다시 보내주세요.']);
        return;
    }
    $errors = validate_inquiry($input);
    if ($errors) {
        json_response(422, ['ok' => false, 'message' => '입력 내용을 확인해 주세요.', 'errors' => $errors]);
        return;
    }
    $ip = client_ip();
    if (!rate_allowed($pdo, $ip, $nonce)) {
        json_response(429, ['ok' => false, 'message' => '잠시 후 다시 시도해 주세요.']);
        return;
    }
    if (!store_inquiry($pdo, $input, $ip)) {
        json_response(500, ['ok' => false, 'message' => '접수 중 문제가 생겼습니다. partner@jejubellabell.com으로 보내주세요.']);
        return;
    }
    json_response(200, ['ok' => true, 'message' => '문의가 접수되었습니다. 검토 후 입력하신 이메일로 회신드리겠습니다.']);
}
