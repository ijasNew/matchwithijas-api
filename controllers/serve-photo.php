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
| access via .htaccess. This script is the ONLY way to reach an
| original photo, and it enforces auth + home-verified status first.
|
| Usage: GET /matchwithijas-api/serve-photo.php?id=123
|
*/

function serve_profile_photo(): never
{
    // Must be logged in. current_user() already sends a 401 and exits
    // if the token is missing/invalid.
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
    | TODO: confirm this matches your actual business rule.
    | As written, this checks the VIEWER's own home_verified flag
    | (matches the bool signature photo_url_for_viewer() already used).
    |
    | If "Home Verified" is really meant to be a per-connection/match
    | status between this viewer and this specific profile owner
    | (not a global flag on the viewer), replace this block with a
    | check against your matches/connections table instead.
    |
    */

    $viewerHomeVerified = viewer_is_home_verified($viewerId);

    $relativePath = $viewerHomeVerified
        ? ltrim(str_replace('\\', '/', (string)$photo['file_path']), '/')
        : ensure_blurred_photo((string)$photo['file_path']);

    if ($relativePath === null || $relativePath === '') {
        error_response('Photo unavailable.', [], 404);
    }

    $absolutePath = dirname(__DIR__) . '/' . $relativePath;
    $realBase = realpath(dirname(__DIR__) . '/uploads/profile-photos');
    $realTarget = realpath($absolutePath);

    // Defence in depth: make sure the resolved path is still inside
    // the uploads/profile-photos directory (blocks path traversal
    // even if $relativePath were ever manipulated upstream).
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

    // Stop any accidental output buffering / whitespace before binary data.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string)filesize($realTarget));
    header('X-Content-Type-Options: nosniff');
    // Private cache only: this response depends on the requester's
    // verification status, so shared/proxy caches must not store it.
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

serve_profile_photo();
