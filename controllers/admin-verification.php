<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

/*
|--------------------------------------------------------------------------
| ADMIN HOME VERIFICATION ACCESS
|--------------------------------------------------------------------------
*/
function require_admin_verification_access(): array
{
    $admin = current_user(true);

    if (($admin['role'] ?? '') !== 'admin') {
        error_response('Admin access required.', [], 403);
    }

    $stmt = db()->prepare(
        'SELECT admin_role, status AS admin_status
         FROM admin_users
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([(int)$admin['id']]);
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$access) {
        error_response('Admin access not found.', [], 403);
    }

    if (($admin['account_status'] ?? '') !== 'active') {
        error_response('Admin account is not active.', [], 403);
    }

    if (($access['admin_status'] ?? '') !== 'active') {
        error_response('Admin access is not active.', [], 403);
    }

    return [
        'admin' => $admin,
        'admin_access' => $access
    ];
}


/*
|--------------------------------------------------------------------------
| GET PAID USERS FOR HOME VERIFICATION
|--------------------------------------------------------------------------
|
| GET /admin/verification
|
| Only the user's latest successful payment is considered.
| Therefore, a user who was Basic and later moved to Free is not shown.
| Basic is the Home Verification payment in the current business model.
|--------------------------------------------------------------------------
*/
function get_admin_verification_requests(): never
{
    require_admin_verification_access();

    $pdo = db();

    $sql = '
        SELECT
            pmt.id AS payment_id,
            pmt.user_id,
            pmt.amount,
            pmt.paid_at,
            pmt.created_at AS payment_created_at,

            u.member_id,
            u.phone,

            pr.full_name,
            pr.gender,
            pr.place,
            pr.district,
            pr.state,

            vr.id AS verification_id,
            vr.status AS verification_status,
            vr.requested_at,
            vr.started_at,
            vr.completed_at,
            vr.latitude,
            vr.longitude,
            vr.location_accuracy,
            vr.location_name,
            vr.location_place,
            vr.location_district,
            vr.location_state,
            vr.verification_photo_path,
            vr.verification_notes

        FROM payments pmt

        INNER JOIN plans pl
            ON pl.id = pmt.plan_id
            AND pl.name = "Basic"

        INNER JOIN users u
            ON u.id = pmt.user_id

        INNER JOIN profiles pr
            ON pr.user_id = pmt.user_id

        LEFT JOIN verification_requests vr
            ON vr.payment_id = pmt.id
            AND vr.user_id = pmt.user_id

        WHERE
            pmt.payment_status = "success"
            AND pmt.id = (
                SELECT MAX(p2.id)
                FROM payments p2
                WHERE
                    p2.user_id = pmt.user_id
                    AND p2.payment_status = "success"
            )
            AND pr.registration_completed = 1

        ORDER BY
            COALESCE(vr.requested_at, pmt.paid_at, pmt.created_at) DESC,
            pmt.id DESC
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $requests = [];

    foreach ($rows as $row) {
        $status = match ((string)($row['verification_status'] ?? '')) {
            'in_progress' => 'In Progress',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => 'Pending'
        };

        $requests[] = [
            'id' => isset($row['verification_id'])
                ? (int)$row['verification_id']
                : null,
            'paymentId' => (int)$row['payment_id'],
            'userId' => (int)$row['user_id'],
            'memberId' => (string)$row['member_id'],
            'name' => (string)$row['full_name'],
            'gender' => (string)$row['gender'],
            'place' => (string)$row['place'],
            'district' => (string)$row['district'],
            'state' => (string)$row['state'],
            'phone' => (string)$row['phone'],
            'paymentStatus' => 'Paid',
            'paymentAmount' => (float)$row['amount'],
            'paidAt' => $row['paid_at'],
            'requestedDate' => $row['requested_at'] ?? $row['paid_at'] ?? $row['payment_created_at'],
            'status' => $status,
            'verificationStatus' => $row['verification_status'] ?? 'pending',
            'latitude' => $row['latitude'] !== null ? (float)$row['latitude'] : null,
            'longitude' => $row['longitude'] !== null ? (float)$row['longitude'] : null,
            'locationAccuracy' => $row['location_accuracy'] !== null ? (float)$row['location_accuracy'] : null,
            'locationName' => $row['location_name'],
            'locationPlace' => $row['location_place'],
            'locationDistrict' => $row['location_district'],
            'locationState' => $row['location_state'],
            'photoPath' => $row['verification_photo_path'],
            'verificationNotes' => $row['verification_notes']
        ];
    }

    success_response(
        'Admin verification requests fetched successfully.',
        [
            'requests' => $requests,
            'count' => count($requests)
        ]
    );
}


/*
|--------------------------------------------------------------------------
| START VERIFICATION
|--------------------------------------------------------------------------
|
| POST /admin/verification/start
| Body: { "verification_id": 123 }
|
| If the paid Basic user does not yet have a verification request,
| this endpoint creates one and starts it in the same operation.
|--------------------------------------------------------------------------
*/
function start_admin_verification(): never
{
    $access = require_admin_verification_access();
    $admin = $access['admin'];

    $data = request_json();
    $verificationId = (int)($data['verification_id'] ?? 0);
    $paymentId = (int)($data['payment_id'] ?? 0);

    if ($verificationId < 1) {
        error_response('Verification ID is required.', [], 422);
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();

        $verification = null;

        if ($verificationId > 0) {
            $stmt = $pdo->prepare(
                'SELECT
                    vr.id,
                    vr.user_id,
                    vr.payment_id,
                    vr.status,
                    p.payment_status,
                    pl.name AS plan_name
                 FROM verification_requests vr
                 INNER JOIN payments p
                    ON p.id = vr.payment_id
                 INNER JOIN plans pl
                    ON pl.id = p.plan_id
                 WHERE
                    vr.id = ?
                    AND pl.name = "Basic"
                    AND p.payment_status = "success"
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute([$verificationId]);
            $verification = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$verification && $paymentId > 0) {
            $stmt = $pdo->prepare(
                'SELECT
                    p.id AS payment_id,
                    p.user_id,
                    p.payment_status,
                    pl.name AS plan_name
                 FROM payments p
                 INNER JOIN plans pl
                    ON pl.id = p.plan_id
                 WHERE
                    p.id = ?
                    AND pl.name = "Basic"
                    AND p.payment_status = "success"
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($payment) {
                $stmt = $pdo->prepare(
                    'SELECT id, status
                     FROM verification_requests
                     WHERE payment_id = ? AND user_id = ?
                     ORDER BY id DESC
                     LIMIT 1
                     FOR UPDATE'
                );
                $stmt->execute([
                    (int)$payment['payment_id'],
                    (int)$payment['user_id']
                ]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $verification = [
                        'id' => (int)$existing['id'],
                        'user_id' => (int)$payment['user_id'],
                        'payment_id' => (int)$payment['payment_id'],
                        'status' => $existing['status'],
                        'payment_status' => $payment['payment_status'],
                        'plan_name' => $payment['plan_name']
                    ];
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO verification_requests (
                            user_id, payment_id, status, requested_at
                         ) VALUES (?, ?, "pending", NOW())'
                    );
                    $stmt->execute([
                        (int)$payment['user_id'],
                        (int)$payment['payment_id']
                    ]);

                    $verification = [
                        'id' => (int)$pdo->lastInsertId(),
                        'user_id' => (int)$payment['user_id'],
                        'payment_id' => (int)$payment['payment_id'],
                        'status' => 'pending',
                        'payment_status' => $payment['payment_status'],
                        'plan_name' => $payment['plan_name']
                    ];
                }
            }
        }

        if (!$verification) {
            $pdo->rollBack();
            error_response('Paid verification request not found.', [], 404);
        }

        if ((string)$verification['status'] === 'verified') {
            $pdo->rollBack();
            error_response('This verification is already completed.', [], 409);
        }

        if (in_array((string)$verification['status'], ['rejected', 'cancelled'], true)) {
            $pdo->rollBack();
            error_response('This verification request is not available.', [], 409);
        }

        $stmt = $pdo->prepare(
            'UPDATE verification_requests
             SET
                status = "in_progress",
                started_at = COALESCE(started_at, NOW())
             WHERE id = ?'
        );
        $stmt->execute([$verificationId]);

        $pdo->commit();

        success_response(
            'Verification started successfully.',
            [
                'verification_id' => $verificationId,
                'status' => 'in_progress',
                'admin_id' => (int)$admin['id']
            ]
        );

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_response(
            'Unable to start verification.',
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| COMPLETE VERIFICATION
|--------------------------------------------------------------------------
|
| POST /admin/verification/complete
|
| multipart/form-data:
| - verification_id
| - latitude
| - longitude
| - accuracy (optional)
| - location_name (optional)
| - location_place (optional)
| - location_district (optional)
| - location_state (optional)
| - verification_notes (optional)
| - house_photo (required image)
|
| On success:
| - verification_requests.status = verified
| - profiles.home_verified = 1
| - profiles.profile_status = verified
|--------------------------------------------------------------------------
*/
function complete_admin_verification(): never
{
    $access = require_admin_verification_access();
    $admin = $access['admin'];

    $verificationId = (int)($_POST['verification_id'] ?? 0);
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $accuracy = $_POST['accuracy'] ?? null;

    $locationName = trim((string)($_POST['location_name'] ?? ''));
    $locationPlace = trim((string)($_POST['location_place'] ?? ''));
    $locationDistrict = trim((string)($_POST['location_district'] ?? ''));
    $locationState = trim((string)($_POST['location_state'] ?? ''));
    $notes = trim((string)($_POST['verification_notes'] ?? ''));

    if ($verificationId < 1) {
        error_response('Verification ID is required.', [], 422);
    }

    if ($latitude === null || $latitude === '' || !is_numeric($latitude)) {
        error_response('GPS latitude is required.', [], 422);
    }

    if ($longitude === null || $longitude === '' || !is_numeric($longitude)) {
        error_response('GPS longitude is required.', [], 422);
    }

    $latitude = (float)$latitude;
    $longitude = (float)$longitude;

    if ($latitude < -90 || $latitude > 90) {
        error_response('Invalid GPS latitude.', [], 422);
    }

    if ($longitude < -180 || $longitude > 180) {
        error_response('Invalid GPS longitude.', [], 422);
    }

    if ($accuracy !== null && $accuracy !== '' && !is_numeric($accuracy)) {
        error_response('Invalid GPS accuracy.', [], 422);
    }

    $accuracyValue = ($accuracy !== null && $accuracy !== '')
        ? (float)$accuracy
        : null;

    if (!isset($_FILES['house_photo'])) {
        error_response('House photo is required.', [], 422);
    }

    $file = $_FILES['house_photo'];

    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        error_response('Unable to upload the house photo.', [], 422);
    }

    if (($file['size'] ?? 0) < 1) {
        error_response('House photo is empty.', [], 422);
    }

    // 5 MB maximum.
    if ((int)$file['size'] > 5 * 1024 * 1024) {
        error_response('House photo must be 5 MB or smaller.', [], 422);
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        error_response('Invalid uploaded house photo.', [], 422);
    }

    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false) {
        error_response('House photo must be a valid image.', [], 422);
    }

    $mime = (string)($imageInfo['mime'] ?? '');
    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedMime[$mime])) {
        error_response('Only JPG, PNG or WebP house photos are allowed.', [], 422);
    }

    $pdo = db();
    $storedAbsolutePath = null;
    $storedRelativePath = null;

    try {
        $stmt = $pdo->prepare(
            'SELECT
                vr.id,
                vr.user_id,
                vr.status,
                p.payment_status,
                pl.name AS plan_name,
                u.member_id
             FROM verification_requests vr
             INNER JOIN payments p
                ON p.id = vr.payment_id
             INNER JOIN plans pl
                ON pl.id = p.plan_id
             INNER JOIN users u
                ON u.id = vr.user_id
             WHERE
                vr.id = ?
                AND pl.name = "Basic"
                AND p.payment_status = "success"
             LIMIT 1'
        );
        $stmt->execute([$verificationId]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            error_response('Paid verification request not found.', [], 404);
        }

        if ((string)$verification['status'] === 'verified') {
            error_response('This verification is already completed.', [], 409);
        }

        if (in_array((string)$verification['status'], ['rejected', 'cancelled'], true)) {
            error_response('This verification request is not available.', [], 409);
        }

        $extension = $allowedMime[$mime];
        $directory = dirname(__DIR__) . '/uploads/home-verification';

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            error_response('Unable to create the verification upload directory.', [], 500);
        }

        $safeMemberId = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$verification['member_id']);
        $safeMemberId = $safeMemberId !== '' ? $safeMemberId : 'member';
        $filename = $safeMemberId . '_home_' . $verificationId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        $storedAbsolutePath = $directory . DIRECTORY_SEPARATOR . $filename;
        $storedRelativePath = 'uploads/home-verification/' . $filename;

        if (!move_uploaded_file($tmpName, $storedAbsolutePath)) {
            error_response('Unable to save the house photo.', [], 500);
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'UPDATE verification_requests
             SET
                status = "verified",
                started_at = COALESCE(started_at, NOW()),
                completed_at = NOW(),
                verified_by = ?,
                latitude = ?,
                longitude = ?,
                location_accuracy = ?,
                location_name = ?,
                location_place = ?,
                location_district = ?,
                location_state = ?,
                verification_notes = ?,
                verification_photo_path = ?
             WHERE id = ?'
        );

        $stmt->execute([
            (int)$admin['id'],
            $latitude,
            $longitude,
            $accuracyValue,
            $locationName !== '' ? $locationName : null,
            $locationPlace !== '' ? $locationPlace : null,
            $locationDistrict !== '' ? $locationDistrict : null,
            $locationState !== '' ? $locationState : null,
            $notes !== '' ? $notes : null,
            $storedRelativePath,
            $verificationId
        ]);

        $stmt = $pdo->prepare(
            'UPDATE profiles
             SET
                home_verified = 1,
                profile_status = "verified"
             WHERE user_id = ?'
        );
        $stmt->execute([(int)$verification['user_id']]);

        $pdo->commit();

        success_response(
            'Home verification completed successfully.',
            [
                'verification_id' => $verificationId,
                'status' => 'verified',
                'home_verified' => 1,
                'profile_status' => 'verified',
                'verified_by' => (int)$admin['id'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracyValue,
                'photo_path' => $storedRelativePath
            ]
        );

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($storedAbsolutePath && is_file($storedAbsolutePath)) {
            @unlink($storedAbsolutePath);
        }

        error_response(
            'Unable to complete home verification.',
            [],
            500
        );
    }
}
