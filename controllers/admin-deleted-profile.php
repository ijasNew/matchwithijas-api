<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

/**
 * Read-only admin access to deleted profile archives.
 *
 * This controller NEVER deletes, updates, or restores deleteddata rows.
 * It only reads the archive created by admin-profile-delete.php.
 */
function require_deleted_profile_admin(): array
{
    $admin = current_user(true);

    if (($admin['role'] ?? '') !== 'admin') {
        error_response('Admin access required.', [], 403);
    }

    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT admin_role, status
         FROM admin_users
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([(int)$admin['id']]);
    $adminAccess = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$adminAccess ||
        ($admin['account_status'] ?? '') !== 'active' ||
        ($adminAccess['status'] ?? '') !== 'active'
    ) {
        error_response('Admin access is not active.', [], 403);
    }

    return $admin;
}

function decode_deleted_json(?string $value): mixed
{
    if ($value === null || trim($value) === '') {
        return null;
    }

    try {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        return null;
    }
}

/**
 * GET /admin/deleted-profile
 *
 * Returns the archive list only. Detailed archived data is intentionally
 * fetched by a separate endpoint when the admin opens View Profile.
 */
function get_admin_deleted_profiles(): never
{
    require_deleted_profile_admin();

    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            id,
            original_user_id,
            member_id,
            phone,
            full_name,
            place,
            delete_reason,
            delete_source,
            deleted_by_admin_user_id,
            deleted_at
         FROM deleteddata
         ORDER BY deleted_at DESC, id DESC'
    );

    $deletedProfiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    success_response(
        'Deleted profiles fetched successfully.',
        [
            'deleted_profiles' => $deletedProfiles,
            'total' => count($deletedProfiles)
        ]
    );
}

/**
 * GET /admin/deleted-profile/{id}
 *
 * Returns every archived data block stored by the deletion process.
 * Credentials are not present because the deletion process deliberately
 * excludes password/auth/OTP hashes from the archive.
 */
function get_admin_deleted_profile(int $deletedId): never
{
    require_deleted_profile_admin();

    if ($deletedId < 1) {
        error_response('Invalid deleted profile ID.', [], 422);
    }

    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT
            id,
            original_user_id,
            member_id,
            phone,
            full_name,
            place,
            delete_reason,
            delete_source,
            deleted_by_admin_user_id,
            account_data,
            profile_data,
            preferences_data,
            preference_values_data,
            related_data,
            deleted_at
         FROM deleteddata
         WHERE id = ?
         LIMIT 1'
    );

    $stmt->execute([$deletedId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        error_response('Deleted profile record not found.', [], 404);
    }

    $archive = [
        'id' => (int)$row['id'],
        'original_user_id' => (int)$row['original_user_id'],
        'member_id' => $row['member_id'],
        'phone' => $row['phone'],
        'full_name' => $row['full_name'],
        'place' => $row['place'],
        'delete_reason' => $row['delete_reason'],
        'delete_source' => $row['delete_source'] ?? null,
        'deleted_by_admin_user_id' => $row['deleted_by_admin_user_id'] !== null
            ? (int)$row['deleted_by_admin_user_id']
            : null,
        'deleted_at' => $row['deleted_at'],
        'account' => decode_deleted_json($row['account_data']),
        'profile' => decode_deleted_json($row['profile_data']),
        'preferences' => decode_deleted_json($row['preferences_data']),
        'preference_values' => decode_deleted_json($row['preference_values_data']),
        'related' => decode_deleted_json($row['related_data'])
    ];

    success_response(
        'Deleted profile archive fetched successfully.',
        ['profile' => $archive]
    );
}
