<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS CHECK
|--------------------------------------------------------------------------
*/

function require_admin_plan_access(): array
{
    $admin = current_user(true);

    if (($admin['role'] ?? '') !== 'admin') {
        error_response(
            'Admin access required.',
            [],
            403
        );
    }

    $pdo = db();

    $stmt = $pdo->prepare(
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

    if (($admin['account_status'] ?? '') !== 'active') {
        error_response(
            'Admin account is not active.',
            [],
            403
        );
    }

    if (($adminAccess['admin_status'] ?? '') !== 'active') {
        error_response(
            'Admin access is not active.',
            [],
            403
        );
    }

    return [
        'admin' => $admin,
        'admin_access' => $adminAccess
    ];
}


/*
|--------------------------------------------------------------------------
| GET ADMIN PLAN USERS
|--------------------------------------------------------------------------
|
| GET /admin/plans
|
| Returns:
| - Registered users
| - Current plan
| - Latest payment status
|
*/

function get_admin_plan_users(): never
{
    require_admin_plan_access();

    $pdo = db();

    /*
     * Latest payment for each user
     */
    $latestPaymentSubquery = '
        SELECT
            p1.user_id,
            p1.id,
            p1.plan_id,
            p1.amount,
            p1.payment_method,
            p1.payment_status,
            p1.paid_at,
            p1.created_at
        FROM payments p1
        INNER JOIN (
            SELECT
                user_id,
                MAX(id) AS max_id
            FROM payments
            GROUP BY user_id
        ) latest
            ON latest.user_id = p1.user_id
            AND latest.max_id = p1.id
    ';

    /*
     * Latest successful payment for each user.
     * This determines the CURRENT PLAN.
     */
    $latestSuccessSubquery = '
        SELECT
            p2.user_id,
            p2.id,
            p2.plan_id
        FROM payments p2
        INNER JOIN (
            SELECT
                user_id,
                MAX(id) AS max_id
            FROM payments
            WHERE payment_status = "success"
            GROUP BY user_id
        ) latest_success
            ON latest_success.user_id = p2.user_id
            AND latest_success.max_id = p2.id
    ';

    $sql = '
        SELECT
            u.id AS user_id,
            u.member_id,
            u.phone,

            p.full_name,
            p.gender,
            p.place,
            p.profile_status,
            p.created_at AS registered_at,

            COALESCE(current_plan.name, "Free") AS current_plan,

            latest_payment.payment_status AS latest_payment_status,
            latest_payment.payment_method AS latest_payment_method,
            latest_payment.amount AS latest_payment_amount,
            latest_payment.paid_at AS latest_paid_at,
            latest_payment.created_at AS latest_payment_created_at

        FROM profiles p

        INNER JOIN users u
            ON u.id = p.user_id

        LEFT JOIN (
            ' . $latestSuccessSubquery . '
        ) latest_success
            ON latest_success.user_id = u.id

        LEFT JOIN plans current_plan
            ON current_plan.id = latest_success.plan_id

        LEFT JOIN (
            ' . $latestPaymentSubquery . '
        ) latest_payment
            ON latest_payment.user_id = u.id

        WHERE
            p.registration_completed = 1

        ORDER BY
            p.created_at DESC,
            p.id DESC
    ';

    $stmt = $pdo->prepare($sql);

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users = [];

    foreach ($rows as $row) {

        $profileStatus = (string)($row['profile_status'] ?? '');

        $status = match ($profileStatus) {
            'pending_verification' => 'Pending',
            'draft' => 'Pending',
            'rejected' => 'Pending',
            'blocked' => 'Pending',
            default => 'Active'
        };

        $paymentStatus = $row['latest_payment_status'];

        /*
         * Free users do not have a real payment.
         * We display "Not Required" instead of "success"
         * for the zero-value admin/free assignment.
         */
        if (
            ($row['current_plan'] ?? 'Free') === 'Free'
            && (
                $paymentStatus === null
                || (
                    $paymentStatus === 'success'
                    && (float)($row['latest_payment_amount'] ?? 0) === 0.0
                )
            )
        ) {
            $paymentStatusLabel = 'Not Required';
        } else {
            $paymentStatusLabel = match ($paymentStatus) {
                'pending' => 'Pending',
                'success' => 'Success',
                'failed' => 'Failed',
                'refunded' => 'Refunded',
                default => 'Not Required'
            };
        }

        $users[] = [
            'id' => $row['member_id'],
            'user_id' => (int)$row['user_id'],
            'name' => $row['full_name'],
            'gender' => $row['gender'],
            'place' => $row['place'],
            'phone' => $row['phone'],
            'registeredDate' => $row['registered_at'],
            'plan' => $row['current_plan'] ?? 'Free',
            'status' => $status,
            'paymentStatus' => $paymentStatusLabel
        ];
    }

    success_response(
        'Admin plan users fetched successfully.',
        [
            'users' => $users,
            'count' => count($users)
        ]
    );
}


/*
|--------------------------------------------------------------------------
| CHANGE USER PLAN
|--------------------------------------------------------------------------
|
| POST /admin/plans/change
|
| Body:
|
| {
|   "member_id": "MWI000020",
|   "plan": "Basic",
|   "payment_status": "success"
| }
|
*/

function change_admin_user_plan(): never
{
    require_admin_plan_access();

    $data = request_json();

    $memberId = trim(
        (string)($data['member_id'] ?? '')
    );

    $newPlan = trim(
        (string)($data['plan'] ?? '')
    );

    $paymentStatus = trim(
        (string)($data['payment_status'] ?? '')
    );

    /*
     * ---------------------------------------------------------
     * VALIDATION
     * ---------------------------------------------------------
     */

    if ($memberId === '') {
        error_response(
            'Member ID is required.',
            [],
            422
        );
    }

    if (!in_array(
        $newPlan,
        ['Free', 'Basic'],
        true
    )) {
        error_response(
            'Invalid plan selected.',
            [],
            422
        );
    }

    if (!in_array(
        $paymentStatus,
        [
            'pending',
            'success',
            'failed',
            'refunded'
        ],
        true
    )) {
        error_response(
            'Invalid payment status.',
            [],
            422
        );
    }

    /*
     * Free plan does not require a payment.
     *
     * Internally we store a zero-value successful admin
     * assignment so the latest successful plan becomes Free.
     */
    if ($newPlan === 'Free') {
        $paymentStatus = 'success';
    }


    $pdo = db();

    try {

        /*
         * -----------------------------------------------------
         * START TRANSACTION
         * -----------------------------------------------------
         */

        $pdo->beginTransaction();


        /*
         * -----------------------------------------------------
         * FIND REGISTERED USER
         * -----------------------------------------------------
         */

        $stmt = $pdo->prepare(
            'SELECT
                u.id,
                u.member_id,
                p.registration_completed
             FROM users u
             INNER JOIN profiles p
                ON p.user_id = u.id
             WHERE
                u.member_id = ?
                AND p.registration_completed = 1
             LIMIT 1
             FOR UPDATE'
        );

        $stmt->execute([
            $memberId
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {

            $pdo->rollBack();

            error_response(
                'Registered user not found.',
                [],
                404
            );
        }


        /*
         * -----------------------------------------------------
         * FIND PLAN
         * -----------------------------------------------------
         */

        $stmt = $pdo->prepare(
            'SELECT
                id,
                name,
                price,
                duration_days
             FROM plans
             WHERE
                name = ?
                AND is_active = 1
             ORDER BY id DESC
             LIMIT 1'
        );

        $stmt->execute([
            $newPlan
        ]);

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {

            $pdo->rollBack();

            error_response(
                $newPlan . ' plan is not configured in database.',
                [],
                422
            );
        }


        /*
         * -----------------------------------------------------
         * PAYMENT VALUES
         * -----------------------------------------------------
         */

        $amount = (float)$plan['price'];

        $planId = (int)$plan['id'];

        $paidAt = null;

        if ($paymentStatus === 'success') {
            $paidAt = date('Y-m-d H:i:s');
        }


        /*
         * -----------------------------------------------------
         * CREATE PAYMENT / PLAN CHANGE RECORD
         * -----------------------------------------------------
         *
         * We INSERT a new payment record instead of
         * overwriting old payment history.
         */

        $stmt = $pdo->prepare(
            'INSERT INTO payments (
                user_id,
                plan_id,
                amount,
                payment_method,
                transaction_id,
                payment_status,
                paid_at
            )
            VALUES (
                ?,
                ?,
                ?,
                "admin",
                NULL,
                ?,
                ?
            )'
        );

        $stmt->execute([
            (int)$user['id'],
            $planId,
            $amount,
            $paymentStatus,
            $paidAt
        ]);

        $paymentId = (int)$pdo->lastInsertId();
    /*
 * -----------------------------------------------------
 * BASIC PLAN = HOME VERIFICATION
 * -----------------------------------------------------
 *
 * Basic plan is the Home Verification payment.
 *
 * When payment is successful:
 * 1. Payment record is created.
 * 2. A pending verification request is created
 *    and linked to that payment.
 */

$verificationId = null;

if (
    $newPlan === 'Basic'
    && $paymentStatus === 'success'
) {
    /*
     * Prevent duplicate active verification requests
     * for the same user.
     */
    $stmt = $pdo->prepare(
        'SELECT id
         FROM verification_requests
         WHERE
             user_id = ?
             AND status IN ("pending", "in_progress")
         ORDER BY id DESC
         LIMIT 1
         FOR UPDATE'
    );

    $stmt->execute([
        (int)$user['id']
    ]);

    $existingVerification = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingVerification) {
        $verificationId =
            (int)$existingVerification['id'];

        /*
         * Make sure the existing request points
         * to the latest Basic payment.
         */
        $stmt = $pdo->prepare(
            'UPDATE verification_requests
             SET payment_id = ?,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );

        $stmt->execute([
            $paymentId,
            $verificationId
        ]);

    } else {

        /*
         * Create a new Home Verification request.
         */
        $stmt = $pdo->prepare(
            'INSERT INTO verification_requests (
                user_id,
                payment_id,
                status,
                requested_at
             )
             VALUES (
                ?,
                ?,
                "pending",
                CURRENT_TIMESTAMP
             )'
        );

        $stmt->execute([
            (int)$user['id'],
            $paymentId
        ]);

        $verificationId =
            (int)$pdo->lastInsertId();
    }

    /*
     * Profile is now waiting for Home Verification.
     */
    $stmt = $pdo->prepare(
        'UPDATE profiles
         SET
             profile_status = "pending_verification",
             home_verified = 0,
             updated_at = CURRENT_TIMESTAMP
         WHERE user_id = ?'
    );

    $stmt->execute([
        (int)$user['id']
    ]);
}

        /*
         * -----------------------------------------------------
         * COMMIT
         * -----------------------------------------------------
         */

        $pdo->commit();


        /*
         * -----------------------------------------------------
         * RESPONSE
         * -----------------------------------------------------
         */

        success_response(
            'User plan updated successfully.',
            [
                'member_id' => $memberId,
                'plan' => $newPlan,
                'payment_status' => $paymentStatus,
                'payment_id' => $paymentId
            ]
        );

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_response(
            'Unable to update user plan.',
            [],
            500
        );
    }
}