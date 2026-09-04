<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';

function reset_password(): never
{
    $data = request_json();

    $phone = preg_replace(
        '/\D+/',
        '',
        (string)($data['phone'] ?? '')
    ) ?? '';

    $otp = trim(
        (string)($data['otp'] ?? '')
    );

    $password = (string)($data['password'] ?? '');

    // =========================
    // VALIDATION
    // =========================

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        error_response(
            'Please enter a valid 10 digit mobile number.',
            ['phone' => 'Invalid phone number.'],
            422
        );
    }

    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        error_response(
            'Please enter the 6 digit OTP.',
            ['otp' => 'Invalid OTP.'],
            422
        );
    }

    if (strlen($password) < 8) {
        error_response(
            'Password must be at least 8 characters.',
            ['password' => 'Password must be at least 8 characters.'],
            422
        );
    }

    if (!preg_match('/[A-Z]/', $password)) {
        error_response(
            'Password must contain at least one uppercase letter.',
            [],
            422
        );
    }

    if (!preg_match('/[a-z]/', $password)) {
        error_response(
            'Password must contain at least one lowercase letter.',
            [],
            422
        );
    }

    if (!preg_match('/[0-9]/', $password)) {
        error_response(
            'Password must contain at least one number.',
            [],
            422
        );
    }

    $pdo = db();

    // =========================
    // CHECK USER
    // =========================

    $stmt = $pdo->prepare(
        'SELECT id
         FROM users
         WHERE phone = ?
         LIMIT 1'
    );

    $stmt->execute([$phone]);

    $user = $stmt->fetch();

    if (!$user) {
        error_response(
            'No account found for this mobile number.',
            [],
            404
        );
    }

    // =========================
    // FIND VERIFIED OTP
    // =========================

    $stmt = $pdo->prepare(
        'SELECT id, otp_hash, expires_at
         FROM otp_verifications
         WHERE phone = ?
           AND purpose = ?
           AND verified_at IS NOT NULL
         ORDER BY id DESC
         LIMIT 1'
    );

    $stmt->execute([
        $phone,
        'forgot_password'
    ]);

    $otpRecord = $stmt->fetch();

    if (!$otpRecord) {
        error_response(
            'OTP verification required.',
            [],
            400
        );
    }

    // =========================
    // OTP EXPIRY CHECK
    // =========================

    if (
        empty($otpRecord['expires_at']) ||
        strtotime($otpRecord['expires_at']) < time()
    ) {
        error_response(
            'OTP has expired. Please request a new OTP.',
            [],
            400
        );
    }

    // =========================
    // VERIFY OTP
    // =========================

    if (
        !password_verify(
            $otp,
            $otpRecord['otp_hash']
        )
    ) {
        error_response(
            'Invalid OTP.',
            [],
            400
        );
    }

    // =========================
    // UPDATE PASSWORD
    // =========================

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $update = $pdo->prepare(
        'UPDATE users
         SET password_hash = ?
         WHERE id = ?'
    );

    $update->execute([
        $passwordHash,
        $user['id']
    ]);

    // =========================
    // CONSUME OTP
    // =========================

    $pdo->prepare(
        'UPDATE otp_verifications
         SET verified_at = NOW()
         WHERE id = ?'
    )->execute([
        $otpRecord['id']
    ]);

    success_response(
        'Password reset successfully.',
        []
    );
}