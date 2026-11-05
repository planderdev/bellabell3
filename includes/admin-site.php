<?php
declare(strict_types=1);

// /admin/menu, /admin/sections and /admin/ingredients: header menu items, per-section text and images, and ingredients.
require_once __DIR__ . '/site.php';

const ADMIN_TEXT_MAX = 5000;

function admin_site_messages(): array
{
    return admin_slide_messages() + [
        'settings' => '설정을 저장하지 못했습니다. 잠시 후 다시 시도해 주세요.',
        'bad-link' => '주소는 /brand, #research, https://... 형식으로 적어 주세요.',
        'no-label' => '한국어 메뉴 이름을 입력해 주세요.',
        'no-name' => '한국어 원료 이름을 입력해 주세요.',
        'no-title' => '한국어 제목을 입력해 주세요.',
        'no-product' => '한국어 상품 이름을 입력해 주세요.',
        'last-gallery' => '사진은 최소 1장이 있어야 합니다. 새 사진을 먼저 올려 주세요.',
        'gallery-full' => '사진은 최대 30장까지 올릴 수 있습니다.',
        'last-research' => '자료는 최소 1개가 있어야 합니다. 새 자료를 먼저 추가해 주세요.',
        'last-ingredient' => '원료는 최소 1개가 있어야 합니다. 새 원료를 먼저 추가해 주세요.',
    ];
}

function admin_notice(): string
{
    $error = admin_site_messages()[(string)($_GET['error'] ?? '')] ?? '';
    if ($error !== '') {
        return '<p class="admin-error" role="alert">' . e($error) . '</p>';
    }
    return isset($_GET['saved']) ? '<p class="admin-saved" role="status">저장했습니다. 홈페이지에 바로 반영됩니다.</p>' : '';
}

function admin_hidden(string $formToken, array $values): string
{
    $html = '<input type="hidden" name="token" value="' . e($formToken) . '">';
    foreach ($values as $name => $value) {
        $html .= '<input type="hidden" name="' . e($name) . '" value="' . e((string)$value) . '">';
    }
    return $html;
}

function admin_link_valid(string $href): bool
{
    return preg_match(SITE_LINK_PATTERN, $href) === 1;
}

// ---- Menu ----

// The first visit copies the current menu into the table, so editing starts from what the site shows.
function site_menu_seed(\PDO $pdo): void
{
    if ((int)$pdo->query('SELECT count(*) FROM site_menu_items')->fetchColumn() > 0) {
        return;
    }
    $menus = array_map(fn (string $code): array => site_defaults($code)['menu'], array_keys(LOCALES));
    $insert = $pdo->prepare('INSERT INTO site_menu_items (parent_id, label_ko, label_en, label_zh, href, position) VALUES (:parent, :ko, :en, :zh, :href, :position) RETURNING id');
    $pdo->beginTransaction();
    foreach ($menus[0] as $index => $item) {
        $insert->execute(['parent' => null, 'ko' => $item['label'], 'en' => $menus[1][$index]['label'], 'zh' => $menus[2][$index]['label'], 'href' => $item['href'], 'position' => $index + 1]);
        $parent = (int)$insert->fetchColumn();
        foreach ($item['children'] as $childIndex => $child) {
            $insert->execute(['parent' => $parent, 'ko' => $child['label'], 'en' => $menus[1][$index]['children'][$childIndex]['label'], 'zh' => $menus[2][$index]['children'][$childIndex]['label'], 'href' => $child['href'], 'position' => $childIndex + 1]);
        }
    }
    $pdo->commit();
}

function site_menu_move(\PDO $pdo, int $id, int $direction): void
{
    $statement = $pdo->prepare('SELECT id FROM site_menu_items WHERE parent_id IS NOT DISTINCT FROM (SELECT parent_id FROM site_menu_items WHERE id = :id) ORDER BY position, id');
    $statement->execute(['id' => $id]);
    $ids = array_map('intval', $statement->fetchAll(\PDO::FETCH_COLUMN));
    $from = array_search($id, $ids, true);
    if ($from === false || !isset($ids[$from + $direction])) {
        return;
    }
    [$ids[$from], $ids[$from + $direction]] = [$ids[$from + $direction], $ids[$from]];
    $pdo->beginTransaction();
    $update = $pdo->prepare('UPDATE site_menu_items SET position = :position WHERE id = :id');
    foreach ($ids as $index => $itemId) {
        $update->execute(['position' => $index + 1, 'id' => $itemId]);
    }
    $pdo->commit();
}

// POST /admin/menu. Returns an error code from admin_site_messages(), or '' on success.
function admin_menu_action(\PDO $pdo, string $op): string
{
    $id = (int)inquiry_value($_POST, 'id');
    $values = [
        'ko' => mb_substr(trim(inquiry_value($_POST, 'label_ko')), 0, 60),
        'en' => mb_substr(trim(inquiry_value($_POST, 'label_en')), 0, 60),
        'zh' => mb_substr(trim(inquiry_value($_POST, 'label_zh')), 0, 60),
        'href' => mb_substr(trim(inquiry_value($_POST, 'href')), 0, 500),
    ];
    try {
        if ($op === 'add' || $op === 'save') {
            if ($values['ko'] === '') {
                return 'no-label';
            }
            if (!admin_link_valid($values['href'])) {
                return 'bad-link';
            }
        }
        if ($op === 'add') {
            $parent = (int)inquiry_value($_POST, 'parent_id');
            $statement = $pdo->prepare('INSERT INTO site_menu_items (parent_id, label_ko, label_en, label_zh, href, position) VALUES (:parent, :ko, :en, :zh, :href, (SELECT coalesce(max(position), 0) + 1 FROM site_menu_items))');
            $statement->execute($values + ['parent' => $parent > 0 ? $parent : null]);
        } elseif ($op === 'save') {
            $statement = $pdo->prepare('UPDATE site_menu_items SET label_ko = :ko, label_en = :en, label_zh = :zh, href = :href, visible = :visible WHERE id = :id');
            $statement->execute($values + ['visible' => inquiry_value($_POST, 'visible') === '1' ? 'true' : 'false', 'id' => $id]);
        } elseif ($op === 'up' || $op === 'down') {
            site_menu_move($pdo, $id, $op === 'up' ? -1 : 1);
        } elseif ($op === 'delete') {
            $pdo->prepare('DELETE FROM site_menu_items WHERE id = :id')->execute(['id' => $id]);
        } elseif ($op === 'reset') {
            $pdo->exec('DELETE FROM site_menu_items');
        }
    } catch (\Throwable $exception) {
        error_log('Admin menu action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_menu_row(array $row, string $formToken, bool $first, bool $last): string
{
    $id = (int)$row['id'];
    $fieldId = 'menu-' . $id;
    $html = '<form class="admin-menu-fields" method="post" action="/admin/menu">' . admin_hidden($formToken, ['op' => 'save', 'id' => $id]);
    foreach (['label_ko' => '한국어', 'label_en' => '영문', 'label_zh' => '중문', 'href' => '주소'] as $name => $label) {
        $html .= '<div><label class="admin-label" for="' . $fieldId . '-' . $name . '">' . $label . '</label><input class="admin-input" type="text" id="' . $fieldId . '-' . $name . '" name="' . $name . '" value="' . e((string)$row[$name]) . '"' . ($name === 'label_ko' || $name === 'href' ? ' required' : '') . '></div>';
    }
    $html .= '<label class="admin-check"><input type="checkbox" name="visible" value="1"' . ($row['visible'] ? ' checked' : '') . '> 사이트에 표시</label>'
        . '<button class="admin-button" type="submit">저장</button></form><div class="admin-slide-actions">';
    foreach (['up' => ['위로', $first], 'down' => ['아래로', $last]] as $op => [$label, $disabled]) {
        $html .= '<form method="post" action="/admin/menu">' . admin_hidden($formToken, ['op' => $op, 'id' => $id]) . '<button class="admin-button admin-button--outline" type="submit"' . ($disabled ? ' disabled' : '') . '>' . $label . '</button></form>';
    }
    return $html . '<form method="post" action="/admin/menu" data-confirm="이 메뉴를 삭제할까요? 하위 메뉴도 함께 삭제됩니다.">' . admin_hidden($formToken, ['op' => 'delete', 'id' => $id]) . '<button class="admin-button admin-button--outline" type="submit">삭제</button></form></div>';
}

function admin_menu_page(\PDO $pdo, string $formToken): never
{
    try {
        site_menu_seed($pdo);
        $rows = site_menu_rows($pdo);
    } catch (\Throwable $exception) {
        error_log('Admin menu query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('메뉴 관리', '/admin/menu', $formToken) . '<main class="admin-shell"><h1>메뉴를 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>의 <code>site_menu_items</code> 테이블이 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    $top = array_values(array_filter($rows, fn (array $row): bool => $row['parent_id'] === null));
    ob_start();
    ?>
<main class="admin-shell">
<?= admin_notice() ?>
<p class="admin-help">헤더와 전체 메뉴에 나옵니다. 영문·중문을 비워 두면 한국어가 나옵니다. 주소는 <code>/brand</code>, <code>/brand#awoo</code>, <code>#research</code>, <code>https://...</code> 형식입니다.</p>
<ol class="admin-list">
<?php foreach ($top as $index => $row): $children = array_values(array_filter($rows, fn (array $child): bool => (int)$child['parent_id'] === (int)$row['id'])); ?>
<li class="admin-card admin-menu-item">
<?= admin_menu_row($row, $formToken, $index === 0, $index === count($top) - 1) ?>
<?php if ($children): ?>
<ol class="admin-menu-children">
<?php foreach ($children as $childIndex => $child): ?>
<li><?= admin_menu_row($child, $formToken, $childIndex === 0, $childIndex === count($children) - 1) ?></li>
<?php endforeach; ?>
</ol>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ol>
<form class="admin-card admin-menu-add" method="post" action="/admin/menu">
<?= admin_hidden($formToken, ['op' => 'add']) ?>
<h2>메뉴 추가</h2>
<div class="admin-menu-fields">
<div><label class="admin-label" for="add-parent">위치</label><select class="admin-input" id="add-parent" name="parent_id"><option value="0">최상위 메뉴</option><?php foreach ($top as $row): ?><option value="<?= (int)$row['id'] ?>"><?= e((string)$row['label_ko']) ?> 아래</option><?php endforeach; ?></select></div>
<div><label class="admin-label" for="add-ko">한국어</label><input class="admin-input" id="add-ko" name="label_ko" required></div>
<div><label class="admin-label" for="add-en">영문</label><input class="admin-input" id="add-en" name="label_en"></div>
<div><label class="admin-label" for="add-zh">중문</label><input class="admin-input" id="add-zh" name="label_zh"></div>
<div><label class="admin-label" for="add-href">주소</label><input class="admin-input" id="add-href" name="href" required></div>
<button class="admin-button" type="submit">추가</button>
</div>
</form>
<form class="admin-menu-reset" method="post" action="/admin/menu" data-confirm="지금 메뉴를 지우고 기본 메뉴로 되돌릴까요?"><?= admin_hidden($formToken, ['op' => 'reset']) ?><button class="admin-button admin-button--outline" type="submit">기본 메뉴로 되돌리기</button></form>
</main>
<?php
    admin_layout('메뉴 관리', admin_header('메뉴 관리', '/admin/menu', $formToken) . ob_get_clean());
}

// ---- Ingredients ----

// The first visit copies what /ingredients shows into the table, keeping edits made before this page existed.
function ingredient_items_seed(\PDO $pdo): void
{
    if ((int)$pdo->query('SELECT count(*) FROM ingredient_items')->fetchColumn() > 0) {
        return;
    }
    $current = locale();
    $sets = [];
    foreach (array_keys(LOCALES) as $code) {
        locale($code);
        $sets[$code] = site_content()['ingredients'];
    }
    locale($current);
    $insert = $pdo->prepare('INSERT INTO ingredient_items (name_ko, name_en, name_zh, body_ko, body_en, body_zh, image, icon, position) VALUES (:nk, :ne, :nz, :bk, :be, :bz, :image, :icon, :position)');
    $pdo->beginTransaction();
    foreach ($sets['ko'] as $index => $item) {
        $insert->execute([
            'nk' => $item['name'], 'ne' => $sets['en'][$index]['name'], 'nz' => $sets['zh'][$index]['name'],
            'bk' => $item['body'], 'be' => $sets['en'][$index]['body'], 'bz' => $sets['zh'][$index]['body'],
            'image' => $item['image'], 'icon' => $item['icon'], 'position' => $index + 1,
        ]);
    }
    $pdo->commit();
}

function ingredient_image_value(\PDO $pdo, int $id): string
{
    $statement = $pdo->prepare('SELECT image FROM ingredient_items WHERE id = :id');
    $statement->execute(['id' => $id]);
    return (string)$statement->fetchColumn();
}

// Uploaded images are removed with their ingredient; built-in files under /assets stay.
function ingredient_image_discard(\PDO $pdo, string $value): void
{
    if (preg_match('~^/media/site/(\d+)$~', $value, $match)) {
        $pdo->prepare('DELETE FROM site_images WHERE id = :id')->execute(['id' => (int)$match[1]]);
    }
}

// POST /admin/ingredients. Returns an error code from admin_site_messages(), or '' on success. $focus is the item to scroll back to.
function admin_ingredient_action(\PDO $pdo, string $op, int &$focus): string
{
    $id = (int)inquiry_value($_POST, 'id');
    $focus = $id;
    $name = fn (string $key): string => mb_substr(trim(inquiry_value($_POST, $key)), 0, 80);
    $icon = function (): string {
        $key = inquiry_value($_POST, 'icon');
        return isset(site_ingredient_icons()[$key]) ? $key : '';
    };
    $body = function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? mb_substr(trim(str_replace("\r\n", "\n", $value)), 0, ADMIN_TEXT_MAX) : '';
    };
    try {
        if ($op === 'add') {
            if ($name('name_ko') === '') {
                return 'no-name';
            }
            $statement = $pdo->prepare('INSERT INTO ingredient_items (name_ko, name_en, name_zh, icon, position) VALUES (:ko, :en, :zh, :icon, (SELECT coalesce(max(position), 0) + 1 FROM ingredient_items)) RETURNING id');
            $statement->execute(['ko' => $name('name_ko'), 'en' => $name('name_en'), 'zh' => $name('name_zh'), 'icon' => $icon()]);
            $focus = (int)$statement->fetchColumn();
        } elseif ($op === 'save') {
            if ($name('name_ko') === '') {
                return 'no-name';
            }
            $pdo->prepare('UPDATE ingredient_items SET name_ko = :nk, name_en = :ne, name_zh = :nz, body_ko = :bk, body_en = :be, body_zh = :bz, icon = :icon WHERE id = :id')->execute([
                'nk' => $name('name_ko'), 'ne' => $name('name_en'), 'nz' => $name('name_zh'),
                'bk' => $body('body_ko'), 'be' => $body('body_en'), 'bz' => $body('body_zh'), 'icon' => $icon(), 'id' => $id,
            ]);
        } elseif ($op === 'image') {
            $image = research_slide_read_upload($_FILES['image'] ?? []);
            if (is_string($image)) {
                return $image;
            }
            [$bytes, $mime, $width, $height] = $image;
            $pdo->beginTransaction();
            ingredient_image_discard($pdo, ingredient_image_value($pdo, $id));
            $statement = $pdo->prepare('INSERT INTO site_images (image, mime, width, height) VALUES (:image, :mime, :width, :height) RETURNING id');
            $statement->bindValue('image', $bytes, \PDO::PARAM_LOB);
            $statement->bindValue('mime', $mime);
            $statement->bindValue('width', $width, \PDO::PARAM_INT);
            $statement->bindValue('height', $height, \PDO::PARAM_INT);
            $statement->execute();
            $pdo->prepare('UPDATE ingredient_items SET image = :image WHERE id = :id')->execute(['image' => '/media/site/' . (int)$statement->fetchColumn(), 'id' => $id]);
            $pdo->commit();
        } elseif ($op === 'image-remove') {
            ingredient_image_discard($pdo, ingredient_image_value($pdo, $id));
            $pdo->prepare("UPDATE ingredient_items SET image = '' WHERE id = :id")->execute(['id' => $id]);
        } elseif ($op === 'delete') {
            if ((int)$pdo->query('SELECT count(*) FROM ingredient_items')->fetchColumn() <= 1) {
                return 'last-ingredient';
            }
            ingredient_image_discard($pdo, ingredient_image_value($pdo, $id));
            $pdo->prepare('DELETE FROM ingredient_items WHERE id = :id')->execute(['id' => $id]);
            $focus = 0;
        } elseif ($op === 'order') {
            $ids = array_map('intval', array_filter(explode(',', inquiry_value($_POST, 'ids')), 'ctype_digit'));
            $statement = $pdo->prepare('UPDATE ingredient_items SET position = :position WHERE id = :id');
            $pdo->beginTransaction();
            foreach (array_values($ids) as $index => $itemId) {
                $statement->execute(['position' => $index + 1, 'id' => $itemId]);
            }
            $pdo->commit();
        }
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin ingredient action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_ingredient_icon_select(string $id, string $selected): string
{
    $options = '<option value="">아이콘 없음</option>';
    foreach (site_ingredient_icons() as $key => $icon) {
        $options .= '<option value="' . e($key) . '"' . ($key === $selected ? ' selected' : '') . '>' . e($icon['label']) . '</option>';
    }
    return '<select class="admin-input" id="' . e($id) . '" name="icon">' . $options . '</select>';
}

function admin_ingredients_page(\PDO $pdo, string $formToken): never
{
    try {
        ingredient_items_seed($pdo);
        $rows = site_ingredient_rows($pdo);
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin ingredients query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('원료 관리', '/admin/ingredients', $formToken) . '<main class="admin-shell"><h1>원료 목록을 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>이 최신으로 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    ob_start();
    ?>
<main class="admin-shell" data-ingredient-board data-token="<?= e($formToken) ?>">
<?= admin_notice() ?>
<p class="admin-slide-status" id="ingredient-status" role="status" aria-live="polite" hidden></p>
<div class="admin-section-head"><p class="admin-help">추가한 원료는 원료소개 페이지의 왼쪽 메뉴와 본문에 바로 나옵니다. 영문·중문을 비워 두면 한국어가 나옵니다.</p><a class="admin-button admin-button--outline" href="/ingredients" target="_blank" rel="noopener">홈페이지에서 보기</a></div>
<form class="admin-card admin-menu-add" method="post" action="/admin/ingredients">
<?= admin_hidden($formToken, ['op' => 'add']) ?>
<h2>원료 추가</h2>
<div class="admin-ingredient-add">
<div><label class="admin-label" for="add-ko">한국어 이름</label><input class="admin-input" id="add-ko" name="name_ko" maxlength="80" required></div>
<div><label class="admin-label" for="add-en">영문 이름</label><input class="admin-input" id="add-en" name="name_en" maxlength="80"></div>
<div><label class="admin-label" for="add-zh">중문 이름</label><input class="admin-input" id="add-zh" name="name_zh" maxlength="80"></div>
<div><label class="admin-label" for="add-icon">아이콘</label><?= admin_ingredient_icon_select('add-icon', '') ?></div>
<button class="admin-button" type="submit">추가</button>
</div>
</form>
<ol class="admin-ingredient-list" id="ingredient-list">
<?php foreach ($rows as $index => $row): $id = (int)$row['id']; $last = $index === count($rows) - 1; ?>
<li class="admin-card admin-ingredient" id="item-<?= $id ?>" data-ingredient-id="<?= $id ?>">
<div class="admin-slide-top"><button class="admin-slide-handle" type="button" title="끌어서 순서 변경" aria-label="<?= e((string)$row['name_ko']) ?>, 끌어서 순서 변경"><?= admin_icon('grip') ?></button><span class="admin-slide-number"><?= sprintf('%02d', $index + 1) ?></span>
<form method="post" action="/admin/ingredients" data-confirm="이 원료를 삭제할까요? 메뉴와 본문에서 함께 사라집니다."><?= admin_hidden($formToken, ['op' => 'delete', 'id' => $id]) ?><button class="admin-slide-delete" type="submit">삭제</button></form></div>
<div class="admin-ingredient-body">
<div class="admin-ingredient-media">
<?php if ($row['image'] !== ''): ?><img src="<?= e((string)$row['image']) ?>" alt="" loading="lazy"><?php else: ?><div class="admin-image-empty">사진 없음</div><?php endif; ?>
<form method="post" action="/admin/ingredients" enctype="multipart/form-data" data-slide-upload><?= admin_hidden($formToken, ['op' => 'image', 'id' => $id]) ?><label class="admin-label" for="image-<?= $id ?>">사진</label><input class="admin-input" type="file" id="image-<?= $id ?>" name="image" accept="image/jpeg,image/png,image/webp,image/heic" required><button class="admin-button admin-button--block" type="submit" data-busy-label="올리는 중...">사진 올리기</button></form>
<?php if ($row['image'] !== ''): ?><form method="post" action="/admin/ingredients" data-confirm="이 사진을 지울까요?"><?= admin_hidden($formToken, ['op' => 'image-remove', 'id' => $id]) ?><button class="admin-button admin-button--outline admin-button--block" type="submit">사진 지우기</button></form><?php endif; ?>
</div>
<form class="admin-ingredient-fields" method="post" action="/admin/ingredients">
<?= admin_hidden($formToken, ['op' => 'save', 'id' => $id]) ?>
<div class="admin-ingredient-icon"><label class="admin-label" for="icon-<?= $id ?>">아이콘</label><div><?= site_ingredient_icon((string)$row['icon'], 'admin-ingredient-icon-preview') ?><?= admin_ingredient_icon_select('icon-' . $id, (string)$row['icon']) ?></div></div>
<?php foreach (['ko' => '한국어', 'en' => '영문', 'zh' => '중문'] as $code => $label): ?>
<div><label class="admin-label" for="name-<?= $code ?>-<?= $id ?>"><?= $label ?> 이름</label><input class="admin-input" id="name-<?= $code ?>-<?= $id ?>" name="name_<?= $code ?>" value="<?= e((string)$row['name_' . $code]) ?>" maxlength="80"<?= $code === 'ko' ? ' required' : '' ?>></div>
<div><label class="admin-label" for="body-<?= $code ?>-<?= $id ?>"><?= $label ?> 설명</label><textarea class="admin-input" id="body-<?= $code ?>-<?= $id ?>" name="body_<?= $code ?>" rows="3" maxlength="<?= ADMIN_TEXT_MAX ?>"><?= e((string)$row['body_' . $code]) ?></textarea></div>
<?php endforeach; ?>
<button class="admin-button admin-button--block" type="submit">저장</button>
</form>
</div>
</li>
<?php endforeach; ?>
</ol>
</main>
<?php
    admin_layout('원료 관리', admin_header('원료 관리', '/admin/ingredients', $formToken) . ob_get_clean(), 200, ['/assets/js/vendor/Sortable.min.js', '/assets/js/admin-ingredient-board.js']);
}

// ---- Research items ----

// The first visit copies the default documents into the table, so editing starts from what the site shows.
function research_items_seed(\PDO $pdo): void
{
    if ((int)$pdo->query('SELECT count(*) FROM research_items')->fetchColumn() > 0) {
        return;
    }
    $current = locale();
    $sets = [];
    foreach (array_keys(LOCALES) as $code) {
        locale($code);
        $sets[$code] = site_content()['research_items'];
    }
    locale($current);
    $insert = $pdo->prepare('INSERT INTO research_items (tag_ko, tag_en, tag_zh, title_ko, title_en, title_zh, body_ko, body_en, body_zh, image, position) VALUES (:tk, :te, :tz, :nk, :ne, :nz, :bk, :be, :bz, :image, :position)');
    $pdo->beginTransaction();
    foreach ($sets['ko'] as $index => $item) {
        $insert->execute([
            'tk' => $item['tag'], 'te' => $sets['en'][$index]['tag'], 'tz' => $sets['zh'][$index]['tag'],
            'nk' => $item['title'], 'ne' => $sets['en'][$index]['title'], 'nz' => $sets['zh'][$index]['title'],
            'bk' => $item['body'], 'be' => $sets['en'][$index]['body'], 'bz' => $sets['zh'][$index]['body'],
            'image' => $item['image'], 'position' => $index + 1,
        ]);
    }
    $pdo->commit();
}

function research_item_image(\PDO $pdo, int $id): string
{
    $statement = $pdo->prepare('SELECT image FROM research_items WHERE id = :id');
    $statement->execute(['id' => $id]);
    return (string)$statement->fetchColumn();
}

// POST /admin/research. Returns an error code from admin_site_messages(), or '' on success. $focus is the item to scroll back to.
function admin_research_action(\PDO $pdo, string $op, int &$focus): string
{
    $id = (int)inquiry_value($_POST, 'id');
    $focus = $id;
    $line = fn (string $key, int $max): string => mb_substr(trim(inquiry_value($_POST, $key)), 0, $max);
    $body = function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? mb_substr(trim(str_replace("\r\n", "\n", $value)), 0, ADMIN_TEXT_MAX) : '';
    };
    try {
        if ($op === 'add') {
            if ($line('title_ko', 120) === '') {
                return 'no-title';
            }
            $statement = $pdo->prepare('INSERT INTO research_items (tag_ko, title_ko, title_en, title_zh, position) VALUES (:tag, :ko, :en, :zh, (SELECT coalesce(max(position), 0) + 1 FROM research_items)) RETURNING id');
            $statement->execute(['tag' => $line('tag_ko', 30), 'ko' => $line('title_ko', 120), 'en' => $line('title_en', 120), 'zh' => $line('title_zh', 120)]);
            $focus = (int)$statement->fetchColumn();
        } elseif ($op === 'save') {
            if ($line('title_ko', 120) === '') {
                return 'no-title';
            }
            $pdo->prepare('UPDATE research_items SET tag_ko = :tk, tag_en = :te, tag_zh = :tz, title_ko = :nk, title_en = :ne, title_zh = :nz, body_ko = :bk, body_en = :be, body_zh = :bz WHERE id = :id')->execute([
                'tk' => $line('tag_ko', 30), 'te' => $line('tag_en', 30), 'tz' => $line('tag_zh', 30),
                'nk' => $line('title_ko', 120), 'ne' => $line('title_en', 120), 'nz' => $line('title_zh', 120),
                'bk' => $body('body_ko'), 'be' => $body('body_en'), 'bz' => $body('body_zh'), 'id' => $id,
            ]);
        } elseif ($op === 'image') {
            $image = research_slide_read_upload($_FILES['image'] ?? []);
            if (is_string($image)) {
                return $image;
            }
            [$bytes, $mime, $width, $height] = $image;
            $pdo->beginTransaction();
            ingredient_image_discard($pdo, research_item_image($pdo, $id));
            $statement = $pdo->prepare('INSERT INTO site_images (image, mime, width, height) VALUES (:image, :mime, :width, :height) RETURNING id');
            $statement->bindValue('image', $bytes, \PDO::PARAM_LOB);
            $statement->bindValue('mime', $mime);
            $statement->bindValue('width', $width, \PDO::PARAM_INT);
            $statement->bindValue('height', $height, \PDO::PARAM_INT);
            $statement->execute();
            $pdo->prepare('UPDATE research_items SET image = :image WHERE id = :id')->execute(['image' => '/media/site/' . (int)$statement->fetchColumn(), 'id' => $id]);
            $pdo->commit();
        } elseif ($op === 'image-remove') {
            ingredient_image_discard($pdo, research_item_image($pdo, $id));
            $pdo->prepare("UPDATE research_items SET image = '' WHERE id = :id")->execute(['id' => $id]);
        } elseif ($op === 'delete') {
            if ((int)$pdo->query('SELECT count(*) FROM research_items')->fetchColumn() <= 1) {
                return 'last-research';
            }
            ingredient_image_discard($pdo, research_item_image($pdo, $id));
            $pdo->prepare('DELETE FROM research_items WHERE id = :id')->execute(['id' => $id]);
            $focus = 0;
        } elseif ($op === 'order') {
            $ids = array_map('intval', array_filter(explode(',', inquiry_value($_POST, 'ids')), 'ctype_digit'));
            $statement = $pdo->prepare('UPDATE research_items SET position = :position WHERE id = :id');
            $pdo->beginTransaction();
            foreach (array_values($ids) as $index => $itemId) {
                $statement->execute(['position' => $index + 1, 'id' => $itemId]);
            }
            $pdo->commit();
        }
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin research action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_research_page(\PDO $pdo, string $formToken): never
{
    try {
        research_items_seed($pdo);
        $rows = site_research_rows($pdo);
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin research query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('연구기술 자료', '/admin/research', $formToken) . '<main class="admin-shell"><h1>자료 목록을 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>이 최신으로 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    ob_start();
    ?>
<main class="admin-shell" data-ingredient-board data-action="/admin/research" data-token="<?= e($formToken) ?>">
<?= admin_notice() ?>
<p class="admin-slide-status" id="ingredient-status" role="status" aria-live="polite" hidden></p>
<div class="admin-section-head"><p class="admin-help">추가한 자료는 연구기술 페이지의 카드로 바로 나옵니다. 사진과 문구를 넣고, 같은 분류끼리 위쪽 버튼으로 모아 볼 수 있습니다. 영문·중문을 비워 두면 한국어가 나옵니다.</p><a class="admin-button admin-button--outline" href="/research" target="_blank" rel="noopener">홈페이지에서 보기</a></div>
<form class="admin-card admin-menu-add" method="post" action="/admin/research">
<?= admin_hidden($formToken, ['op' => 'add']) ?>
<h2>자료 추가</h2>
<div class="admin-ingredient-add">
<div><label class="admin-label" for="add-ko">한국어 제목</label><input class="admin-input" id="add-ko" name="title_ko" maxlength="120" required></div>
<div><label class="admin-label" for="add-en">영문 제목</label><input class="admin-input" id="add-en" name="title_en" maxlength="120"></div>
<div><label class="admin-label" for="add-zh">중문 제목</label><input class="admin-input" id="add-zh" name="title_zh" maxlength="120"></div>
<div><label class="admin-label" for="add-tag">분류</label><input class="admin-input" id="add-tag" name="tag_ko" maxlength="30" placeholder="예: 특허"></div>
<button class="admin-button" type="submit">추가</button>
</div>
</form>
<ol class="admin-ingredient-list" id="ingredient-list">
<?php foreach ($rows as $index => $row): $id = (int)$row['id']; $last = $index === count($rows) - 1; ?>
<li class="admin-card admin-ingredient" id="item-<?= $id ?>" data-ingredient-id="<?= $id ?>">
<div class="admin-slide-top"><button class="admin-slide-handle" type="button" title="끌어서 순서 변경" aria-label="<?= e((string)$row['title_ko']) ?>, 끌어서 순서 변경"><?= admin_icon('grip') ?></button><span class="admin-slide-number"><?= sprintf('%02d', $index + 1) ?></span>
<form method="post" action="/admin/research" data-confirm="이 자료를 삭제할까요? 연구기술 페이지에서 사라집니다."><?= admin_hidden($formToken, ['op' => 'delete', 'id' => $id]) ?><button class="admin-slide-delete" type="submit">삭제</button></form></div>
<div class="admin-ingredient-body">
<div class="admin-ingredient-media">
<?php if ($row['image'] !== ''): ?><img src="<?= e((string)$row['image']) ?>" alt="" loading="lazy"><?php else: ?><div class="admin-image-empty">사진 없음</div><?php endif; ?>
<form method="post" action="/admin/research" enctype="multipart/form-data" data-slide-upload><?= admin_hidden($formToken, ['op' => 'image', 'id' => $id]) ?><label class="admin-label" for="image-<?= $id ?>">사진</label><input class="admin-input" type="file" id="image-<?= $id ?>" name="image" accept="image/jpeg,image/png,image/webp,image/heic" required><button class="admin-button admin-button--block" type="submit" data-busy-label="올리는 중...">사진 올리기</button></form>
<?php if ($row['image'] !== ''): ?><form method="post" action="/admin/research" data-confirm="이 사진을 지울까요?"><?= admin_hidden($formToken, ['op' => 'image-remove', 'id' => $id]) ?><button class="admin-button admin-button--outline admin-button--block" type="submit">사진 지우기</button></form><?php endif; ?>
</div>
<form class="admin-ingredient-fields" method="post" action="/admin/research">
<?= admin_hidden($formToken, ['op' => 'save', 'id' => $id]) ?>
<div class="admin-research-tags"><?php foreach (['ko' => '한국어', 'en' => '영문', 'zh' => '중문'] as $code => $label): ?><div><label class="admin-label" for="tag-<?= $code ?>-<?= $id ?>"><?= $label ?> 분류</label><input class="admin-input" id="tag-<?= $code ?>-<?= $id ?>" name="tag_<?= $code ?>" value="<?= e((string)$row['tag_' . $code]) ?>" maxlength="30"></div><?php endforeach; ?></div>
<?php foreach (['ko' => '한국어', 'en' => '영문', 'zh' => '중문'] as $code => $label): ?>
<div><label class="admin-label" for="title-<?= $code ?>-<?= $id ?>"><?= $label ?> 제목</label><input class="admin-input" id="title-<?= $code ?>-<?= $id ?>" name="title_<?= $code ?>" value="<?= e((string)$row['title_' . $code]) ?>" maxlength="120"<?= $code === 'ko' ? ' required' : '' ?>></div>
<div><label class="admin-label" for="body-<?= $code ?>-<?= $id ?>"><?= $label ?> 설명</label><textarea class="admin-input" id="body-<?= $code ?>-<?= $id ?>" name="body_<?= $code ?>" rows="3" maxlength="<?= ADMIN_TEXT_MAX ?>"><?= e((string)$row['body_' . $code]) ?></textarea></div>
<?php endforeach; ?>
<button class="admin-button admin-button--block" type="submit">저장</button>
</form>
</div>
</li>
<?php endforeach; ?>
</ol>
</main>
<?php
    admin_layout('연구기술 자료', admin_header('연구기술 자료', '/admin/research', $formToken) . ob_get_clean(), 200, ['/assets/js/vendor/Sortable.min.js', '/assets/js/admin-ingredient-board.js']);
}

// ---- Brand activity photos ----

const BRAND_GALLERY_MAX = 30;

// The first visit copies the default photos into the table, so they can be reordered and deleted too.
function brand_gallery_seed(\PDO $pdo): void
{
    if ((int)$pdo->query('SELECT count(*) FROM brand_gallery_items')->fetchColumn() > 0) {
        return;
    }
    $defaults = [];
    foreach (site_defaults('ko')['brand_slides']['bellabelfarm'] as $slide) {
        $defaults = $slide['gallery'] ?? $defaults;
    }
    $insert = $pdo->prepare('INSERT INTO brand_gallery_items (image, position) VALUES (:image, :position)');
    $pdo->beginTransaction();
    foreach ($defaults as $index => $image) {
        $insert->execute(['image' => $image, 'position' => $index + 1]);
    }
    $pdo->commit();
}

// POST /admin/gallery. Returns an error code from admin_site_messages(), or '' on success.
function admin_gallery_action(\PDO $pdo, string $op): string
{
    $id = (int)inquiry_value($_POST, 'id');
    try {
        brand_gallery_seed($pdo);
        $count = (int)$pdo->query('SELECT count(*) FROM brand_gallery_items')->fetchColumn();
        if ($op === 'upload') {
            if ($count >= BRAND_GALLERY_MAX) {
                return 'gallery-full';
            }
            $image = research_slide_read_upload($_FILES['image'] ?? []);
            if (is_string($image)) {
                return $image;
            }
            [$bytes, $mime, $width, $height] = $image;
            $pdo->beginTransaction();
            $statement = $pdo->prepare('INSERT INTO site_images (image, mime, width, height) VALUES (:image, :mime, :width, :height) RETURNING id');
            $statement->bindValue('image', $bytes, \PDO::PARAM_LOB);
            $statement->bindValue('mime', $mime);
            $statement->bindValue('width', $width, \PDO::PARAM_INT);
            $statement->bindValue('height', $height, \PDO::PARAM_INT);
            $statement->execute();
            $pdo->prepare('INSERT INTO brand_gallery_items (image, position) VALUES (:image, (SELECT coalesce(max(position), 0) + 1 FROM brand_gallery_items))')->execute(['image' => '/media/site/' . (int)$statement->fetchColumn()]);
            $pdo->commit();
        } elseif ($op === 'delete') {
            if ($count <= 1) {
                return 'last-gallery';
            }
            $statement = $pdo->prepare('SELECT image FROM brand_gallery_items WHERE id = :id');
            $statement->execute(['id' => $id]);
            ingredient_image_discard($pdo, (string)$statement->fetchColumn());
            $pdo->prepare('DELETE FROM brand_gallery_items WHERE id = :id')->execute(['id' => $id]);
        } elseif ($op === 'order') {
            $ids = array_map('intval', array_filter(explode(',', inquiry_value($_POST, 'ids')), 'ctype_digit'));
            $statement = $pdo->prepare('UPDATE brand_gallery_items SET position = :position WHERE id = :id');
            $pdo->beginTransaction();
            foreach (array_values($ids) as $index => $itemId) {
                $statement->execute(['position' => $index + 1, 'id' => $itemId]);
            }
            $pdo->commit();
        }
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin gallery action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_settings_save(\PDO $pdo): bool
{
    try {
        $statement = $pdo->prepare("INSERT INTO site_content (key, locale, value) VALUES ('settings.block_context_menu', 'all', :value) ON CONFLICT (key, locale) DO UPDATE SET value = EXCLUDED.value, updated_at = now()");
        $statement->execute(['value' => isset($_POST['block_context_menu']) ? '1' : '0']);
        return true;
    } catch (\Throwable $exception) {
        error_log('Admin settings save failed (' . get_class($exception) . ').');
        return false;
    }
}

function admin_settings_page(\PDO $pdo, string $formToken): never
{
    ob_start();
    ?>
<main class="admin-shell">
<?= admin_notice() ?>
<form class="admin-card" method="post" action="/admin/settings">
<h2>이미지 보호</h2>
<?= admin_hidden($formToken, []) ?>
<p><label class="admin-check"><input type="checkbox" name="block_context_menu" value="1"<?= site_context_menu_blocked() ? ' checked' : '' ?>> 홈페이지에서 우클릭과 이미지 끌어가기 막기</label></p>
<p class="admin-help">켜면 방문자가 우클릭 메뉴로 사진을 저장하기 어려워집니다. 입력칸의 메뉴는 그대로 쓸 수 있고, 화면 캡처나 개발자 도구까지 막지는 못합니다.</p>
<button class="admin-button" type="submit">저장</button>
</form>
</main>
<?php
    admin_layout('사이트 설정', admin_header('사이트 설정', '/admin/settings', $formToken) . ob_get_clean());
}

function admin_gallery_page(\PDO $pdo, string $formToken): never
{
    try {
        brand_gallery_seed($pdo);
        $rows = site_brand_gallery_rows($pdo);
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin gallery query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('브랜드 활동 사진', '/admin/gallery', $formToken) . '<main class="admin-shell"><h1>사진 목록을 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>이 최신으로 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    ob_start();
    ?>
<main class="admin-shell admin-gallery" data-ingredient-board data-action="/admin/gallery" data-token="<?= e($formToken) ?>">
<?= admin_notice() ?>
<p class="admin-slide-status" id="ingredient-status" role="status" aria-live="polite" hidden></p>
<div class="admin-section-head"><p class="admin-help">브랜드 소개 벨아벨팜 5번 슬라이드에 나오는 사진입니다. 맨 앞 사진이 큰 사진으로 먼저 보이고, 끌어서 순서를 바꿀 수 있습니다.</p><a class="admin-button admin-button--outline" href="/brand#bellabelfarm" target="_blank" rel="noopener">홈페이지에서 보기</a></div>
<form class="admin-slide-drop" method="post" action="/admin/gallery" enctype="multipart/form-data" data-gallery-upload>
<?= admin_hidden($formToken, ['op' => 'upload']) ?>
<label for="gallery-image"><?= admin_icon('upload') ?><strong>사진 추가</strong><span>여기로 끌어다 놓거나 눌러서 선택하세요. 여러 장도 됩니다. JPG, PNG, WEBP</span></label>
<input type="file" id="gallery-image" name="image" accept="image/jpeg,image/png,image/webp,image/heic" multiple>
<noscript><button class="admin-button" type="submit">올리기</button></noscript>
</form>
<div class="admin-slide-head"><h2>사진 목록 <span><?= count($rows) ?></span>장</h2></div>
<ol class="admin-slide-board" id="ingredient-list">
<?php foreach ($rows as $index => $row): $id = (int)$row['id']; ?>
<li class="admin-card admin-slide-card" data-ingredient-id="<?= $id ?>">
<div class="admin-slide-top"><button class="admin-slide-handle" type="button" title="끌어서 순서 변경" aria-label="<?= $index + 1 ?>번 사진, 끌어서 순서 변경"><?= admin_icon('grip') ?></button><span class="admin-slide-number"><?= sprintf('%02d', $index + 1) ?></span>
<form method="post" action="/admin/gallery" data-confirm="이 사진을 삭제할까요?"><?= admin_hidden($formToken, ['op' => 'delete', 'id' => $id]) ?><button class="admin-slide-delete" type="submit">삭제</button></form></div>
<div class="admin-slide-frame"><img src="<?= e((string)$row['image']) ?>" alt="" loading="lazy" draggable="false"></div>
</li>
<?php endforeach; ?>
</ol>
</main>
<?php
    admin_layout('브랜드 활동 사진', admin_header('브랜드 활동 사진', '/admin/gallery', $formToken) . ob_get_clean(), 200, ['/assets/js/vendor/Sortable.min.js', '/assets/js/admin-ingredient-board.js', '/assets/js/admin-gallery.js']);
}

// ---- Products ----

function product_categories(): array
{
    return array_column(site_content()['product_categories'], 'name', 'id');
}

// The first visit copies the current products into the table, per language.
function product_items_seed(\PDO $pdo): void
{
    if ((int)$pdo->query('SELECT count(*) FROM product_items')->fetchColumn() > 0) {
        return;
    }
    $current = locale();
    $sets = [];
    foreach (array_keys(LOCALES) as $code) {
        locale($code);
        $sets[$code] = site_content()['products'];
    }
    locale($current);
    $insert = $pdo->prepare('INSERT INTO product_items (category, name_ko, name_en, name_zh, detail_ko, detail_en, detail_zh, size_ko, size_en, size_zh, image, position) VALUES (:category, :nk, :ne, :nz, :dk, :de, :dz, :sk, :se, :sz, :image, :position)');
    $pdo->beginTransaction();
    foreach ($sets['ko'] as $index => $item) {
        $insert->execute([
            'category' => $item['category'],
            'nk' => $item['name'], 'ne' => $sets['en'][$index]['name'], 'nz' => $sets['zh'][$index]['name'],
            'dk' => $item['detail'], 'de' => $sets['en'][$index]['detail'], 'dz' => $sets['zh'][$index]['detail'],
            'sk' => (string)$item['size'], 'se' => (string)$sets['en'][$index]['size'], 'sz' => (string)$sets['zh'][$index]['size'],
            'image' => (string)($item['image']['src'] ?? ''), 'position' => $index + 1,
        ]);
    }
    $pdo->commit();
}

function product_item_image(\PDO $pdo, int $id): string
{
    $statement = $pdo->prepare('SELECT image FROM product_items WHERE id = :id');
    $statement->execute(['id' => $id]);
    return (string)$statement->fetchColumn();
}

// POST /admin/products. Returns an error code from admin_site_messages(), or '' on success.
function admin_product_action(\PDO $pdo, string $op, int &$focus, string &$category): string
{
    $id = (int)inquiry_value($_POST, 'id');
    $focus = $id;
    $categories = product_categories();
    $posted = inquiry_value($_POST, 'category');
    $line = fn (string $key, int $max = 120): string => mb_substr(trim(inquiry_value($_POST, $key)), 0, $max);
    try {
        product_items_seed($pdo);
        if (isset($categories[$posted])) {
            $category = $posted;
        }
        if ($op === 'add' || $op === 'save') {
            if ($line('name_ko') === '') {
                return 'no-product';
            }
            $url = $line('url', 500);
            if ($url !== '' && !admin_link_valid($url)) {
                return 'bad-link';
            }
            $values = [
                'category' => $category,
                'nk' => $line('name_ko'), 'ne' => $line('name_en'), 'nz' => $line('name_zh'),
                'dk' => $line('detail_ko', 300), 'de' => $line('detail_en', 300), 'dz' => $line('detail_zh', 300),
                'sk' => $line('size_ko', 60), 'se' => $line('size_en', 60), 'sz' => $line('size_zh', 60),
                'url' => $url,
            ];
            if ($op === 'add') {
                $statement = $pdo->prepare('INSERT INTO product_items (category, name_ko, name_en, name_zh, detail_ko, detail_en, detail_zh, size_ko, size_en, size_zh, url, position) VALUES (:category, :nk, :ne, :nz, :dk, :de, :dz, :sk, :se, :sz, :url, (SELECT coalesce(max(position), 0) + 1 FROM product_items WHERE category = :category2)) RETURNING id');
                $statement->execute($values + ['category2' => $category]);
                $focus = (int)$statement->fetchColumn();
            } else {
                $pdo->prepare('UPDATE product_items SET category = :category, name_ko = :nk, name_en = :ne, name_zh = :nz, detail_ko = :dk, detail_en = :de, detail_zh = :dz, size_ko = :sk, size_en = :se, size_zh = :sz, url = :url WHERE id = :id')->execute($values + ['id' => $id]);
            }
        } elseif ($op === 'image') {
            $image = research_slide_read_upload($_FILES['image'] ?? []);
            if (is_string($image)) {
                return $image;
            }
            [$bytes, $mime, $width, $height] = $image;
            $old = product_item_image($pdo, $id);
            $pdo->beginTransaction();
            $statement = $pdo->prepare('INSERT INTO site_images (image, mime, width, height) VALUES (:image, :mime, :width, :height) RETURNING id');
            $statement->bindValue('image', $bytes, \PDO::PARAM_LOB);
            $statement->bindValue('mime', $mime);
            $statement->bindValue('width', $width, \PDO::PARAM_INT);
            $statement->bindValue('height', $height, \PDO::PARAM_INT);
            $statement->execute();
            $pdo->prepare('UPDATE product_items SET image = :image WHERE id = :id')->execute(['image' => '/media/site/' . (int)$statement->fetchColumn(), 'id' => $id]);
            ingredient_image_discard($pdo, $old);
            $pdo->commit();
        } elseif ($op === 'image-remove') {
            $old = product_item_image($pdo, $id);
            $pdo->prepare("UPDATE product_items SET image = '' WHERE id = :id")->execute(['id' => $id]);
            ingredient_image_discard($pdo, $old);
        } elseif ($op === 'delete') {
            $old = product_item_image($pdo, $id);
            $pdo->prepare('DELETE FROM product_items WHERE id = :id')->execute(['id' => $id]);
            ingredient_image_discard($pdo, $old);
            $focus = 0;
        } elseif ($op === 'order') {
            $ids = array_map('intval', array_filter(explode(',', inquiry_value($_POST, 'ids')), 'ctype_digit'));
            $statement = $pdo->prepare('UPDATE product_items SET position = :position WHERE id = :id');
            $pdo->beginTransaction();
            foreach (array_values($ids) as $index => $itemId) {
                $statement->execute(['position' => $index + 1, 'id' => $itemId]);
            }
            $pdo->commit();
        }
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin product action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_product_category_select(string $id, string $selected): string
{
    $options = '';
    foreach (product_categories() as $key => $name) {
        $options .= '<option value="' . e($key) . '"' . ($key === $selected ? ' selected' : '') . '>' . e($name) . '</option>';
    }
    return '<select class="admin-input" id="' . e($id) . '" name="category">' . $options . '</select>';
}

function admin_products_page(\PDO $pdo, string $formToken): never
{
    try {
        product_items_seed($pdo);
        $all = site_product_rows($pdo);
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin products query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('상품 관리', '/admin/products', $formToken) . '<main class="admin-shell"><h1>상품 목록을 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>이 최신으로 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    $categories = product_categories();
    $category = (string)($_GET['category'] ?? '');
    $category = isset($categories[$category]) ? $category : (string)array_key_first($categories);
    $rows = array_values(array_filter($all, fn (array $row): bool => $row['category'] === $category));
    $counts = array_count_values(array_column($all, 'category'));
    $languages = ['ko' => '한국어', 'en' => '영문', 'zh' => '중문'];
    ob_start();
    ?>
<main class="admin-shell" data-ingredient-board data-action="/admin/products" data-token="<?= e($formToken) ?>">
<?= admin_notice() ?>
<p class="admin-slide-status" id="ingredient-status" role="status" aria-live="polite" hidden></p>
<div class="admin-section-head"><p class="admin-help">카테고리를 고르고 상품을 추가하면 상품소개 페이지의 해당 분류에 바로 나옵니다. 영문·중문을 비워 두면 한국어가 나옵니다.</p><a class="admin-button admin-button--outline" href="/product#<?= e($category) ?>" target="_blank" rel="noopener">홈페이지에서 보기</a></div>
<nav class="admin-tabs admin-section-nav admin-section-nav--sub" aria-label="상품 카테고리">
<?php foreach ($categories as $key => $name): ?>
<a class="admin-chip<?= $key === $category ? ' is-active' : '' ?>" href="/admin/products?category=<?= e($key) ?>"<?= $key === $category ? ' aria-current="page"' : '' ?>><?= e($name) ?> (<?= (int)($counts[$key] ?? 0) ?>)</a>
<?php endforeach; ?>
</nav>
<form class="admin-card admin-menu-add" method="post" action="/admin/products">
<h2><?= e($categories[$category]) ?>에 상품 추가</h2>
<?= admin_hidden($formToken, ['op' => 'add', 'category' => $category]) ?>
<div class="admin-menu-fields admin-product-add">
<div><label class="admin-label" for="add-name">상품 이름 (한국어)</label><input class="admin-input" id="add-name" name="name_ko" maxlength="120" required></div>
<div><label class="admin-label" for="add-detail">설명</label><input class="admin-input" id="add-detail" name="detail_ko" maxlength="300"></div>
<div><label class="admin-label" for="add-size">용량</label><input class="admin-input" id="add-size" name="size_ko" maxlength="60" placeholder="예: 9g × 10개입"></div>
<button class="admin-button" type="submit">추가</button>
</div>
</form>
<?php if (!$rows): ?><p class="admin-help admin-product-none">이 카테고리에는 아직 상품이 없습니다. 홈페이지에는 "준비 중인 상품입니다."로 나옵니다.</p><?php endif; ?>
<ol class="admin-ingredient-list" id="ingredient-list">
<?php foreach ($rows as $index => $row): $id = (int)$row['id']; ?>
<li class="admin-card admin-ingredient" id="item-<?= $id ?>" data-ingredient-id="<?= $id ?>">
<div class="admin-slide-top"><button class="admin-slide-handle" type="button" title="끌어서 순서 변경" aria-label="<?= e((string)$row['name_ko']) ?>, 끌어서 순서 변경"><?= admin_icon('grip') ?></button><span class="admin-slide-number"><?= sprintf('%02d', $index + 1) ?></span>
<form method="post" action="/admin/products" data-confirm="이 상품을 삭제할까요? 상품소개 페이지에서 사라집니다."><?= admin_hidden($formToken, ['op' => 'delete', 'id' => $id, 'category' => $category]) ?><button class="admin-slide-delete" type="submit">삭제</button></form></div>
<div class="admin-ingredient-body">
<div class="admin-ingredient-media">
<?php if ($row['image'] !== ''): ?><img src="<?= e((string)$row['image']) ?>" alt="" loading="lazy"><?php else: ?><div class="admin-image-empty">사진 없음</div><?php endif; ?>
<form method="post" action="/admin/products" enctype="multipart/form-data" data-slide-upload><?= admin_hidden($formToken, ['op' => 'image', 'id' => $id, 'category' => $category]) ?><label class="admin-label" for="image-<?= $id ?>">사진</label><input class="admin-input" type="file" id="image-<?= $id ?>" name="image" accept="image/jpeg,image/png,image/webp,image/heic" required><button class="admin-button admin-button--outline admin-button--block" type="submit" data-busy-label="올리는 중...">사진 올리기</button></form>
<?php if ($row['image'] !== ''): ?><form method="post" action="/admin/products" data-confirm="이 사진을 지울까요?"><?= admin_hidden($formToken, ['op' => 'image-remove', 'id' => $id, 'category' => $category]) ?><button class="admin-button admin-button--outline admin-button--block" type="submit">사진 지우기</button></form><?php endif; ?>
</div>
<form class="admin-product-fields" method="post" action="/admin/products">
<?= admin_hidden($formToken, ['op' => 'save', 'id' => $id]) ?>
<div class="admin-product-wide"><label class="admin-label" for="category-<?= $id ?>">카테고리</label><?= admin_product_category_select('category-' . $id, (string)$row['category']) ?></div>
<?php foreach ($languages as $code => $label): ?>
<div><label class="admin-label" for="name-<?= $code ?>-<?= $id ?>"><?= $label ?> 이름</label><input class="admin-input" id="name-<?= $code ?>-<?= $id ?>" name="name_<?= $code ?>" value="<?= e((string)$row['name_' . $code]) ?>" maxlength="120"<?= $code === 'ko' ? ' required' : '' ?>></div>
<div><label class="admin-label" for="detail-<?= $code ?>-<?= $id ?>"><?= $label ?> 설명</label><input class="admin-input" id="detail-<?= $code ?>-<?= $id ?>" name="detail_<?= $code ?>" value="<?= e((string)$row['detail_' . $code]) ?>" maxlength="300"></div>
<div><label class="admin-label" for="size-<?= $code ?>-<?= $id ?>"><?= $label ?> 용량</label><input class="admin-input" id="size-<?= $code ?>-<?= $id ?>" name="size_<?= $code ?>" value="<?= e((string)$row['size_' . $code]) ?>" maxlength="60"></div>
<?php endforeach; ?>
<div class="admin-product-wide"><label class="admin-label" for="url-<?= $id ?>">상품 보기 주소 (비우면 분류 주소, 그것도 없으면 기본 주소)</label><input class="admin-input" id="url-<?= $id ?>" name="url" value="<?= e((string)$row['url']) ?>" maxlength="500" placeholder="https://"></div>
<button class="admin-button admin-button--block admin-product-wide" type="submit">저장</button>
</form>
</div>
</li>
<?php endforeach; ?>
</ol>
</main>
<?php
    admin_layout('상품 관리', admin_header('상품 관리', '/admin/products', $formToken) . ob_get_clean(), 200, ['/assets/js/vendor/Sortable.min.js', '/assets/js/admin-ingredient-board.js']);
}

// ---- Sections ----

function admin_section_selection(): array
{
    $sections = site_sections();
    $page = (string)($_REQUEST['page'] ?? '');
    $page = isset($sections[$page]) ? $page : array_key_first($sections);
    $section = (string)($_REQUEST['section'] ?? '');
    $section = isset($sections[$page]['sections'][$section]) ? $section : array_key_first($sections[$page]['sections']);
    $lang = (string)($_REQUEST['lang'] ?? 'ko');
    return [$page, $section, isset(LOCALES[$lang]) ? $lang : 'ko'];
}

function admin_section_url(string $page, string $section, string $lang, string $extra = ''): string
{
    return '/admin/sections?' . http_build_query(['page' => $page, 'section' => $section, 'lang' => $lang]) . $extra;
}

function site_content_upsert(\PDO $pdo, string $key, string $code, string $value): void
{
    $pdo->prepare('INSERT INTO site_content (key, locale, value) VALUES (:key, :code, :value) ON CONFLICT (key, locale) DO UPDATE SET value = excluded.value, updated_at = now()')
        ->execute(['key' => $key, 'code' => $code, 'value' => $value]);
}

function site_image_remove(\PDO $pdo, string $key): void
{
    $statement = $pdo->prepare("SELECT value FROM site_content WHERE key = :key AND locale = 'all'");
    $statement->execute(['key' => $key]);
    $value = (string)$statement->fetchColumn();
    if (preg_match('~^/media/site/(\d+)$~', $value, $match)) {
        $pdo->prepare('DELETE FROM site_images WHERE id = :id')->execute(['id' => (int)$match[1]]);
    }
    $pdo->prepare("DELETE FROM site_content WHERE key = :key AND locale = 'all'")->execute(['key' => $key]);
}

function site_image_store(\PDO $pdo, string $key, array $image): void
{
    [$bytes, $mime, $width, $height] = $image;
    $pdo->beginTransaction();
    site_image_remove($pdo, $key);
    $statement = $pdo->prepare('INSERT INTO site_images (image, mime, width, height) VALUES (:image, :mime, :width, :height) RETURNING id');
    $statement->bindValue('image', $bytes, \PDO::PARAM_LOB);
    $statement->bindValue('mime', $mime);
    $statement->bindValue('width', $width, \PDO::PARAM_INT);
    $statement->bindValue('height', $height, \PDO::PARAM_INT);
    $statement->execute();
    site_content_upsert($pdo, $key, 'all', '/media/site/' . (int)$statement->fetchColumn());
    $pdo->commit();
}

// POST /admin/sections. Returns an error code from admin_site_messages(), or '' on success.
function admin_sections_action(\PDO $pdo, string $op, array $fields, string $lang): string
{
    try {
        if ($op === 'text') {
            $defaults = site_defaults($lang);
            $submitted = is_array($_POST['fields'] ?? null) ? $_POST['fields'] : [];
            foreach ($fields as $field) {
                $value = $submitted[$field['key']] ?? null;
                if ($field['type'] === 'link' && is_string($value) && trim($value) !== '' && !admin_link_valid(trim($value))) {
                    return 'bad-link';
                }
            }
            $pdo->beginTransaction();
            foreach ($fields as $field) {
                $value = $submitted[$field['key']] ?? null;
                if ($field['type'] === 'image' || !is_string($value)) {
                    continue;
                }
                $value = mb_substr(trim(str_replace("\r\n", "\n", $value)), 0, ADMIN_TEXT_MAX);
                // An empty box or the original wording means "use the default", so later copy fixes still reach it.
                if ($value === '' || $value === (string)site_get($defaults, $field['key'])) {
                    $pdo->prepare('DELETE FROM site_content WHERE key = :key AND locale = :code')->execute(['key' => $field['key'], 'code' => $lang]);
                } else {
                    site_content_upsert($pdo, $field['key'], $lang, $value);
                }
            }
            $pdo->commit();
            return '';
        }
        $key = inquiry_value($_POST, 'key');
        $isImage = (bool)array_filter($fields, fn (array $field): bool => $field['key'] === $key && $field['type'] === 'image');
        if (!$isImage) {
            return 'failed';
        }
        if ($op === 'image') {
            $image = research_slide_read_upload($_FILES['image'] ?? []);
            if (is_string($image)) {
                return $image;
            }
            site_image_store($pdo, $key, $image);
        } elseif ($op === 'image-reset') {
            site_image_remove($pdo, $key);
        }
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Admin section action failed (' . get_class($exception) . ').');
        return 'failed';
    }
    return '';
}

function admin_sections_page(\PDO $pdo, string $formToken): never
{
    [$pageKey, $sectionKey, $lang] = admin_section_selection();
    $pages = site_sections();
    $page = $pages[$pageKey];
    $section = $page['sections'][$sectionKey];
    try {
        $saved = site_rows($pdo, $lang);
    } catch (\Throwable $exception) {
        error_log('Admin section query failed (' . get_class($exception) . ').');
        admin_layout('데이터베이스 오류', admin_header('페이지 편집', '/admin/sections', $formToken) . '<main class="admin-shell"><h1>섹션 내용을 불러오지 못했습니다.</h1><p><code>db/schema.sql</code>의 <code>site_content</code> 테이블이 적용되었는지 확인해 주세요.</p></main>', 502);
    }
    $defaults = site_defaults($lang);
    $textFields = array_filter($section['fields'], fn (array $field): bool => $field['type'] !== 'image');
    $imageFields = array_filter($section['fields'], fn (array $field): bool => $field['type'] === 'image');
    $previewPath = page_url($page['url'], $lang) . ($section['anchor'] ?? '');
    ob_start();
    ?>
<main class="admin-shell">
<?= admin_notice() ?>
<nav class="admin-tabs admin-section-nav admin-section-nav--sub" aria-label="섹션">
<?php foreach ($page['sections'] as $key => $item): ?>
<a class="admin-chip<?= $key === $sectionKey ? ' is-active' : '' ?>" href="<?= e(admin_section_url($pageKey, $key, $lang)) ?>"<?= $key === $sectionKey ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
<?php endforeach; ?>
</nav>
<div class="admin-section-head">
<h2><?= e($section['label']) ?></h2>
<a class="admin-button admin-button--outline" href="<?= e($previewPath) ?>" target="_blank" rel="noopener">홈페이지에서 보기</a>
</div>
<?php if (isset($section['note'])): ?><p class="admin-help"><?= e($section['note']) ?></p><?php endif; ?>
<?php if ($textFields): ?>
<form class="admin-card admin-section-form" method="post" action="/admin/sections">
<?= admin_hidden($formToken, ['op' => 'text', 'page' => $pageKey, 'section' => $sectionKey, 'lang' => $lang]) ?>
<div class="admin-section-form-head">
<nav class="admin-tabs" aria-label="언어">
<?php foreach (['ko' => '한국어', 'en' => '영문', 'zh' => '중문'] as $code => $label): ?>
<a class="admin-chip<?= $code === $lang ? ' is-active' : '' ?>" href="<?= e(admin_section_url($pageKey, $sectionKey, $code)) ?>"<?= $code === $lang ? ' aria-current="true"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
</nav>
<p class="admin-help">언어별로 따로 저장됩니다. 칸을 비우고 저장하면 원래 문구로 돌아갑니다.</p>
</div>
<?php foreach ($textFields as $index => $field): $key = $field['key']; $id = 'field-' . $index; $value = (string)($saved[$key] ?? site_get($defaults, $key) ?? ''); ?>
<div class="admin-field">
<label class="admin-label" for="<?= $id ?>"><?= e($field['label']) ?><?php if (isset($saved[$key])): ?> <span class="admin-badge">수정됨</span><?php endif; ?></label>
<?php if ($field['type'] === 'textarea'): ?>
<textarea class="admin-input" id="<?= $id ?>" name="fields[<?= e($key) ?>]" rows="<?= max(2, min(12, substr_count($value, "\n") + 1 + intdiv(mb_strlen($value), 60))) ?>" maxlength="<?= ADMIN_TEXT_MAX ?>"><?= e($value) ?></textarea>
<?php else: ?>
<input class="admin-input" type="text" id="<?= $id ?>" name="fields[<?= e($key) ?>]" value="<?= e($value) ?>" maxlength="<?= ADMIN_TEXT_MAX ?>">
<?php endif; ?>
</div>
<?php endforeach; ?>
<button class="admin-button admin-button--block" type="submit">저장</button>
</form>
<?php endif; ?>
<?php if ($imageFields): ?>
<h3 class="admin-section-subtitle">이미지</h3>
<p class="admin-help">이미지는 모든 언어에 함께 적용됩니다. JPG, PNG, WEBP. 큰 이미지는 올리기 전에 자동으로 줄입니다.</p>
<ul class="admin-image-grid">
<?php foreach ($imageFields as $index => $field): $key = $field['key']; $src = (string)($saved[$key] ?? site_get($defaults, $key) ?? ''); $id = 'image-' . $index; ?>
<li class="admin-card admin-image-field">
<?php if ($src !== ''): ?><img src="<?= e($src) ?>" alt="" loading="lazy"><?php else: ?><div class="admin-image-empty">이미지 없음</div><?php endif; ?>
<form method="post" action="/admin/sections" enctype="multipart/form-data" data-slide-upload>
<?= admin_hidden($formToken, ['op' => 'image', 'page' => $pageKey, 'section' => $sectionKey, 'lang' => $lang, 'key' => $key]) ?>
<label class="admin-label" for="<?= $id ?>"><?= e($field['label']) ?><?php if (isset($saved[$key])): ?> <span class="admin-badge">수정됨</span><?php endif; ?></label>
<input class="admin-input" type="file" id="<?= $id ?>" name="image" accept="image/jpeg,image/png,image/webp,image/heic" required>
<button class="admin-button admin-button--block" type="submit" data-busy-label="올리는 중...">이미지 바꾸기</button>
</form>
<?php if (isset($saved[$key])): ?>
<form method="post" action="/admin/sections" data-confirm="원래 이미지로 되돌릴까요?"><?= admin_hidden($formToken, ['op' => 'image-reset', 'page' => $pageKey, 'section' => $sectionKey, 'lang' => $lang, 'key' => $key]) ?><button class="admin-button admin-button--outline admin-button--block" type="submit">원래 이미지로</button></form>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</main>
<?php
    admin_layout($page['label'] . ' 편집', admin_header($page['label'] . ' 편집', '/admin/sections', $formToken) . ob_get_clean());
}
