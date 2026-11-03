<?php
declare(strict_types=1);

// Internal admin: /admin, /admin/login, /admin/logout (POST), /admin/handle (POST), /admin/slides.
// Single shared password (ADMIN_PASSWORD_HASH) and a stateless signed cookie (APP_SECRET).
require_once __DIR__ . '/contact-handler.php';
require_once __DIR__ . '/research-slides.php';

const ADMIN_SESSION_TTL = 43200; // 12 hours
const ADMIN_PAGE_SIZE = 50;

function admin_password_hash(): string
{
    $hash = getenv('ADMIN_PASSWORD_HASH') ?: '';
    return password_get_info($hash)['algo'] ? $hash : '';
}

function admin_sign(string $payload): string
{
    return hash_hmac('sha256', $payload, app_secret());
}

// Session cookie: "<expires-unix>.<hmac>". Nothing is stored server-side.
function admin_session_value(): string
{
    $expires = (string)(time() + ADMIN_SESSION_TTL);
    return $expires . '.' . admin_sign('admin-session|' . $expires);
}

function admin_session_valid(string $cookie): bool
{
    if (!preg_match('/^(\d{1,12})\.([a-f0-9]{64})$/', $cookie, $m) || (int)$m[1] <= time()) {
        return false;
    }
    return hash_equals(admin_sign('admin-session|' . $m[1]), $m[2]);
}

// Login token: valid for the current and previous clock hour.
function admin_login_token_valid(string $token): bool
{
    $hour = intdiv(time(), 3600);
    return preg_match('/^[a-f0-9]{64}$/', $token) === 1
        && (hash_equals(admin_sign('admin-login|' . $hour), $token) || hash_equals(admin_sign('admin-login|' . ($hour - 1)), $token));
}

function admin_time(?string $value): string
{
    if (!$value) {
        return '';
    }
    try {
        return (new DateTime($value))->setTimezone(new DateTimeZone('Asia/Seoul'))->format('Y-m-d H:i');
    } catch (\Throwable) {
        return substr($value, 0, 16);
    }
}

function admin_layout(string $title, string $body, int $status = 200, string $script = ''): never
{
    http_response_code($status);
    echo '<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex, nofollow">'
        . '<title>' . e($title) . ' | 벨아벨팜 관리</title>'
        . '<link rel="stylesheet" href="/assets/css/fonts.css"><link rel="stylesheet" href="/assets/css/base.css"><link rel="stylesheet" href="/assets/css/admin.css">'
        . ($script !== '' ? '<script defer src="' . e($script) . '"></script>' : '')
        . '</head><body class="admin-body">' . $body . '</body></html>';
    exit;
}

function admin_redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function admin_query(array $overrides = []): string
{
    $params = array_filter(array_merge(['status' => $_GET['status'] ?? 'open', 'q' => $_GET['q'] ?? '', 'page' => $_GET['page'] ?? ''], $overrides), fn($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
}

function admin_header(string $title, string $current, string $formToken): string
{
    $nav = '';
    foreach (['/admin' => '문의', '/admin/slides' => '연구·기술 슬라이드'] as $href => $label) {
        $nav .= '<a class="admin-chip' . ($href === $current ? ' is-active' : '') . '" href="' . $href . '"' . ($href === $current ? ' aria-current="page"' : '') . '>' . e($label) . '</a>';
    }
    return '<header class="admin-header"><div class="admin-shell admin-header-inner">'
        . '<div><p class="admin-eyebrow">BELLABELL FARM</p><h1>' . e($title) . '</h1></div>'
        . '<nav class="admin-tabs" aria-label="관리 메뉴">' . $nav . '</nav>'
        . '<form method="post" action="/admin/logout"><input type="hidden" name="token" value="' . e($formToken) . '"><button class="admin-button admin-button--outline" type="submit">로그아웃</button></form>'
        . '</div></header>';
}

function admin_slide_messages(): array
{
    return [
        'no-file' => '이미지 파일을 선택해 주세요.',
        'too-large' => '이미지가 너무 큽니다. 3MB 이하로 줄여서 올려 주세요.',
        'bad-type' => 'JPG, PNG, WEBP 이미지만 올릴 수 있습니다.',
        'too-many' => '슬라이드는 최대 ' . RESEARCH_SLIDE_MAX_COUNT . '장까지 올릴 수 있습니다.',
        'failed' => '저장하지 못했습니다. 다시 시도해 주세요.',
    ];
}

// POST /admin/slides. Returns an error code from admin_slide_messages(), or '' on success.
function admin_slide_action(\PDO $pdo, string $op): string
{
    $id = (int)inquiry_value($_POST, 'id');
    try {
        if ($op === 'upload') {
            $image = research_slide_read_upload($_FILES['image'] ?? []);
            if (is_string($image)) {
                return $image;
            }
            if (count(research_slides_uploaded($pdo)) >= RESEARCH_SLIDE_MAX_COUNT) {
                return 'too-many';
            }
            research_slide_store($pdo, $image, mb_substr(inquiry_value($_POST, 'alt'), 0, 200));
        } elseif ($op === 'alt') {
            $pdo->prepare('UPDATE research_slides SET alt = :alt WHERE id = :id')->execute(['alt' => mb_substr(inquiry_value($_POST, 'alt'), 0, 200), 'id' => $id]);
        } elseif ($op === 'up' || $op === 'down') {
            research_slide_move($pdo, $id, $op === 'up' ? -1 : 1);
        } elseif ($op === 'delete') {
            $pdo->prepare('DELETE FROM research_slides WHERE id = :id')->execute(['id' => $id]);
        }
    } catch (\Throwable $exception) {
        error_log('Admin slide action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_slides_page(\PDO $pdo, string $formToken): never
{
    try {
        $slides = research_slides_uploaded($pdo);
    } catch (\Throwable $exception) {
        error_log('Admin slides query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('연구·기술 슬라이드', '/admin/slides', $formToken) . '<main class="admin-shell"><h1>슬라이드 목록을 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>의 <code>research_slides</code> 테이블이 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    $error = admin_slide_messages()[(string)($_GET['error'] ?? '')] ?? '';
    $last = count($slides) - 1;
    ob_start();
    ?>
<main class="admin-shell">
<?php if ($error !== ''): ?><p class="admin-error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form class="admin-card admin-slide-upload" method="post" action="/admin/slides" enctype="multipart/form-data" data-slide-upload>
<input type="hidden" name="token" value="<?= e($formToken) ?>"><input type="hidden" name="op" value="upload">
<h2>이미지 추가</h2>
<p class="admin-help">JPG, PNG, WEBP. 큰 이미지는 올리기 전에 자동으로 줄입니다. 세로 이미지는 전체가 보이고, 가로 이미지는 카드에 맞게 잘립니다.</p>
<label class="admin-label" for="slide-image">이미지</label>
<input class="admin-input" type="file" id="slide-image" name="image" accept="image/jpeg,image/png,image/webp,image/heic" required>
<label class="admin-label" for="slide-alt">이미지 설명 (화면에 보이지 않고 시각장애인용 음성 안내에 쓰입니다)</label>
<input class="admin-input" type="text" id="slide-alt" name="alt" maxlength="200">
<button class="admin-button admin-button--block" type="submit" data-busy-label="올리는 중...">올리기</button>
</form>
<?php if (!$slides): ?>
<p class="admin-empty" role="status">아직 올린 이미지가 없어 사이트에는 임시 이미지가 나옵니다. 첫 이미지를 올리면 임시 이미지는 사라집니다.</p>
<?php else: ?>
<ol class="admin-list admin-slide-list">
<?php foreach ($slides as $index => $slide): $id = (int)$slide['id']; ?>
<li class="admin-card admin-slide">
<img src="/media/research/<?= $id ?>" alt="" width="<?= (int)$slide['width'] ?>" height="<?= (int)$slide['height'] ?>" loading="lazy">
<div class="admin-slide-body">
<form class="admin-search" method="post" action="/admin/slides">
<input type="hidden" name="token" value="<?= e($formToken) ?>"><input type="hidden" name="op" value="alt"><input type="hidden" name="id" value="<?= $id ?>">
<input class="admin-input" type="text" name="alt" value="<?= e((string)$slide['alt']) ?>" maxlength="200" placeholder="이미지 설명" aria-label="<?= $index + 1 ?>번 이미지 설명">
<button class="admin-button admin-button--outline" type="submit">저장</button>
</form>
<div class="admin-slide-actions">
<?php foreach (['up' => ['위로', $index === 0], 'down' => ['아래로', $index === $last]] as $op => [$label, $disabled]): ?>
<form method="post" action="/admin/slides"><input type="hidden" name="token" value="<?= e($formToken) ?>"><input type="hidden" name="op" value="<?= $op ?>"><input type="hidden" name="id" value="<?= $id ?>"><button class="admin-button admin-button--outline" type="submit"<?= $disabled ? ' disabled' : '' ?>><?= $label ?></button></form>
<?php endforeach; ?>
<form method="post" action="/admin/slides" data-confirm="이 이미지를 삭제할까요?"><input type="hidden" name="token" value="<?= e($formToken) ?>"><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $id ?>"><button class="admin-button admin-button--outline" type="submit">삭제</button></form>
</div>
</div>
</li>
<?php endforeach; ?>
</ol>
<?php endif; ?>
</main>
<?php
    admin_layout('연구·기술 슬라이드', admin_header('연구·기술 슬라이드', '/admin/slides', $formToken) . ob_get_clean(), 200, '/assets/js/admin-slides.js');
}

function handle_admin_request(string $action): void
{
    header('Cache-Control: no-store, max-age=0');
    header('X-Robots-Tag: noindex, nofollow');
    $isHttps = ($_SERVER['HTTPS'] ?? 'off') !== 'off' || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $cookieOptions = ['path' => '/admin', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Strict'];
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($action, ['', 'login', 'logout', 'handle', 'slides'], true)) {
        admin_layout('찾을 수 없음', '<main class="admin-shell"><h1>페이지를 찾을 수 없습니다.</h1><p><a href="/admin">문의 목록으로</a></p></main>', 404);
    }
    if (admin_password_hash() === '' || !inquiry_ready()) {
        $missing = array_keys(array_filter([
            'ADMIN_PASSWORD_HASH' => admin_password_hash() === '',
            'APP_SECRET' => app_secret() === '',
            'DATABASE_URL' => (getenv('DATABASE_URL') ?: '') === '',
        ]));
        admin_layout('설정 필요', '<main class="admin-shell"><h1>관리 화면이 아직 설정되지 않았습니다.</h1><p>필요한 환경변수: <code>' . e(implode(', ', $missing)) . '</code></p></main>', 503);
    }

    $cookie = (string)($_COOKIE['bellabell_admin'] ?? '');
    $session = admin_session_valid($cookie) ? $cookie : '';
    $pdo = db_connection();

    if ($action === 'login') {
        if ($session !== '') {
            admin_redirect('/admin');
        }
        $error = '';
        $status = 200;
        if ($method === 'POST') {
            $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
            if (!admin_login_token_valid(inquiry_value($_POST, 'token'))) {
                [$status, $error] = [419, '세션이 만료되었습니다. 다시 시도해 주세요.'];
            } elseif (!$pdo instanceof \PDO || !rate_allowed($pdo, 'admin-login|' . client_ip())) {
                [$status, $error] = [429, '시도 횟수를 초과했습니다. 1시간 뒤에 다시 시도해 주세요.'];
            } elseif ($password === '' || strlen($password) > 256 || !password_verify($password, admin_password_hash())) {
                [$status, $error] = [401, '비밀번호가 올바르지 않습니다.'];
            } else {
                setcookie('bellabell_admin', admin_session_value(), $cookieOptions + ['expires' => time() + ADMIN_SESSION_TTL]);
                admin_redirect('/admin');
            }
        }
        admin_layout('로그인', '<main class="admin-shell admin-login"><form class="admin-card" method="post" action="/admin/login" autocomplete="off">'
            . '<p class="admin-eyebrow">BELLABELL FARM</p><h1>관리자 로그인</h1>'
            . ($error ? '<p class="admin-error" role="alert">' . e($error) . '</p>' : '')
            . '<input type="hidden" name="token" value="' . e(admin_sign('admin-login|' . intdiv(time(), 3600))) . '">'
            . '<label class="admin-label" for="password">비밀번호</label><input class="admin-input" type="password" id="password" name="password" required autofocus autocomplete="current-password">'
            . '<button class="admin-button admin-button--block" type="submit">로그인</button></form></main>', $status);
    }
    if ($session === '') {
        admin_redirect('/admin/login');
    }
    if (!$pdo instanceof \PDO) {
        admin_layout('데이터베이스 오류', '<main class="admin-shell"><h1>데이터베이스에 연결할 수 없습니다.</h1><p>잠시 후 다시 시도해 주세요.</p></main>', 502);
    }
    $formToken = admin_sign('admin-form|' . $session);
    $formTokenValid = fn(): bool => hash_equals($formToken, inquiry_value($_POST, 'token'));

    if ($action === 'logout') {
        if ($method === 'POST' && $formTokenValid()) {
            setcookie('bellabell_admin', '', $cookieOptions + ['expires' => time() - 3600]);
        }
        admin_redirect('/admin/login');
    }
    if ($action === 'handle') {
        if ($method !== 'POST') {
            admin_redirect('/admin');
        }
        if (!$formTokenValid()) {
            admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin">다시 열기</a></p></main>', 419);
        }
        $id = (int)inquiry_value($_POST, 'id');
        if ($id > 0) {
            try {
                $statement = $pdo->prepare('UPDATE business_inquiries SET handled_at = ' . (inquiry_value($_POST, 'done') === '1' ? 'now()' : 'NULL') . ' WHERE id = :id');
                $statement->execute(['id' => $id]);
            } catch (\Throwable $exception) {
                error_log('Admin handled update failed (' . get_class($exception) . ').');
            }
        }
        $back = inquiry_value($_POST, 'back');
        admin_redirect('/admin' . (preg_match('/^\?[A-Za-z0-9=&%+._-]*$/', $back) ? $back : '') . '#inquiry-' . $id);
    }

    if ($action === 'slides') {
        if ($method === 'POST') {
            // PHP empties $_POST when the body is over post_max_size.
            if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                admin_redirect('/admin/slides?error=too-large');
            }
            if (!$formTokenValid()) {
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/slides">다시 열기</a></p></main>', 419);
            }
            $error = admin_slide_action($pdo, inquiry_value($_POST, 'op'));
            admin_redirect('/admin/slides' . ($error !== '' ? '?error=' . $error : ''));
        }
        admin_slides_page($pdo, $formToken);
    }

    $status = (string)($_GET['status'] ?? 'open');
    if (!in_array($status, ['open', 'done', 'all'], true)) {
        $status = 'open';
    }
    $query = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 100);
    $pageNo = max(1, (int)($_GET['page'] ?? 1));
    try {
        $counts = $pdo->query('SELECT count(*) FILTER (WHERE handled_at IS NULL) AS open, count(*) FILTER (WHERE handled_at IS NOT NULL) AS done FROM business_inquiries')->fetch(\PDO::FETCH_ASSOC);
        $where = [];
        $params = [];
        if ($status === 'open') {
            $where[] = 'handled_at IS NULL';
        } elseif ($status === 'done') {
            $where[] = 'handled_at IS NOT NULL';
        }
        if ($query !== '') {
            $where[] = '(company ILIKE :q OR contact_name ILIKE :q OR email ILIKE :q OR subject ILIKE :q OR message ILIKE :q)';
            $params['q'] = '%' . addcslashes($query, '%_\\') . '%';
        }
        $statement = $pdo->prepare('SELECT id, inquiry_types, company, contact_name, email, phone, website, subject, message, handled_at, created_at FROM business_inquiries'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY created_at DESC LIMIT ' . (ADMIN_PAGE_SIZE + 1) . ' OFFSET ' . ($pageNo - 1) * ADMIN_PAGE_SIZE);
        $statement->execute($params);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Throwable $exception) {
        error_log('Admin list query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', '<main class="admin-shell"><h1>문의 목록을 불러오지 못했습니다.</h1></main>', 502);
    }
    $more = count($rows) > ADMIN_PAGE_SIZE;
    $rows = array_slice($rows, 0, ADMIN_PAGE_SIZE);
    $open = (int)$counts['open'];
    $done = (int)$counts['done'];
    $back = admin_query();
    ob_start();
    ?>
<?= admin_header('문의 관리', '/admin', $formToken) ?>
<main class="admin-shell">
<div class="admin-toolbar">
<nav class="admin-tabs" aria-label="상태">
<?php foreach (['open' => '미처리 ' . $open, 'done' => '처리 완료 ' . $done, 'all' => '전체 ' . ($open + $done)] as $key => $label): ?>
<a class="admin-chip<?= $status === $key ? ' is-active' : '' ?>" href="/admin<?= e(admin_query(['status' => $key, 'page' => ''])) ?>"<?= $status === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endforeach; ?>
</nav>
<form class="admin-search" method="get" action="/admin" role="search">
<input type="hidden" name="status" value="<?= e($status) ?>">
<input class="admin-input" type="search" name="q" value="<?= e($query) ?>" placeholder="회사, 담당자, 이메일, 제목, 내용 검색" aria-label="검색">
<button class="admin-button admin-button--outline" type="submit">검색</button>
</form>
</div>
<?php if (!$rows): ?>
<p class="admin-empty" role="status"><?= $query !== '' ? '검색 결과가 없습니다.' : ($status === 'open' ? '미처리 문의가 없습니다.' : '문의가 없습니다.') ?></p>
<?php else: ?>
<ol class="admin-list">
<?php foreach ($rows as $row): $handled = $row['handled_at'] !== null; ?>
<li class="admin-card admin-item<?= $handled ? ' is-handled' : '' ?>" id="inquiry-<?= (int)$row['id'] ?>">
<div class="admin-item-head">
<div class="admin-meta">
<?php foreach (explode(',', trim((string)$row['inquiry_types'], '{}')) as $type): ?>
<span class="admin-badge"><?= e(INQUIRY_TYPES[$type] ?? $type) ?></span>
<?php endforeach; ?>
<time datetime="<?= e($row['created_at']) ?>"><?= e(admin_time($row['created_at'])) ?> KST</time>
</div>
<form method="post" action="/admin/handle">
<input type="hidden" name="token" value="<?= e($formToken) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="done" value="<?= $handled ? '0' : '1' ?>"><input type="hidden" name="back" value="<?= e($back) ?>">
<button class="admin-button<?= $handled ? ' admin-button--outline' : '' ?>" type="submit"><?= $handled ? '처리 취소' : '처리 완료' ?></button>
</form>
</div>
<h2><?= e($row['subject']) ?></h2>
<dl class="admin-contact">
<div><dt>회사</dt><dd><?= e($row['company']) ?></dd></div>
<div><dt>담당자</dt><dd><?= e($row['contact_name']) ?></dd></div>
<div><dt>이메일</dt><dd><a href="mailto:<?= e($row['email']) ?>?subject=<?= rawurlencode('Re: ' . $row['subject']) ?>"><?= e($row['email']) ?></a></dd></div>
<?php if ($row['phone'] !== ''): ?><div><dt>연락처</dt><dd><?= e($row['phone']) ?></dd></div><?php endif; ?>
<?php if ($row['website'] !== ''): ?><div><dt>홈페이지</dt><dd><a href="<?= e($row['website']) ?>" rel="noopener noreferrer nofollow" target="_blank"><?= e($row['website']) ?></a></dd></div><?php endif; ?>
</dl>
<p class="admin-message"><?= nl2br(e($row['message']), false) ?></p>
<?php if ($handled): ?><p class="admin-handled">처리 완료 <?= e(admin_time($row['handled_at'])) ?> KST</p><?php endif; ?>
</li>
<?php endforeach; ?>
</ol>
<nav class="admin-pager" aria-label="페이지">
<?php if ($pageNo > 1): ?><a class="admin-button admin-button--outline" href="/admin<?= e(admin_query(['page' => $pageNo - 1 > 1 ? $pageNo - 1 : ''])) ?>">이전</a><?php endif; ?>
<?php if ($more): ?><a class="admin-button admin-button--outline" href="/admin<?= e(admin_query(['page' => $pageNo + 1])) ?>">다음</a><?php endif; ?>
</nav>
<?php endif; ?>
</main>
<?php
    admin_layout('문의 목록', ob_get_clean());
}
