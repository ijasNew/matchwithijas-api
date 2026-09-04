<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


function admin_login(): never
{
    $data = request_json();

    $username = trim((string)($data['username'] ?? ''));
    $password = (string)($data['password'] ?? '');

    if ($username === '') {
        error_response(
            'Username is required.',
            ['username' => 'Username is required.'],
            422
        );
    }

    if ($password === '') {
        error_response(
            'Password is required.',
            ['password' => 'Password is required.'],
            422
        );
    }

    $pdo = db();

    /*
     * Admin login identifier:
     * users.member_id
     *
     * Admin access is confirmed by:
     * 1. users.role = admin
     * 2. admin_users record exists
     * 3. users.account_status = active
     * 4. admin_users.status = active
     */

    $stmt = $pdo->prepare(
        'SELECT
            u.id,
            u.member_id,
            u.phone,
            u.password_hash,
            u.role,
            u.account_status,
            au.admin_role,
            au.status AS admin_status
         FROM users u
         INNER JOIN admin_users au
            ON au.user_id = u.id
         WHERE
            u.member_id = ?
            AND u.role = "admin"
         LIMIT 1'
    );

    $stmt->execute([$username]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$admin ||
        empty($admin['password_hash']) ||
        !password_verify(
            $password,
            $admin['password_hash']
        )
    ) {
        error_response(
            'Invalid username or password.',
            [],
            401
        );
    }

    if ($admin['account_status'] === 'blocked') {
        error_response(
            'Admin account is blocked.',
            [],
            403
        );
    }

    if ($admin['account_status'] !== 'active') {
        error_response(
            'Admin account is not active.',
            [],
            403
        );
    }

    if ($admin['admin_status'] !== 'active') {
        error_response(
            'Admin access is not active.',
            [],
            403
        );
    }

    $token = issue_token(
        (int)$admin['id']
    );

    $update = $pdo->prepare(
        'UPDATE users
         SET last_login_at = NOW()
         WHERE id = ?'
    );

    $update->execute([
        $admin['id']
    ]);

    success_response(
        'Admin login successful.',
        [
            'token' => $token,

            'admin' => [
                'id' => (int)$admin['id'],
                'member_id' => $admin['member_id'],
                'phone' => $admin['phone'],
                'role' => $admin['role'],
                'admin_role' => $admin['admin_role']
            ]
        ]
    );
}


function admin_me(): never
{
    $admin = current_user(true);

    /*
     * Token must belong to an admin user.
     */

    if (($admin['role'] ?? '') !== 'admin') {
        error_response(
            'Admin access required.',
            [],
            403
        );
    }

    $stmt = db()->prepare(
        'SELECT
            au.admin_role,
            au.status AS admin_status
         FROM admin_users au
         WHERE au.user_id = ?
         LIMIT 1'
    );

    $stmt->execute([
        $admin['id']
    ]);

    $adminAccess = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminAccess) {
        error_response(
            'Admin access not found.',
            [],
            403
        );
    }

    if ($admin['account_status'] !== 'active') {
        error_response(
            'Admin account is not active.',
            [],
            403
        );
    }

    if ($adminAccess['admin_status'] !== 'active') {
        error_response(
            'Admin access is not active.',
            [],
            403
        );
    }

    success_response(
        'Current admin fetched successfully.',
        [
            'admin' => [
                'id' => (int)$admin['id'],
                'member_id' => $admin['member_id'],
                'phone' => $admin['phone'],
                'role' => $admin['role'],
                'admin_role' => $adminAccess['admin_role']
            ]
        ]
    );
}


function admin_logout(): never
{
    $admin = current_user(true);

    if (($admin['role'] ?? '') !== 'admin') {
        error_response(
            'Admin access required.',
            [],
            403
        );
    }

    revoke_current_token();

    success_response(
        'Admin logged out successfully.'
    );
}