<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

/**
 * Delete an admin-selected user profile while preserving a non-sensitive
 * historical snapshot in deleteddata.
 *
 * IMPORTANT:
 * - Only an authenticated active admin can call this endpoint.
 * - The target must be a normal user, never an admin account.
 * - Database changes are transactional.
 * - Password hashes, auth token hashes and OTP hashes are NOT archived.
 * - Profile/verification uploaded files are deleted only after DB commit.
 */
function delete_admin_profile(): never
{
    $admin = current_user(true);

    if (($admin['role'] ?? '') !== 'admin') {
        error_response('Admin access required.', [], 403);
    }

    $pdo = db();

    $adminStmt = $pdo->prepare(
        'SELECT admin_role, status
         FROM admin_users
         WHERE user_id = ?
         LIMIT 1'
    );
    $adminStmt->execute([(int)$admin['id']]);
    $adminAccess = $adminStmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$adminAccess ||
        ($admin['account_status'] ?? '') !== 'active' ||
        ($adminAccess['status'] ?? '') !== 'active'
    ) {
        error_response('Admin access is not active.', [], 403);
    }

    $data = request_json();

    $memberId = trim((string)($data['member_id'] ?? ''));
    $reason = trim((string)($data['reason'] ?? ''));

    if (
        $memberId === '' ||
        !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $memberId)
    ) {
        error_response('Invalid member ID.', [], 422);
    }

    if ($reason === '') {
        error_response(
            'Delete reason is required.',
            ['reason' => 'Delete reason is required.'],
            422
        );
    }

    if (mb_strlen($reason) > 1000) {
        error_response(
            'Delete reason is too long.',
            ['reason' => 'Maximum 1000 characters allowed.'],
            422
        );
    }

    /*
     * Resolve the target using member_id and explicitly require role=user.
     * This prevents an admin account from being deleted accidentally.
     */
    $userStmt = $pdo->prepare(
        'SELECT *
         FROM users
         WHERE member_id = ?
           AND role = "user"
         LIMIT 1'
    );
    $userStmt->execute([$memberId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        error_response('User profile not found.', [], 404);
    }

    $userId = (int)$user['id'];
    $phone = (string)$user['phone'];

    /* ---------------------------------------------------------
     * Collect everything required for the archive BEFORE delete.
     * --------------------------------------------------------- */

    $profileStmt = $pdo->prepare(
        'SELECT * FROM profiles WHERE user_id = ? LIMIT 1'
    );
    $profileStmt->execute([$userId]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    $preferencesStmt = $pdo->prepare(
        'SELECT * FROM profile_preferences WHERE user_id = ? LIMIT 1'
    );
    $preferencesStmt->execute([$userId]);
    $preferences = $preferencesStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    $preferenceValuesStmt = $pdo->prepare(
        'SELECT * FROM preference_values WHERE user_id = ? ORDER BY id ASC'
    );
    $preferenceValuesStmt->execute([$userId]);
    $preferenceValues = $preferenceValuesStmt->fetchAll(PDO::FETCH_ASSOC);

    $photoStmt = $pdo->prepare(
        'SELECT * FROM profile_photos WHERE user_id = ? ORDER BY id ASC'
    );
    $photoStmt->execute([$userId]);
    $photos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);

    $paymentStmt = $pdo->prepare(
        'SELECT * FROM payments WHERE user_id = ? ORDER BY id ASC'
    );
    $paymentStmt->execute([$userId]);
    $payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

    $verificationStmt = $pdo->prepare(
        'SELECT * FROM verification_requests WHERE user_id = ? ORDER BY id ASC'
    );
    $verificationStmt->execute([$userId]);
    $verificationRequests = $verificationStmt->fetchAll(PDO::FETCH_ASSOC);

    $interestStmt = $pdo->prepare(
        'SELECT *
         FROM interests
         WHERE sender_user_id = ?
            OR receiver_user_id = ?
         ORDER BY id ASC'
    );
    $interestStmt->execute([$userId, $userId]);
    $interests = $interestStmt->fetchAll(PDO::FETCH_ASSOC);

    $shortlistStmt = $pdo->prepare(
        'SELECT *
         FROM shortlists
         WHERE user_id = ?
            OR shortlisted_user_id = ?
         ORDER BY id ASC'
    );
    $shortlistStmt->execute([$userId, $userId]);
    $shortlists = $shortlistStmt->fetchAll(PDO::FETCH_ASSOC);

    $feedbackStmt = $pdo->prepare(
        'SELECT * FROM feedback WHERE user_id = ? ORDER BY id ASC'
    );
    $feedbackStmt->execute([$userId]);
    $feedback = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

    $adminLinkStmt = $pdo->prepare(
        'SELECT id, user_id, admin_role, status, created_at, updated_at
         FROM admin_users
         WHERE user_id = ?
         ORDER BY id ASC'
    );
    $adminLinkStmt->execute([$userId]);
    $adminLinks = $adminLinkStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Keep only useful account fields. Never archive credentials.
     */
    $accountData = [
        'id' => $user['id'] ?? null,
        'member_id' => $user['member_id'] ?? null,
        'phone' => $user['phone'] ?? null,
        'role' => $user['role'] ?? null,
        'account_status' => $user['account_status'] ?? null,
        'otp_verified' => $user['otp_verified'] ?? null,
        'created_at' => $user['created_at'] ?? null,
        'updated_at' => $user['updated_at'] ?? null,
        'last_login_at' => $user['last_login_at'] ?? null,
    ];

    /*
     * Verification may contain an uploaded home-verification photo.
     * Save its path in the archive before removing the physical file.
     */
    $filePaths = [];
    foreach ($photos as $photo) {
        if (!empty($photo['file_path'])) {
            $filePaths[] = (string)$photo['file_path'];
        }
    }
    foreach ($verificationRequests as $verification) {
        if (!empty($verification['verification_photo_path'])) {
            $filePaths[] = (string)$verification['verification_photo_path'];
        }
    }

    $filePaths = array_values(array_unique($filePaths));

    $archive = [
        'account' => $accountData,
        'profile' => $profile,
        'preferences' => $preferences,
        'preference_values' => $preferenceValues,
        'photos' => $photos,
        'payments' => $payments,
        'verification_requests' => $verificationRequests,
        'interests' => $interests,
        'shortlists' => $shortlists,
        'feedback' => $feedback,
        'admin_links' => $adminLinks,
        'file_paths' => $filePaths,
    ];

    try {
        $profileJson = json_encode(
            $archive['profile'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $preferencesJson = json_encode(
            $archive['preferences'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $preferenceValuesJson = json_encode(
            $archive['preference_values'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $relatedJson = json_encode(
            [
                'photos' => $archive['photos'],
                'payments' => $archive['payments'],
                'verification_requests' => $archive['verification_requests'],
                'interests' => $archive['interests'],
                'shortlists' => $archive['shortlists'],
                'feedback' => $archive['feedback'],
                'admin_links' => $archive['admin_links'],
                'file_paths' => $archive['file_paths'],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $accountJson = json_encode(
            $archive['account'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    } catch (JsonException $e) {
        error_response('Unable to archive profile data.', [], 500);
    }

    $pdo->beginTransaction();

    try {
        /* -----------------------------------------------------
         * 1. Archive first. If archive fails, nothing is deleted.
         * ----------------------------------------------------- */
        $archiveStmt = $pdo->prepare(
            'INSERT INTO deleteddata (
                original_user_id,
                member_id,
                phone,
                full_name,
                place,
                delete_reason,
                deleted_by_admin_user_id,
                account_data,
                profile_data,
                preferences_data,
                preference_values_data,
                related_data,
                deleted_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $archiveStmt->execute([
            $userId,
            $memberId,
            $phone,
            $profile['full_name'] ?? null,
            $profile['place'] ?? null,
            $reason,
            (int)$admin['id'],
            $accountJson,
            $profileJson,
            $preferencesJson,
            $preferenceValuesJson,
            $relatedJson,
        ]);

        /* -----------------------------------------------------
         * 2. Delete child/reference rows explicitly.
         *    This is safer than relying on mixed FK behaviours.
         * ----------------------------------------------------- */

        // OTP records are keyed by phone, not user_id.
        $pdo->prepare(
            'DELETE FROM otp_verifications WHERE phone = ?'
        )->execute([$phone]);

        $pdo->prepare(
            'DELETE FROM auth_sessions WHERE user_id = ?'
        )->execute([$userId]);

        $pdo->prepare(
            'DELETE FROM interests
             WHERE sender_user_id = ?
                OR receiver_user_id = ?'
        )->execute([$userId, $userId]);

        $pdo->prepare(
            'DELETE FROM shortlists
             WHERE user_id = ?
                OR shortlisted_user_id = ?'
        )->execute([$userId, $userId]);

        $pdo->prepare(
            'DELETE FROM verification_requests WHERE user_id = ?'
        )->execute([$userId]);

        // payments.user_id has no ON DELETE CASCADE in the current schema.
        $pdo->prepare(
            'DELETE FROM payments WHERE user_id = ?'
        )->execute([$userId]);

        // These are also explicit even though the current schema cascades them.
        $pdo->prepare(
            'DELETE FROM feedback WHERE user_id = ?'
        )->execute([$userId]);

        $pdo->prepare(
            'DELETE FROM preference_values WHERE user_id = ?'
        )->execute([$userId]);

        $pdo->prepare(
            'DELETE FROM profile_preferences WHERE user_id = ?'
        )->execute([$userId]);

        $pdo->prepare(
            'DELETE FROM profile_photos WHERE user_id = ?'
        )->execute([$userId]);

        $pdo->prepare(
            'DELETE FROM profiles WHERE user_id = ?'
        )->execute([$userId]);

        /*
         * Admin link should normally not exist for role=user, but delete it
         * defensively so the final users delete cannot leave an orphan.
         */
        $pdo->prepare(
            'DELETE FROM admin_users WHERE user_id = ?'
        )->execute([$userId]);

        $deleteUser = $pdo->prepare(
            'DELETE FROM users
             WHERE id = ?
               AND role = "user"'
        );
        $deleteUser->execute([$userId]);

        if ($deleteUser->rowCount() !== 1) {
            throw new RuntimeException('User account could not be deleted.');
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_response(
            'Profile deletion failed. No data was deleted.',
            [],
            500
        );
    }

    /*
     * Physical files are removed only after the DB transaction succeeds.
     * A missing file does not make the already-completed DB deletion fail.
     */
    foreach ($filePaths as $filePath) {
        safe_delete_uploaded_file($filePath);
    }

    success_response(
        'Profile deleted successfully.',
        [
            'member_id' => $memberId,
        ]
    );
}

/**
 * Delete only files that resolve inside this API's uploads directory.
 */
function safe_delete_uploaded_file(string $path): void
{
    $path = trim($path);
    if ($path === '') {
        return;
    }

    $normalized = str_replace('\\', '/', $path);

    if (preg_match('/^https?:\/\//i', $normalized)) {
        return;
    }

    $normalized = preg_replace('#/+#', '/', $normalized) ?? $normalized;

    // Reject traversal before building a filesystem path.
    if (str_contains($normalized, '../') || str_contains($normalized, '/..')) {
        return;
    }

    $relative = '';

    $uploadsMarker = 'uploads/';
    $uploadsPosition = strpos($normalized, $uploadsMarker);

    if ($uploadsPosition !== false) {
        $relative = substr($normalized, $uploadsPosition);
    }

    if ($relative === '' || !str_starts_with($relative, 'uploads/')) {
        return;
    }

    $fullPath = __DIR__ . '/../' . $relative;
    $uploadsRoot = realpath(__DIR__ . '/../uploads');
    $fileRealPath = realpath($fullPath);

    if ($uploadsRoot === false || $fileRealPath === false) {
        return;
    }

    $uploadsRoot = rtrim(str_replace('\\', '/', $uploadsRoot), '/') . '/';
    $fileRealPath = str_replace('\\', '/', $fileRealPath);

    if (!str_starts_with($fileRealPath, $uploadsRoot)) {
        return;
    }

    if (is_file($fileRealPath)) {
        @unlink($fileRealPath);
    }
}
