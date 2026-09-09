<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

/*
|--------------------------------------------------------------------------
| PROFILE SETTINGS
|--------------------------------------------------------------------------
|
| GET  /profile/settings
| PUT  /profile/settings
|
| User-specific settings. Both settings default to OFF at database level.
|--------------------------------------------------------------------------
*/

function get_profile_settings()
{
    $user = current_user(true);
    $userId = (int)$user['id'];

    // Settings are stored separately from the users table.
    // If a user has no settings row yet, both settings are OFF by default.
    $stmt = db()->prepare(
        'SELECT photo_privacy_enabled, strict_matching_enabled
         FROM user_settings
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    success_response(
        'Profile settings fetched successfully.',
        [
            'photoPrivacyEnabled' => $row !== false
                && (int)($row['photo_privacy_enabled'] ?? 0) === 1,
            'strictMatchingEnabled' => $row !== false
                && (int)($row['strict_matching_enabled'] ?? 0) === 1,
        ]
    );
}

function update_profile_settings()
{
    $user = current_user(true);
    $userId = (int)$user['id'];

    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);

    if (!is_array($data)) {
        error_response('Invalid request.', [], 400);
    }

    $hasPhoto = array_key_exists('photoPrivacyEnabled', $data);
    $hasStrict = array_key_exists('strictMatchingEnabled', $data);

    if (!$hasPhoto && !$hasStrict) {
        error_response('No settings supplied.', [], 422);
    }

    $sets = [];
    $params = [];

    if ($hasPhoto) {
        if (!is_bool($data['photoPrivacyEnabled']) && !in_array($data['photoPrivacyEnabled'], [0, 1, '0', '1'], true)) {
            error_response('Invalid photo privacy setting.', [], 422);
        }

        $sets[] = 'photo_privacy_enabled = ?';
        $params[] = filter_var($data['photoPrivacyEnabled'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    if ($hasStrict) {
        if (!is_bool($data['strictMatchingEnabled']) && !in_array($data['strictMatchingEnabled'], [0, 1, '0', '1'], true)) {
            error_response('Invalid matching setting.', [], 422);
        }

        $sets[] = 'strict_matching_enabled = ?';
        $params[] = filter_var($data['strictMatchingEnabled'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    // Keep settings in the dedicated user_settings table.
    // INSERT a row on first change; UPDATE only the supplied setting(s).
    $pdo = db();

    $check = $pdo->prepare(
        'SELECT id
         FROM user_settings
         WHERE user_id = ?
         LIMIT 1'
    );
    $check->execute([$userId]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $params[] = $userId;

        $stmt = $pdo->prepare(
            'UPDATE user_settings SET ' . implode(', ', $sets) . ' WHERE user_id = ? LIMIT 1'
        );
        $stmt->execute($params);
    } else {
        // Build a complete first row using OFF defaults for settings
        // that were not included in this partial update.
        $photoValue = 0;
        $strictValue = 0;

        if ($hasPhoto) {
            $photoValue = $params[0];
        }

        if ($hasStrict) {
            $strictValue = $hasPhoto ? $params[1] : $params[0];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO user_settings
                (user_id, photo_privacy_enabled, strict_matching_enabled)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $photoValue,
            $strictValue
        ]);
    }

    get_profile_settings();
}
