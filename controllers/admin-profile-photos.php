<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/photo-security.php';

/*
|--------------------------------------------------------------------------
| ADMIN PROFILE PHOTOS
|--------------------------------------------------------------------------
|
| Admin-only photo management for an existing member profile.
| This intentionally does NOT use photo_profile(), because that helper
| locks normal user editing after Home Verification. Admin profile editing
| already has its own access control and is allowed to manage the member's
| photos.
|
| Routes:
|   GET  /admin/profiles/{memberId}/photos
|   POST /admin/profiles/{memberId}/photos
|
| POST uses photo_slots JSON so existing and newly uploaded photos can keep
| their exact order. The first slot becomes the primary photo.
|
*/

function admin_profile_photo_member(string $memberId): array
{
    $memberId = trim($memberId);

    if ($memberId === '' || !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $memberId)) {
        error_response('Invalid member ID.', [], 422);
    }

    $stmt = db()->prepare(
        'SELECT p.user_id, u.member_id
         FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         WHERE u.member_id = ? AND u.role = \'user\'
         LIMIT 1'
    );
    $stmt->execute([$memberId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        error_response('Profile not found.', [], 404);
    }

    return [
        'user_id' => (int)$row['user_id'],
        'member_id' => (string)$row['member_id'],
    ];
}

function admin_profile_photo_list(int $userId): array
{
    $stmt = db()->prepare(
        "SELECT id, file_path, is_primary, display_order, created_at
         FROM profile_photos
         WHERE user_id = ? AND status = 'active'
         ORDER BY display_order ASC, id ASC"
    );
    $stmt->execute([$userId]);

    return array_map(
        static function (array $row): array {
            return [
                'id' => (int)$row['id'],
                'url' => '/matchwithijas-api/serve-photo?id=' . (int)$row['id'],
                'is_primary' => (int)$row['is_primary'],
                'display_order' => (int)$row['display_order'],
                'created_at' => $row['created_at'],
            ];
        },
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );
}

function get_admin_profile_photos(string $memberId): never
{
    require_admin_profile_access();

    $member = admin_profile_photo_member($memberId);
    $photos = admin_profile_photo_list($member['user_id']);

    success_response(
        'Admin profile photos fetched successfully.',
        [
            'member_id' => $member['member_id'],
            'photos' => $photos,
            'count' => count($photos),
            'max_photos' => 4,
        ]
    );
}

function save_admin_profile_photos(string $memberId): never
{
    require_admin_profile_access();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_response('Invalid request method.', [], 405);
    }

    $member = admin_profile_photo_member($memberId);
    $userId = $member['user_id'];

    $photoSlotsRaw = $_POST['photo_slots'] ?? '';
    if (!is_string($photoSlotsRaw) || trim($photoSlotsRaw) === '') {
        error_response('Invalid photo order data.', [], 422);
    }

    try {
        $photoSlots = json_decode($photoSlotsRaw, true, 512, JSON_THROW_ON_ERROR);
    } catch (Throwable $e) {
        error_response('Invalid photo order data.', [], 422);
    }

    if (!is_array($photoSlots)) {
        error_response('Invalid photo order data.', [], 422);
    }

    if (count($photoSlots) < 1) {
        error_response('Please add at least one profile photo.', [], 422);
    }

    if (count($photoSlots) > 4) {
        error_response('You can upload a maximum of 4 photos.', [], 422);
    }

    $files = $_FILES['photos'] ?? null;
    $fileCount = 0;

    if ($files !== null) {
        if (
            !isset(
                $files['name'],
                $files['type'],
                $files['tmp_name'],
                $files['error'],
                $files['size']
            ) || !is_array($files['name'])
        ) {
            error_response('Invalid photo upload data.', [], 422);
        }

        $fileCount = count($files['name']);
    }

    $pdo = db();

    $existingStmt = $pdo->prepare(
        "SELECT id, file_path
         FROM profile_photos
         WHERE user_id = ? AND status = 'active'
         ORDER BY display_order ASC, id ASC"
    );
    $existingStmt->execute([$userId]);
    $existing = $existingStmt->fetchAll(PDO::FETCH_ASSOC);

    $existingById = [];
    foreach ($existing as $row) {
        $existingById[(int)$row['id']] = $row;
    }

    $referencedExistingIds = [];
    $referencedFileIndexes = [];

    foreach ($photoSlots as $index => $slot) {
        if (!is_array($slot) || !isset($slot['type'])) {
            error_response('Invalid photo order data.', ['index' => $index], 422);
        }

        $type = (string)$slot['type'];

        if ($type === 'existing') {
            $id = isset($slot['id']) ? (int)$slot['id'] : 0;

            if ($id <= 0 || !isset($existingById[$id])) {
                error_response('Invalid existing profile photo selected.', ['index' => $index], 422);
            }

            if (isset($referencedExistingIds[$id])) {
                error_response('Duplicate profile photo selected.', ['photo_id' => $id], 422);
            }

            $referencedExistingIds[$id] = true;
            continue;
        }

        if ($type === 'new') {
            $fileIndex = isset($slot['file_index']) ? (int)$slot['file_index'] : -1;

            if ($fileIndex < 0 || $fileIndex >= $fileCount) {
                error_response('Invalid new profile photo selected.', ['index' => $index], 422);
            }

            if (isset($referencedFileIndexes[$fileIndex])) {
                error_response('Duplicate new profile photo selected.', ['file_index' => $fileIndex], 422);
            }

            $referencedFileIndexes[$fileIndex] = true;
            continue;
        }

        error_response('Invalid photo order data.', ['index' => $index], 422);
    }

    if (count($referencedFileIndexes) !== $fileCount) {
        error_response('Invalid photo upload list.', [], 422);
    }

    $uploadDir = dirname(__DIR__) . '/uploads/profile-photos';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        error_response('Unable to create photo upload directory.', [], 500);
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $newFiles = [];

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

            $newFiles[$i] = [
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
            if (!isset($referencedExistingIds[$id])) {
                $removedPaths[] = (string)$row['file_path'];
                $pdo->prepare(
                    "UPDATE profile_photos
                     SET status = 'deleted'
                     WHERE id = ? AND user_id = ?"
                )->execute([$id, $userId]);
            }
        }

        foreach ($photoSlots as $order => $slot) {
            $isPrimary = $order === 0 ? 1 : 0;

            if ((string)$slot['type'] === 'existing') {
                $id = (int)$slot['id'];

                $pdo->prepare(
                    "UPDATE profile_photos
                     SET display_order = ?, is_primary = ?
                     WHERE id = ? AND user_id = ? AND status = 'active'"
                )->execute([$order, $isPrimary, $id, $userId]);

                continue;
            }

            $fileIndex = (int)$slot['file_index'];
            $file = $newFiles[$fileIndex];
            $filename = bin2hex(random_bytes(16)) . '.' . $file['extension'];
            $absolutePath = $uploadDir . '/' . $filename;
            $relativePath = 'uploads/profile-photos/' . $filename;

            if (!move_uploaded_file($file['tmp'], $absolutePath)) {
                throw new RuntimeException('Unable to store uploaded photo.');
            }

            $createdPaths[] = $absolutePath;

            $blurredRelativePath = ensure_blurred_photo($relativePath);
            if ($blurredRelativePath === null) {
                throw new RuntimeException('Unable to create blurred photo preview.');
            }

            $blurredAbsolutePath = dirname(__DIR__) . '/' . $blurredRelativePath;
            $createdPaths[] = $blurredAbsolutePath;

            $stmt = $pdo->prepare(
                "INSERT INTO profile_photos
                    (user_id, file_path, is_primary, display_order, status)
                 VALUES (?, ?, ?, ?, 'active')"
            );
            $stmt->execute([
                $userId,
                $relativePath,
                $isPrimary,
                $order,
            ]);
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
        $absolutePath = dirname(__DIR__) . '/' . ltrim(
            str_replace('\\', '/', $relativePath),
            '/'
        );

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }

        $blurredRelativePath = photo_blurred_relative_path($relativePath);
        if ($blurredRelativePath !== '') {
            $blurredAbsolutePath = dirname(__DIR__) . '/' . $blurredRelativePath;
            if (is_file($blurredAbsolutePath)) {
                @unlink($blurredAbsolutePath);
            }
        }
    }

    $photos = admin_profile_photo_list($userId);

    success_response(
        'Profile photos updated successfully.',
        [
            'member_id' => $member['member_id'],
            'photos' => $photos,
            'count' => count($photos),
            'max_photos' => 4,
        ]
    );
}
