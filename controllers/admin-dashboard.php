<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

function require_admin_dashboard_access(): void
{
    $admin = current_user(true);
    if (($admin['role'] ?? '') !== 'admin') {
        error_response('Admin access required.', [], 403);
    }

    $stmt = db()->prepare('SELECT status FROM admin_users WHERE user_id = ? LIMIT 1');
    $stmt->execute([(int)$admin['id']]);
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$access || ($admin['account_status'] ?? '') !== 'active' || ($access['status'] ?? '') !== 'active') {
        error_response('Admin access is not active.', [], 403);
    }
}

function get_admin_dashboard(): never
{
    require_admin_dashboard_access();
    $pdo = db();

    $total = (int)$pdo->query(
        'SELECT COUNT(*) FROM profiles p INNER JOIN users u ON u.id = p.user_id WHERE u.role = "user" AND p.registration_completed = 1'
    )->fetchColumn();

    $basic = (int)$pdo->query(
        'SELECT COUNT(*) FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         INNER JOIN (
             SELECT pay.user_id, pay.plan_id
             FROM payments pay
             INNER JOIN (SELECT user_id, MAX(id) max_id FROM payments WHERE payment_status = "success" GROUP BY user_id) latest
               ON latest.user_id = pay.user_id AND latest.max_id = pay.id
         ) latest_success ON latest_success.user_id = u.id
         INNER JOIN plans pl ON pl.id = latest_success.plan_id
         WHERE u.role = "user" AND p.registration_completed = 1 AND pl.name = "Basic"'
    )->fetchColumn();

    $verified = (int)$pdo->query(
        'SELECT COUNT(*) FROM profiles p INNER JOIN users u ON u.id = p.user_id WHERE u.role = "user" AND p.registration_completed = 1 AND p.home_verified = 1'
    )->fetchColumn();

    $pendingVerification = (int)$pdo->query(
        'SELECT COUNT(*) FROM verification_requests vr
         INNER JOIN users u ON u.id = vr.user_id
         WHERE u.role = "user" AND vr.status IN ("pending", "in_progress")'
    )->fetchColumn();

    $incomplete = (int)$pdo->query(
        'SELECT COUNT(*) FROM profiles p INNER JOIN users u ON u.id = p.user_id WHERE u.role = "user" AND p.registration_completed = 0'
    )->fetchColumn();

    $stmt = $pdo->query(
        'SELECT u.member_id, p.full_name, p.gender, p.place, p.created_at,
                p.profile_status, COALESCE(pl.name, "Free") AS plan
         FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         LEFT JOIN (
             SELECT pay.user_id, pay.plan_id
             FROM payments pay
             INNER JOIN (SELECT user_id, MAX(id) max_id FROM payments WHERE payment_status = "success" GROUP BY user_id) latest
               ON latest.user_id = pay.user_id AND latest.max_id = pay.id
         ) latest_success ON latest_success.user_id = u.id
         LEFT JOIN plans pl ON pl.id = latest_success.plan_id
         WHERE u.role = "user" AND p.registration_completed = 1
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 5'
    );
    $recentProfiles = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $recentProfiles[] = [
            'id' => $row['member_id'],
            'name' => $row['full_name'],
            'gender' => $row['gender'],
            'place' => $row['place'],
            'registeredDate' => $row['created_at'],
            'status' => match ($row['profile_status']) {
                'verified' => 'Verified', 'pending_verification' => 'Pending', 'new' => 'New',
                'rejected' => 'Rejected', 'blocked' => 'Blocked', default => 'New'
            },
            'plan' => $row['plan'] ?: 'Free'
        ];
    }

    $stmt = $pdo->query(
        'SELECT vr.id, u.member_id, p.full_name, p.place, vr.requested_at, vr.status
         FROM verification_requests vr
         INNER JOIN users u ON u.id = vr.user_id
         INNER JOIN profiles p ON p.user_id = vr.user_id
         WHERE u.role = "user" AND vr.status IN ("pending", "in_progress")
         ORDER BY vr.requested_at DESC, vr.id DESC LIMIT 5'
    );
    $verificationRequests = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $verificationRequests[] = [
            'id' => (string)$row['member_id'],
            'name' => $row['full_name'],
            'place' => $row['place'],
            'requestedDate' => $row['requested_at'],
            'status' => $row['status'] === 'in_progress' ? 'In Progress' : 'Pending'
        ];
    }

    success_response('Admin dashboard fetched successfully.', [
        'stats' => [
            'totalProfiles' => $total,
            'paidUsers' => $basic,
            'homeVerified' => $verified,
            'pendingVerification' => $pendingVerification,
            'freeUsers' => max(0, $total - $basic),
            'other' => $incomplete
        ],
        'recentProfiles' => $recentProfiles,
        'verificationRequests' => $verificationRequests
    ]);
}
