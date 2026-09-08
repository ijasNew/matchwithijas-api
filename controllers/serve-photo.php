<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/photo-security.php';

/*
|--------------------------------------------------------------------------
| SERVE PROFILE PHOTO (GATEKEEPER)
|--------------------------------------------------------------------------
|
| Originals under uploads/profile-photos/ are blocked from direct web
| access via .htaccess. This is the ONLY way to reach a photo, and it
| enforces auth first, then decides original vs blurred.
|
| Routed via index.php:
|   GET /matchwithijas-api/serve-photo?id=123
|
| IMPORTANT: this file lives in controllers/, same as the other
| controller files, so dirname(__DIR__) below correctly resolves to
| the matchwithijas-api root (where uploads/ lives) - same pattern
| already used in photo-security.php and profile-photos.php.
|
*/

function serve_profile_photo(): never
{
    $viewer = current_user(true);
    $viewerId = (int)$viewer['id'];

    $photoId = (int)($_GET['id'] ?? 0);

    if ($photoId <= 0) {
        error_response('Invalid photo.', [], 404);
    }

    $pdo = db();

    $stmt = $pdo->prepare(
        "SELECT id, user_id, file_path
         FROM profile_photos
         WHERE id = ? AND status = 'active'
         LIMIT 1"
    );
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$photo) {
        error_response('Photo not found.', [], 404);
    }

    $ownerId = (int)$photo['user_id'];

    /*
    |--------------------------------------------------------------------------
    | IS THIS VIEWER ALLOWED TO SEE THE ORIGINAL?
    |--------------------------------------------------------------------------
    |
    |   1. Admin (users.role = 'admin')            -> always original
    |   2. Viewer's OWN photo                       -> always original
    |   3. Viewer is Home Verified                  -> original
    |   Otherwise                                   -> blurred preview
    |
    */

    $isAdmin = (string)($viewer['role'] ?? '') === 'admin';
    $isOwnPhoto = $viewerId === $ownerId;

    $canSeeOriginal =
        $isAdmin ||
        $isOwnPhoto ||
        viewer_is_home_verified($viewerId);

    $relativePath = $canSeeOriginal
        ? ltrim(str_replace('\\', '/', (string)$photo['file_path']), '/')
        : ensure_blurred_photo((string)$photo['file_path']);

    if ($relativePath === null || $relativePath === '') {
        error_response('Photo unavailable.', [], 404);
    }

    $absolutePath = dirname(__DIR__) . '/' . $relativePath;
    $realBase = realpath(dirname(__DIR__) . '/uploads/profile-photos');
    $realTarget = realpath($absolutePath);

    // Defence in depth: resolved path must stay inside
    // uploads/profile-photos (blocks path traversal).
    if (
        $realBase === false ||
        $realTarget === false ||
        !str_starts_with($realTarget, $realBase . DIRECTORY_SEPARATOR)
    ) {
        error_response('Photo not found.', [], 404);
    }

    if (!is_file($realTarget)) {
        error_response('Photo not found.', [], 404);
    }

    $mime = @mime_content_type($realTarget) ?: 'image/jpeg';
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mime, $allowedMime, true)) {
        error_response('Photo not found.', [], 404);
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string)filesize($realTarget));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=300');

    readfile($realTarget);
    exit;
}

/*
|--------------------------------------------------------------------------
| VIEWER HOME-VERIFIED CHECK
|--------------------------------------------------------------------------
*/

function viewer_is_home_verified(int $viewerId): bool
{
    $stmt = db()->prepare(
        'SELECT home_verified FROM profiles WHERE user_id = ? LIMIT 1'
    );
    $stmt->execute([$viewerId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false && (int)($row['home_verified'] ?? 0) === 1;
}