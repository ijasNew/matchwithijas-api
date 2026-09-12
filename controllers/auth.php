<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/msg91.php';

function normalize_phone(mixed $phone): string
{
    return preg_replace('/\D+/', '', (string)$phone) ?? '';
}

function validate_password(string $password): ?string
{
    if (strlen($password) < 8) return 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $password)) return 'Password must contain at least one uppercase letter.';
    if (!preg_match('/[a-z]/', $password)) return 'Password must contain at least one lowercase letter.';
    if (!preg_match('/[0-9]/', $password)) return 'Password must contain at least one number.';
    return null;
}

function generate_member_id(PDO $pdo, int $id): string
{
    return 'MWI' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
}

function register_user(): never
{
    $data = request_json();
    $phone = normalize_phone($data['phone'] ?? '');
    $password = (string)($data['password'] ?? '');
    $accessToken = trim((string)($data['msg91_access_token'] ?? ''));

    $errors = [];
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors['phone'] = 'Invalid phone number.';
    }

    if ($accessToken === '') {
        $errors['msg91_access_token'] = 'Mobile OTP verification is required.';
    }

    if (($passwordError = validate_password($password)) !== null) {
        $errors['password'] = $passwordError;
    }

    if ($errors) {
        error_response('Validation failed.', $errors, 422);
    }

    $pdo = db();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ? LIMIT 1');
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        error_response(
            'This mobile number is already registered. Please login to continue.',
            [],
            409
        );
    }

    try {
        $msg91Response = verify_msg91_access_token($accessToken);
        assert_msg91_token_matches_phone($msg91Response, $accessToken, $phone);
    } catch (Throwable $e) {
        error_response(
            $e->getMessage() ?: 'Mobile OTP verification failed.',
            [],
            401
        );
    }

    $pdo->beginTransaction();
    try {
        $tempMember = 'TEMP-' . bin2hex(random_bytes(6));
        $stmt = $pdo->prepare(
            "INSERT INTO users (member_id, phone, password_hash, role, account_status, otp_verified)\n             VALUES (?, ?, ?, 'user', 'pending', 1)"
        );
        $stmt->execute([
            $tempMember,
            $phone,
            password_hash($password, PASSWORD_DEFAULT)
        ]);

        $userId = (int)$pdo->lastInsertId();
        $memberId = generate_member_id($pdo, $userId);

        $pdo->prepare(
            'UPDATE users SET member_id = ? WHERE id = ?'
        )->execute([$memberId, $userId]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_response('Unable to create the account.', [], 500);
    }

    $token = issue_token($userId);

    success_response(
        'Account created successfully.',
        [
            'token' => $token,
            'user' => [
                'id' => $userId,
                'member_id' => $memberId,
                'phone' => $phone,
                'role' => 'user',
                'account_status' => 'pending'
            ]
        ],
        201
    );
}

function login_user(): never
{
    $data = request_json();
    $phone = normalize_phone($data['phone'] ?? '');
    $password = (string)($data['password'] ?? '');

    if (!preg_match('/^[0-9]{10}$/', $phone)) error_response('Invalid phone number.', ['phone' => 'Please enter a valid 10 digit mobile number.'], 422);
    if ($password === '') error_response('Password is required.', ['password' => 'Password is required.'], 422);

    $stmt = db()->prepare('SELECT * FROM users WHERE phone = ? LIMIT 1');
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user || !$user['password_hash'] || !password_verify($password, $user['password_hash'])) {
        error_response('Invalid phone number or password.', [], 401);
    }
    if ($user['account_status'] === 'blocked') error_response('Your account is blocked. Please contact support.', [], 403);
    
    if (!in_array($user['account_status'], ['active', 'pending'], true)) {
    error_response('Your account is not available for login.', [], 403);
}
    $token = issue_token((int)$user['id']);
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

    success_response('Login successful.', [
        'token' => $token,
        'user' => [
            'id' => (int)$user['id'],
            'member_id' => $user['member_id'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'account_status' => $user['account_status']
        ]
    ]);
}

function logout_user(): never
{
    current_user(true);
    revoke_current_token();
    success_response('Logged out successfully.');
}

function change_password(): never
{
    $user = current_user(true);

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    $currentPassword =
        trim((string)($input['current_password'] ?? ''));

    $newPassword =
        (string)($input['new_password'] ?? '');

    if ($currentPassword === '') {
        error_response(
            'Current password is required.',
            [],
            422
        );
    }

    if ($newPassword === '') {
        error_response(
            'New password is required.',
            [],
            422
        );
    }

    if (strlen($newPassword) < 8) {
        error_response(
            'New password must be at least 8 characters.',
            [],
            422
        );
    }

    if (!preg_match('/[A-Z]/', $newPassword)) {
        error_response(
            'Password must contain at least one uppercase letter.',
            [],
            422
        );
    }

    if (!preg_match('/[a-z]/', $newPassword)) {
        error_response(
            'Password must contain at least one lowercase letter.',
            [],
            422
        );
    }

    if (!preg_match('/[0-9]/', $newPassword)) {
        error_response(
            'Password must contain at least one number.',
            [],
            422
        );
    }

    // Verify current password
    if (
        empty($user['password_hash']) ||
        !password_verify(
            $currentPassword,
            $user['password_hash']
        )
    ) {
        error_response(
            'Current password is incorrect.',
            [],
            401
        );
    }

    // Prevent same password
    if (
        password_verify(
            $newPassword,
            $user['password_hash']
        )
    ) {
        error_response(
            'New password must be different from current password.',
            [],
            422
        );
    }

    // Hash new password
    $passwordHash =
        password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

    // Update DB
    $stmt = db()->prepare(
        'UPDATE users
         SET password_hash = ?
         WHERE id = ?'
    );

    $stmt->execute([
        $passwordHash,
        $user['id']
    ]);

        // Revoke all existing sessions after password change
        $pdo = db();

        $pdo->prepare(
            'UPDATE auth_sessions
            SET revoked_at = NOW()
            WHERE user_id = ?
            AND revoked_at IS NULL'
        )->execute([
            $user['id']
        ]);
    success_response(
        'Password changed successfully.'
    );
}
  

function me(): never
{
    $user = current_user(true);

    $stmt = db()->prepare(
        'SELECT
            registration_completed,
            completion_percentage
         FROM profiles
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([
        $user['id']
    ]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);


    $registrationCompleted =
        (bool)($profile['registration_completed'] ?? false);

    $completionPercentage =
        (int)($profile['completion_percentage'] ?? 0);


    success_response(
        'Current user fetched successfully.',
        [
            'id' =>
                (int)$user['id'],

            'member_id' =>
                $user['member_id'],

            'phone' =>
                $user['phone'],

            'role' =>
                $user['role'],

            'account_status' =>
                $user['account_status'],

            'otp_verified' =>
                (bool)$user['otp_verified'],

            'registration_completed' =>
                $registrationCompleted,

            'completion_percentage' =>
                $completionPercentage
        ]
    );
}