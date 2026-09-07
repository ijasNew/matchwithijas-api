<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

function require_admin_feedback_access(): array
{
    $admin = current_user(true);
    if (($admin['role'] ?? '') !== 'admin') error_response('Admin access required.', [], 403);

    $stmt = db()->prepare(
        'SELECT au.admin_role, au.status AS admin_status
         FROM admin_users au WHERE au.user_id = ? LIMIT 1'
    );
    $stmt->execute([$admin['id']]);
    $adminAccess = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminAccess) error_response('Admin access not found.', [], 403);
    if (($admin['account_status'] ?? '') !== 'active') error_response('Admin account is not active.', [], 403);
    if (($adminAccess['admin_status'] ?? '') !== 'active') error_response('Admin access is not active.', [], 403);

    return ['admin' => $admin, 'admin_access' => $adminAccess];
}

function get_admin_feedback(): never
{
    require_admin_feedback_access();

    $stmt = db()->prepare(
        "SELECT
            f.id,
            f.user_id,
            u.member_id,
            u.phone,
            COALESCE(p.full_name, u.member_id) AS full_name,
            f.feedback_type,
            f.message,
            f.status,
            f.admin_note,
            f.created_at,
            f.updated_at
         FROM feedback f
         INNER JOIN users u ON u.id = f.user_id
         LEFT JOIN profiles p ON p.user_id = f.user_id
         ORDER BY f.created_at DESC, f.id DESC"
    );
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $feedbacks = [];
    foreach ($rows as $row) {
        $feedbacks[] = [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'member_id' => (string)$row['member_id'],
            'name' => (string)$row['full_name'],
            'phone' => (string)$row['phone'],
            'feedback_type' => (string)$row['feedback_type'],
            'message' => (string)$row['message'],
            'status' => (string)$row['status'],
            'admin_note' => $row['admin_note'] !== null ? (string)$row['admin_note'] : null,
            'created_at' => (string)$row['created_at'],
            'updated_at' => (string)$row['updated_at']
        ];
    }

    success_response(
        'Admin feedback fetched successfully.',
        ['feedbacks' => $feedbacks, 'total' => count($feedbacks)]
    );
}
