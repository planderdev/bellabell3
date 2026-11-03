<?php
declare(strict_types=1);

// Research section slider images. Uploaded images live in PostgreSQL (the only writable store on Vercel)
// and are served from /media/research/<id>. Until something is uploaded, content.php's temporary slides show.
require_once __DIR__ . '/contact-handler.php';

const RESEARCH_SLIDE_MAX_BYTES = 3145728; // 3 MB. The admin page shrinks images in the browser before upload.
const RESEARCH_SLIDE_MAX_COUNT = 20;
const RESEARCH_SLIDE_TYPES = [IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png', IMAGETYPE_WEBP => 'image/webp'];

function research_slides_uploaded(\PDO $pdo): array
{
    return $pdo->query('SELECT id, width, height, alt FROM research_slides ORDER BY position, id')->fetchAll(\PDO::FETCH_ASSOC);
}

// Slides for the home page. A missing database or table must never break the page.
function research_slides(array $fallback): array
{
    $pdo = db_connection();
    if (!$pdo instanceof \PDO) {
        return $fallback;
    }
    try {
        $rows = research_slides_uploaded($pdo);
    } catch (\Throwable $exception) {
        error_log('Research slides query failed (' . get_class($exception) . ').');
        return $fallback;
    }
    if (!$rows) {
        return $fallback;
    }
    return array_map(fn (array $row): array => [
        'src' => '/media/research/' . (int)$row['id'],
        'alt' => t((string)$row['alt']),
        'width' => (int)$row['width'],
        'height' => (int)$row['height'],
    ], $rows);
}

// Reads and checks an uploaded file. Returns [bytes, mime, width, height], or an error code from admin_slide_messages().
function research_slide_read_upload(array $file): array|string
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return 'no-file';
    }
    if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || (int)($file['size'] ?? 0) > RESEARCH_SLIDE_MAX_BYTES) {
        return 'too-large';
    }
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
        return 'failed';
    }
    $bytes = (string)file_get_contents((string)$file['tmp_name']);
    $info = getimagesizefromstring($bytes);
    if (!$info || !isset(RESEARCH_SLIDE_TYPES[$info[2]]) || $info[0] < 1 || $info[1] < 1) {
        return 'bad-type';
    }
    return [$bytes, RESEARCH_SLIDE_TYPES[$info[2]], $info[0], $info[1]];
}

function research_slide_store(\PDO $pdo, array $image, string $alt): void
{
    [$bytes, $mime, $width, $height] = $image;
    $statement = $pdo->prepare('INSERT INTO research_slides (image, mime, width, height, alt, position) VALUES (:image, :mime, :width, :height, :alt, (SELECT coalesce(max(position), 0) + 1 FROM research_slides))');
    $statement->bindValue('image', $bytes, \PDO::PARAM_LOB);
    $statement->bindValue('mime', $mime);
    $statement->bindValue('width', $width, \PDO::PARAM_INT);
    $statement->bindValue('height', $height, \PDO::PARAM_INT);
    $statement->bindValue('alt', $alt);
    $statement->execute();
}

// Swaps a slide with its neighbour. $direction is -1 (earlier) or 1 (later).
function research_slide_move(\PDO $pdo, int $id, int $direction): void
{
    $ids = array_map('intval', array_column(research_slides_uploaded($pdo), 'id'));
    $from = array_search($id, $ids, true);
    if ($from === false || !isset($ids[$from + $direction])) {
        return;
    }
    [$ids[$from], $ids[$from + $direction]] = [$ids[$from + $direction], $ids[$from]];
    $pdo->beginTransaction();
    $statement = $pdo->prepare('UPDATE research_slides SET position = :position WHERE id = :id');
    foreach ($ids as $index => $slideId) {
        $statement->execute(['position' => $index + 1, 'id' => $slideId]);
    }
    $pdo->commit();
}

// GET /media/research/<id>. An id is never reused for different bytes, so it can be cached for good.
function serve_research_slide(int $id): void
{
    $row = false;
    $pdo = db_connection();
    if ($pdo instanceof \PDO) {
        try {
            $statement = $pdo->prepare('SELECT image, mime FROM research_slides WHERE id = :id');
            $statement->execute(['id' => $id]);
            $row = $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $exception) {
            error_log('Research slide read failed (' . get_class($exception) . ').');
        }
    }
    if (!$row) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        return;
    }
    $bytes = is_resource($row['image']) ? (string)stream_get_contents($row['image']) : (string)$row['image'];
    header('Content-Type: ' . $row['mime']);
    header('Content-Length: ' . strlen($bytes));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=31536000, immutable');
    echo $bytes;
}
