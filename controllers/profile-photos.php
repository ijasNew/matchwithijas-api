<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

function photo_user(): array
{
    return current_user(true);
}

function photo_profile(int $userId): array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM profiles WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        error_response('Profile not found.', [], 404);
    }

    if ((int)($profile['home_verified'] ?? 0) === 1) {
        error_response('Profile editing is locked after Home Verification.', [], 403);
    }

    return $profile;
}

function photo_public_url(string $relativePath): string
{
    return ltrim(str_replace('\\', '/', $relativePath), '/');
}

function get_profile_photos(): never
{
    $user = photo_user();
    $userId = (int)$user['id'];

    $pdo = db();
    $stmt = $pdo->prepare(
        "SELECT id, file_path, is_primary, display_order, status, created_at
         FROM profile_photos
         WHERE user_id = ? AND status = 'active'
         ORDER BY display_order ASC, id ASC"
    );
    $stmt->execute([$userId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $photos = array_map(static function (array $row): array {
        return [
            'id' => (int)$row['id'],
            'url' => photo_public_url((string)$row['file_path']),
            'is_primary' => (int)$row['is_primary'],
            'display_order' => (int)$row['display_order'],
            'created_at' => $row['created_at'],
        ];
    }, $rows);

    success_response('Profile photos fetched successfully.', [
        'photos' => $photos,
        'count' => count($photos),
        'max_photos' => 4,
    ]);
}

function save_profile_photos(): never
{
    $user = photo_user();
    $userId = (int)$user['id'];
    photo_profile($userId);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_response('Invalid request method.', [], 405);
    }

    $pdo = db();

    $retainedIds = $_POST['retained_ids'] ?? [];
    if (!is_array($retainedIds)) {
        error_response('Invalid retained photo list.', [], 422);
    }

    $retainedIds = array_values(array_unique(array_map('intval', $retainedIds)));
    $retainedIds = array_values(array_filter($retainedIds, static fn(int $id): bool => $id > 0));

    $files = $_FILES['photos'] ?? null;
    $fileCount = 0;

    if ($files !== null) {
        if (!isset($files['name'], $files['type'], $files['tmp_name'], $files['error'], $files['size']) || !is_array($files['name'])) {
            error_response('Invalid photo upload data.', [], 422);
        }
        $fileCount = count($files['name']);
    }

    $stmt = $pdo->prepare(
        "SELECT id, file_path
         FROM profile_photos
         WHERE user_id = ? AND status = 'active'
         ORDER BY display_order ASC, id ASC"
    );
    $stmt->execute([$userId]);
    $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $existingIds = array_map(static fn(array $row): int => (int)$row['id'], $existing);
    foreach ($retainedIds as $id) {
        if (!in_array($id, $existingIds, true)) {
            error_response('Invalid profile photo selected.', ['photo_id' => $id], 422);
        }
    }

    if (count($retainedIds) + $fileCount < 1) {
        error_response('Please add at least one profile photo.', [], 422);
    }

    if (count($retainedIds) + $fileCount > 4) {
        error_response('You can upload a maximum of 4 photos.', [], 422);
    }

    $uploadDir = dirname(__DIR__) . '/uploads/profile-photos';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        error_response('Unable to create photo upload directory.', [], 500);
    }

    $newFiles = [];
    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if ($fileCount > 0) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < $fileCount; $i++) {
            $error = (int)$files['error'][$i];
            $size = (int)$files['size'][$i];
            $tmp = (string)$files['tmp_name'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                error_response('One or more photos could not be uploaded.', ['index' => $i], 422);
            }
            if ($size <= 0 || $size > 5 * 1024 * 1024) {
                error_response('Each photo must be 5 MB or smaller.', ['index' => $i], 422);
            }
            if (!is_uploaded_file($tmp)) {
                error_response('Invalid uploaded photo.', ['index' => $i], 422);
            }

            $mime = $finfo->file($tmp);
            if (!isset($allowedMime[$mime])) {
                error_response('Only JPG, PNG and WebP images are allowed.', ['index' => $i], 422);
            }

            $imageInfo = @getimagesize($tmp);
            if ($imageInfo === false || empty($imageInfo[0]) || empty($imageInfo[1])) {
                error_response('One or more photos are invalid or corrupted.', ['index' => $i], 422);
            }

            $newFiles[] = [
                'tmp' => $tmp,
                'extension' => $allowedMime[$mime],
            ];
        }
    }

    $createdPaths = [];
    $removedPaths = [];

    try {
        $pdo->beginTransaction();

        foreach ($existing as $row) {
            $id = (int)$row['id'];
            if (!in_array($id, $retainedIds, true)) {
                $removedPaths[] = (string)$row['file_path'];
                $pdo->prepare("UPDATE profile_photos SET status = 'deleted' WHERE id = ? AND user_id = ?")->execute([$id, $userId]);
            }
        }

        $order = 0;
        foreach ($retainedIds as $id) {
            $isPrimary = $order === 0 ? 1 : 0;
            $pdo->prepare(
                'UPDATE profile_photos SET display_order = ?, is_primary = ? WHERE id = ? AND user_id = ? AND status = \'active\''
            )->execute([$order, $isPrimary, $id, $userId]);
            $order++;
        }

        foreach ($newFiles as $file) {
            $filename = bin2hex(random_bytes(16)) . '.' . $file['extension'];
            $absolutePath = $uploadDir . '/' . $filename;
            $relativePath = 'uploads/profile-photos/' . $filename;

            if (!move_uploaded_file($file['tmp'], $absolutePath)) {
                throw new RuntimeException('Unable to store uploaded photo.');
            }

            $createdPaths[] = $absolutePath;
            $isPrimary = $order === 0 ? 1 : 0;

            $stmt = $pdo->prepare(
                'INSERT INTO profile_photos (user_id, file_path, is_primary, display_order, status) VALUES (?, ?, ?, ?, \'active\')'
            );
            $stmt->execute([$userId, $relativePath, $isPrimary, $order]);
            $order++;
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        foreach ($createdPaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        error_response('Unable to save profile photos.', [], 500);
    }

    foreach ($removedPaths as $relativePath) {
        $absolutePath = dirname(__DIR__) . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    get_profile_photos();
}
