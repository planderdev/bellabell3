<?php
declare(strict_types=1);

// Internal admin: /admin (home), /admin/inbox, /admin/login, /admin/logout (POST), /admin/handle (POST), /admin/slides, /admin/menu, /admin/sections, /admin/ingredients.
// Single shared password (ADMIN_PASSWORD_HASH) and a stateless signed cookie (APP_SECRET).
require_once __DIR__ . '/contact-handler.php';
require_once __DIR__ . '/research-slides.php';
require_once __DIR__ . '/admin-site.php';

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

// Assets are cached for an hour, so a new deploy could pair fresh markup with old styles. A per-deploy version forces a new copy.
// public/assets is not in the function bundle on Vercel, so the version cannot come from the files themselves.
function admin_asset(string $path): string
{
    $version = getenv('VERCEL_GIT_COMMIT_SHA') ?: getenv('VERCEL_DEPLOYMENT_ID') ?: (string)filemtime(__FILE__);
    return e($path . '?v=' . substr($version, 0, 12));
}

function admin_layout(string $title, string $body, int $status = 200, array $scripts = []): never
{
    http_response_code($status);
    echo '<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex, nofollow">'
        . '<title>' . e($title) . ' | 벨아벨팜운영관리대시보드</title>'
        . '<link rel="stylesheet" href="' . admin_asset('/assets/css/fonts.css') . '"><link rel="stylesheet" href="' . admin_asset('/assets/css/base.css') . '"><link rel="stylesheet" href="' . admin_asset('/assets/css/admin.css') . '">'
        . '<script defer src="' . admin_asset('/assets/js/admin-slides.js') . '"></script>'
        . implode('', array_map(fn (string $script): string => '<script defer src="' . admin_asset($script) . '"></script>', $scripts))
        . '</head><body class="admin-body">' . $body . (str_contains($body, 'data-admin-app') ? '</div></div>' : '') . '</body></html>';
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

function admin_icon(string $name): string
{
    static $icons = null;
    $icons ??= require __DIR__ . '/admin-icons.php';
    return '<svg class="admin-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($icons[$name] ?? '') . '</svg>';
}

function admin_open_inquiries(): ?int
{
    $pdo = db_connection();
    if (!$pdo instanceof \PDO) {
        return null;
    }
    try {
        return (int)$pdo->query('SELECT count(*) FROM business_inquiries WHERE handled_at IS NULL')->fetchColumn();
    } catch (\Throwable) {
        return null;
    }
}

// Rail group > labelled block > items. Page items open that page in /admin/sections.
function admin_navigation(): array
{
    $pages = [];
    foreach (site_sections() as $key => $page) {
        $pages[] = ['href' => '/admin/sections?page=' . $key, 'label' => $page['label'], 'icon' => 'page'];
    }
    return [
        'overview' => ['label' => "벨아벨팜\n운영관리", 'icon' => 'overview', 'href' => '/admin', 'blocks' => [
            ['label' => '개요', 'items' => [
                ['href' => '/admin', 'label' => '홈', 'icon' => 'home'],
                ['href' => '/admin/inbox', 'label' => '문의', 'icon' => 'inbox', 'count' => admin_open_inquiries()],
            ]],
        ]],
        'site' => ['label' => '사이트 관리', 'icon' => 'site', 'href' => '/admin/menu', 'blocks' => [
            ['label' => '구성', 'items' => [
                ['href' => '/admin/menu', 'label' => '메뉴 관리', 'icon' => 'menu'],
                ['href' => '/admin/products', 'label' => '상품 관리', 'icon' => 'package'],
                ['href' => '/admin/ingredients', 'label' => '원료 관리', 'icon' => 'leaf'],
                ['href' => '/admin/research', 'label' => '연구기술 자료', 'icon' => 'images'],
            ]],
            ['label' => '페이지 편집', 'items' => $pages],
            ['label' => '미디어', 'items' => [
                ['href' => '/admin/slides', 'label' => '연구·기술 슬라이드', 'icon' => 'images'],
                ['href' => '/admin/gallery', 'label' => '브랜드 활동 사진', 'icon' => 'images'],
            ]],
        ]],
    ];
}

// Opens the app frame; admin_layout() closes it.
function admin_header(string $title, string $current, string $formToken): string
{
    if ($current === '/admin/sections') {
        $current .= '?page=' . admin_section_selection()[0];
    }
    $navigation = admin_navigation();
    $activeGroup = array_key_first($navigation);
    foreach ($navigation as $key => $group) {
        foreach ($group['blocks'] as $block) {
            if (in_array($current, array_column($block['items'], 'href'), true)) {
                $activeGroup = $key;
            }
        }
    }
    $rail = '';
    foreach ($navigation as $key => $group) {
        $active = $key === $activeGroup;
        $rail .= '<a class="admin-rail-link' . ($active ? ' is-active' : '') . '" href="' . e($group['href']) . '" title="' . e(str_replace("\n", ' ', $group['label'])) . '" aria-label="' . e(str_replace("\n", ' ', $group['label'])) . '"' . ($active ? ' aria-current="true"' : '') . '>' . admin_icon($group['icon']) . '</a>';
    }
    $side = '';
    foreach ($navigation[$activeGroup]['blocks'] as $block) {
        $side .= '<p class="admin-side-label">' . e($block['label']) . '</p><ul class="admin-side-list">';
        foreach ($block['items'] as $item) {
            $active = $item['href'] === $current;
            $count = (int)($item['count'] ?? 0);
            $side .= '<li><a class="admin-side-link' . ($active ? ' is-active' : '') . '" href="' . e($item['href']) . '"' . ($active ? ' aria-current="page"' : '') . '>' . admin_icon($item['icon']) . '<span>' . e($item['label']) . '</span>' . ($count > 0 ? '<b class="admin-side-count">' . $count . '</b>' : '') . '</a></li>';
        }
        $side .= '</ul>';
    }
    return '<div class="admin-app" data-admin-app>'
        . '<nav class="admin-rail" aria-label="관리 영역"><a class="admin-rail-logo" href="/admin" aria-label="관리자 홈"><svg class="admin-rail-mark" width="24" height="15" viewBox="0 0 233 141" fill="currentColor" aria-hidden="true" focusable="false"><rect x="99.65" y="9.83" width="33.43" height="33.43" rx="7.88" ry="7.88" transform="translate(15.31 90.06) rotate(-45)"/><path d="M178.38,66.33l50.44,51.3c8.4,8.54,2.35,22.97-9.63,22.97H13.53c-11.98,0-18.03-14.43-9.63-22.97l50.44-51.3-.2.2c3.11-3.38,6.15-6.95,12.38-6.95,12.45,0,12.45,13.5,24.91,13.5s12.46-13.5,24.92-13.5,12.45,13.5,24.91,13.5,12.46-13.5,24.93-13.5c6.15,0,9.13,3.42,12.2,6.75Z"/></svg></a>' . $rail . '</nav>'
        . '<aside class="admin-side" id="admin-side"><div class="admin-side-head"><strong>' . nl2br(e($navigation[$activeGroup]['label']), false) . '</strong><button class="admin-side-toggle" type="button" data-admin-collapse aria-controls="admin-side" aria-expanded="true" aria-label="메뉴 접기">' . admin_icon('panel') . '</button></div>'
        . '<nav class="admin-side-nav" aria-label="관리 메뉴">' . $side . '</nav>'
        . '<div class="admin-side-foot"><a class="admin-side-link" href="/" target="_blank" rel="noopener">' . admin_icon('external') . '<span>홈페이지 보기</span></a>'
        . '<form method="post" action="/admin/logout"><input type="hidden" name="token" value="' . e($formToken) . '"><button class="admin-side-link" type="submit">' . admin_icon('logout') . '<span>로그아웃</span></button></form></div></aside>'
        . '<div class="admin-main"><header class="admin-topbar"><button class="admin-side-toggle admin-side-open" type="button" data-admin-collapse aria-controls="admin-side" aria-expanded="false" aria-label="메뉴 펼치기">' . admin_icon('panel') . '</button><h1>' . e($title) . '</h1></header>';
}

function admin_home_page(\PDO $pdo, string $formToken): never
{
    $count = function (string $sql) use ($pdo): ?int {
        try {
            return (int)$pdo->query($sql)->fetchColumn();
        } catch (\Throwable $exception) {
            error_log('Admin home count failed (' . get_class($exception) . ').');
            return null;
        }
    };
    $recent = [];
    try {
        $recent = $pdo->query('SELECT id, company, subject, created_at FROM business_inquiries WHERE handled_at IS NULL ORDER BY created_at DESC LIMIT 5')->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Throwable $exception) {
        error_log('Admin home inquiries failed (' . get_class($exception) . ').');
    }
    $cards = [
        ['/admin/inbox', 'inbox', '미처리 문의', $count('SELECT count(*) FROM business_inquiries WHERE handled_at IS NULL')],
        ['/admin/sections', 'page', '수정한 문구·이미지', $count('SELECT count(*) FROM site_content')],
        ['/admin/menu', 'menu', '메뉴 항목', $count('SELECT count(*) FROM site_menu_items')],
        ['/admin/slides', 'images', '연구·기술 슬라이드', $count('SELECT count(*) FROM research_slides')],
    ];
    ob_start();
    ?>
<main class="admin-shell">
<ul class="admin-stats">
<?php foreach ($cards as [$href, $icon, $label, $value]): ?>
<li><a class="admin-card admin-stat" href="<?= e($href) ?>"><?= admin_icon($icon) ?><span><?= e($label) ?></span><strong><?= $value === null ? '-' : $value ?></strong></a></li>
<?php endforeach; ?>
</ul>
<section class="admin-card admin-recent">
<div class="admin-section-head"><h2>최근 미처리 문의</h2><a class="admin-button admin-button--outline" href="/admin/inbox">전체 보기</a></div>
<?php if (!$recent): ?>
<p class="admin-help" role="status">미처리 문의가 없습니다.</p>
<?php else: ?>
<ul class="admin-recent-list">
<?php foreach ($recent as $row): ?>
<li><a href="/admin/inbox#inquiry-<?= (int)$row['id'] ?>"><strong><?= e($row['subject']) ?></strong><span><?= e($row['company']) ?></span><time datetime="<?= e($row['created_at']) ?>"><?= e(admin_time($row['created_at'])) ?></time></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</section>
</main>
<?php
    admin_layout('홈', admin_header('홈', '/admin', $formToken) . ob_get_clean());
}

function admin_slide_messages(): array
{
    return [
        'last-visible' => '메인에 슬라이드가 최소 1장은 나와야 합니다.',
        'no-file' => '이미지 파일을 선택해 주세요.',
        'too-large' => '이미지가 너무 큽니다. 3MB 이하로 줄여서 올려 주세요.',
        'bad-type' => 'JPG, PNG, WEBP 이미지만 올릴 수 있습니다.',
        'too-many' => '슬라이드는 최대 ' . RESEARCH_SLIDE_MAX_COUNT . '장까지 올릴 수 있습니다.',
        'failed' => '저장하지 못했습니다. 다시 시도해 주세요.',
        'last-one' => '슬라이드는 최소 1장이 있어야 합니다. 새 이미지를 먼저 추가해 주세요.',
    ];
}

// POST /admin/slides. Returns an error code from admin_slide_messages(), or '' on success. $created receives a new slide's id.
function admin_slide_action(\PDO $pdo, string $op, ?int &$created = null): string
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
            $created = research_slide_store($pdo, $image, mb_substr(inquiry_value($_POST, 'alt'), 0, 200));
        } elseif ($op === 'alt') {
            $pdo->prepare('UPDATE research_slides SET alt = :alt WHERE id = :id')->execute(['alt' => mb_substr(inquiry_value($_POST, 'alt'), 0, 200), 'id' => $id]);
        } elseif ($op === 'up' || $op === 'down') {
            research_slide_move($pdo, $id, $op === 'up' ? -1 : 1);
        } elseif ($op === 'order') {
            research_slides_order($pdo, array_map('intval', array_filter(explode(',', inquiry_value($_POST, 'ids')), 'ctype_digit')));
        } elseif ($op === 'visibility') {
            $hide = inquiry_value($_POST, 'hide') === '1';
            $visible = array_filter(research_slides_uploaded($pdo), fn (array $row): bool => !(int)$row['hidden_home'] && (int)$row['id'] !== $id);
            if ($hide && !$visible) {
                return 'last-visible';
            }
            $pdo->prepare('UPDATE research_slides SET hidden_home = :hidden WHERE id = :id')->execute(['hidden' => $hide ? 'true' : 'false', 'id' => $id]);
        } elseif ($op === 'delete') {
            if (count(research_slides_uploaded($pdo)) <= 1) {
                return 'last-one';
            }
            $pdo->prepare('DELETE FROM research_slides WHERE id = :id')->execute(['id' => $id]);
        }
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin slide action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_slide_card(array $slide, string $formToken, int $number, int $total): string
{
    $id = (int)$slide['id'];
    $hidden = fn (string $op): string => admin_hidden($formToken, ['op' => $op, 'id' => $id]);
    $isHidden = (int)($slide['hidden_home'] ?? 0) === 1;
    return '<li class="admin-card admin-slide-card' . ($isHidden ? ' is-hidden-home' : '') . '" data-slide-id="' . $id . '">'
        . '<div class="admin-slide-top"><button class="admin-slide-handle" type="button" title="끌어서 순서 변경" aria-label="' . $number . '번 슬라이드, 끌어서 순서 변경">' . admin_icon('grip') . '</button><span class="admin-slide-number">' . $number . '</span>'
        . '<form method="post" action="/admin/slides" data-slide-form data-confirm="이 이미지를 삭제할까요?">' . $hidden('delete') . '<button class="admin-slide-delete" type="submit">삭제</button></form></div>'
        . '<div class="admin-slide-frame"><img src="' . e(research_slide_src($slide)) . '" alt="" width="' . (int)$slide['width'] . '" height="' . (int)$slide['height'] . '" loading="lazy" draggable="false"></div>'
        . '<form class="admin-slide-alt" method="post" action="/admin/slides" data-slide-form data-slide-alt>' . $hidden('alt') . '<label class="admin-label" for="slide-alt-' . $id . '">이미지 설명</label><input class="admin-input" type="text" id="slide-alt-' . $id . '" name="alt" value="' . e((string)$slide['alt']) . '" maxlength="200"></form>'
        . '<form class="admin-slide-visibility" method="post" action="/admin/slides">' . admin_hidden($formToken, ['op' => 'visibility', 'id' => $id, 'hide' => $isHidden ? '0' : '1']) . '<span>' . ($isHidden ? '메인에서 숨김' : '메인에 표시 중') . '</span><button class="admin-button admin-button--outline" type="submit">' . ($isHidden ? '다시 보이기' : '숨기기') . '</button></form>'
        . '<div class="admin-slide-moves">'
        . '<form method="post" action="/admin/slides" data-slide-form>' . $hidden('up') . '<button class="admin-button admin-button--outline" type="submit" data-move="up"' . ($number === 1 ? ' disabled' : '') . '>앞으로</button></form>'
        . '<form method="post" action="/admin/slides" data-slide-form>' . $hidden('down') . '<button class="admin-button admin-button--outline" type="submit" data-move="down"' . ($number === $total ? ' disabled' : '') . '>뒤로</button></form>'
        . '</div></li>';
}

function admin_slides_page(\PDO $pdo, string $formToken): never
{
    try {
        research_slides_seed($pdo, site_defaults('ko')['research_slides']);
        $slides = research_slides_uploaded($pdo);
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin slides query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('연구·기술 슬라이드', '/admin/slides', $formToken) . '<main class="admin-shell"><h1>슬라이드 목록을 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>이 최신으로 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    $error = admin_slide_messages()[(string)($_GET['error'] ?? '')] ?? '';
    $total = count($slides);
    ob_start();
    ?>
<main class="admin-shell">
<?php if ($error !== ''): ?><p class="admin-error" role="alert"><?= e($error) ?></p><?php endif; ?>
<p class="admin-slide-status" id="slide-status" role="status" aria-live="polite" hidden></p>
<form class="admin-slide-drop" method="post" action="/admin/slides" enctype="multipart/form-data" data-slide-drop>
<?= admin_hidden($formToken, ['op' => 'upload']) ?>
<label for="slide-image"><?= admin_icon('upload') ?><strong>이미지 추가</strong><span>여기로 끌어다 놓거나 눌러서 선택하세요. 여러 장도 됩니다. JPG, PNG, WEBP</span></label>
<input type="file" id="slide-image" name="image" accept="image/jpeg,image/png,image/webp,image/heic" multiple>
<noscript><button class="admin-button" type="submit">올리기</button></noscript>
</form>
<div class="admin-slide-head"><h2>슬라이드 목록 <span id="slide-count"><?= $total ?></span>장</h2><p class="admin-help">끌어서 순서를 바꾸면 홈페이지에 바로 반영됩니다. 왼쪽 위부터 차례로 나옵니다.</p></div>
<ol class="admin-slide-board" id="slide-board">
<?php foreach ($slides as $index => $slide): ?>
<?= admin_slide_card($slide, $formToken, $index + 1, $total) ?>
<?php endforeach; ?>
</ol>
</main>
<?php
    admin_layout('연구·기술 슬라이드', admin_header('연구·기술 슬라이드', '/admin/slides', $formToken) . ob_get_clean(), 200, ['/assets/js/vendor/Sortable.min.js', '/assets/js/admin-slide-board.js']);
}

function handle_admin_request(string $action): void
{
    header('Cache-Control: no-store, max-age=0');
    header('X-Robots-Tag: noindex, nofollow');
    $isHttps = ($_SERVER['HTTPS'] ?? 'off') !== 'off' || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $cookieOptions = ['path' => '/admin', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Strict'];
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($action, ['', 'login', 'logout', 'handle', 'inbox', 'slides', 'menu', 'sections', 'ingredients', 'research', 'gallery', 'products'], true)) {
        admin_layout('찾을 수 없음', '<main class="admin-shell"><h1>페이지를 찾을 수 없습니다.</h1><p><a href="/admin">관리자 홈으로</a></p></main>', 404);
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
            admin_redirect('/admin/inbox');
        }
        if (!$formTokenValid()) {
            admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/inbox">다시 열기</a></p></main>', 419);
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
        admin_redirect('/admin/inbox' . (preg_match('/^\?[A-Za-z0-9=&%+._-]*$/', $back) ? $back : '') . '#inquiry-' . $id);
    }

    if ($action === 'slides') {
        if ($method === 'POST') {
            $wantsJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
            $respond = function (string $error, int $status = 200, array $extra = []) use ($wantsJson): never {
                if (!$wantsJson) {
                    admin_redirect('/admin/slides' . ($error !== '' ? '?error=' . $error : ''));
                }
                http_response_code($status);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => $error === '' ? null : (admin_slide_messages()[$error] ?? $error)] + $extra, JSON_UNESCAPED_UNICODE);
                exit;
            };
            // PHP empties $_POST when the body is over post_max_size.
            if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                $respond('too-large', 413);
            }
            if (!$formTokenValid()) {
                if ($wantsJson) {
                    http_response_code(419);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => '세션이 만료되었습니다. 새로고침해 주세요.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/slides">다시 열기</a></p></main>', 419);
            }
            $created = null;
            $error = admin_slide_action($pdo, inquiry_value($_POST, 'op'), $created);
            $extra = [];
            if ($created !== null && $wantsJson) {
                $rows = research_slides_uploaded($pdo);
                foreach ($rows as $row) {
                    if ((int)$row['id'] === $created) {
                        $extra['html'] = admin_slide_card($row, $formToken, count($rows), count($rows));
                    }
                }
            }
            $respond($error, $error === '' ? 200 : 422, $extra);
        }
        admin_slides_page($pdo, $formToken);
    }

    if ($action === 'menu') {
        if ($method === 'POST') {
            if (!$formTokenValid()) {
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/menu">다시 열기</a></p></main>', 419);
            }
            $error = admin_menu_action($pdo, inquiry_value($_POST, 'op'));
            admin_redirect('/admin/menu' . ($error !== '' ? '?error=' . $error : '?saved=1'));
        }
        admin_menu_page($pdo, $formToken);
    }

    if ($action === 'ingredients') {
        if ($method === 'POST') {
            $wantsJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
            if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                admin_redirect('/admin/ingredients?error=too-large');
            }
            if (!$formTokenValid()) {
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/ingredients">다시 열기</a></p></main>', 419);
            }
            $focus = 0;
            $error = admin_ingredient_action($pdo, inquiry_value($_POST, 'op'), $focus);
            if ($wantsJson) {
                http_response_code($error === '' ? 200 : 422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => $error === '' ? null : (admin_site_messages()[$error] ?? $error)], JSON_UNESCAPED_UNICODE);
                exit;
            }
            admin_redirect('/admin/ingredients' . ($error !== '' ? '?error=' . $error : '?saved=1') . ($focus > 0 ? '#item-' . $focus : ''));
        }
        admin_ingredients_page($pdo, $formToken);
    }

    if ($action === 'products') {
        if ($method === 'POST') {
            $wantsJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
            if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                admin_redirect('/admin/products?error=too-large');
            }
            if (!$formTokenValid()) {
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/products">다시 열기</a></p></main>', 419);
            }
            $focus = 0;
            $category = '';
            $error = admin_product_action($pdo, inquiry_value($_POST, 'op'), $focus, $category);
            if ($wantsJson) {
                http_response_code($error === '' ? 200 : 422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => $error === '' ? null : (admin_site_messages()[$error] ?? $error)], JSON_UNESCAPED_UNICODE);
                exit;
            }
            admin_redirect('/admin/products?' . http_build_query(['category' => $category] + ($error !== '' ? ['error' => $error] : ['saved' => 1])) . ($focus > 0 ? '#item-' . $focus : ''));
        }
        admin_products_page($pdo, $formToken);
    }

    if ($action === 'gallery') {
        if ($method === 'POST') {
            $wantsJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
            if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                admin_redirect('/admin/gallery?error=too-large');
            }
            if (!$formTokenValid()) {
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/gallery">다시 열기</a></p></main>', 419);
            }
            $error = admin_gallery_action($pdo, inquiry_value($_POST, 'op'));
            if ($wantsJson) {
                http_response_code($error === '' ? 200 : 422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => $error === '' ? null : (admin_site_messages()[$error] ?? $error)], JSON_UNESCAPED_UNICODE);
                exit;
            }
            admin_redirect('/admin/gallery' . ($error !== '' ? '?error=' . $error : '?saved=1'));
        }
        admin_gallery_page($pdo, $formToken);
    }

    if ($action === 'research') {
        if ($method === 'POST') {
            $wantsJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
            if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                admin_redirect('/admin/research?error=too-large');
            }
            if (!$formTokenValid()) {
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/research">다시 열기</a></p></main>', 419);
            }
            $focus = 0;
            $error = admin_research_action($pdo, inquiry_value($_POST, 'op'), $focus);
            if ($wantsJson) {
                http_response_code($error === '' ? 200 : 422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => $error === '' ? null : (admin_site_messages()[$error] ?? $error)], JSON_UNESCAPED_UNICODE);
                exit;
            }
            admin_redirect('/admin/research' . ($error !== '' ? '?error=' . $error : '?saved=1') . ($focus > 0 ? '#item-' . $focus : ''));
        }
        admin_research_page($pdo, $formToken);
    }

    if ($action === 'sections') {
        if ($method === 'POST') {
            if (!$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                admin_redirect('/admin/sections?error=too-large');
            }
            if (!$formTokenValid()) {
                admin_layout('세션 만료', '<main class="admin-shell"><h1>세션이 만료되었습니다.</h1><p><a href="/admin/sections">다시 열기</a></p></main>', 419);
            }
            [$pageKey, $sectionKey, $lang] = admin_section_selection();
            $error = admin_sections_action($pdo, inquiry_value($_POST, 'op'), site_sections()[$pageKey]['sections'][$sectionKey]['fields'], $lang);
            admin_redirect(admin_section_url($pageKey, $sectionKey, $lang, $error !== '' ? '&error=' . $error : '&saved=1'));
        }
        admin_sections_page($pdo, $formToken);
    }

    if ($action === '') {
        admin_home_page($pdo, $formToken);
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
<?= admin_header('문의', '/admin/inbox', $formToken) ?>
<main class="admin-shell">
<div class="admin-toolbar">
<nav class="admin-tabs" aria-label="상태">
<?php foreach (['open' => '미처리 ' . $open, 'done' => '처리 완료 ' . $done, 'all' => '전체 ' . ($open + $done)] as $key => $label): ?>
<a class="admin-chip<?= $status === $key ? ' is-active' : '' ?>" href="/admin/inbox<?= e(admin_query(['status' => $key, 'page' => ''])) ?>"<?= $status === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endforeach; ?>
</nav>
<form class="admin-search" method="get" action="/admin/inbox" role="search">
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
<?php if ($pageNo > 1): ?><a class="admin-button admin-button--outline" href="/admin/inbox<?= e(admin_query(['page' => $pageNo - 1 > 1 ? $pageNo - 1 : ''])) ?>">이전</a><?php endif; ?>
<?php if ($more): ?><a class="admin-button admin-button--outline" href="/admin/inbox<?= e(admin_query(['page' => $pageNo + 1])) ?>">다음</a><?php endif; ?>
</nav>
<?php endif; ?>
</main>
<?php
    admin_layout('문의 목록', ob_get_clean());
}
