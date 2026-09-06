<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


/*
|--------------------------------------------------------------------------
| GET CURRENT USER VERIFICATION STATUS
|--------------------------------------------------------------------------
|
| GET /verification/status
|
| Returns the latest home-verification payment and
| verification request status for the logged-in user.
|
*/

function get_verification_status(): never
{
    /*
     * ---------------------------------------------------------------
     * AUTHENTICATED USER
     * ---------------------------------------------------------------
     */

    $user = current_user(true);

    $userId = (int)$user['id'];

    $pdo = db();


    /*
     * ---------------------------------------------------------------
     * GET PROFILE
     * ---------------------------------------------------------------
     */

    $stmt = $pdo->prepare(
        'SELECT
            id,
            district,
            state,
            registration_completed,
            profile_status,
            home_verified,
            created_at
         FROM profiles
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([
        $userId
    ]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$profile) {

        error_response(
            'Profile not found.',
            [],
            404
        );
    }


    /*
     * ---------------------------------------------------------------
     * GET CURRENT BASIC / HOME VERIFICATION PAYMENT
     * ---------------------------------------------------------------
     *
     * Basic plan is the Home Verification payment.
     *
     * We find the latest Basic payment and then
     * its linked verification request.
     */

    $stmt = $pdo->prepare(
        'SELECT
            p.id AS payment_id,
            p.amount,
            p.payment_status,
            p.payment_method,
            p.transaction_id,
            p.paid_at,
            p.created_at AS payment_created_at,

            pl.name AS plan_name,

            vr.id AS verification_id,
            vr.status AS verification_status,
            vr.requested_at,
            vr.started_at,
            vr.completed_at

         FROM payments p

         INNER JOIN plans pl
            ON pl.id = p.plan_id

         LEFT JOIN verification_requests vr
            ON vr.payment_id = p.id
            AND vr.user_id = p.user_id

         WHERE
            p.user_id = ?
            AND pl.name = "Basic"

         ORDER BY
            p.id DESC

         LIMIT 1'
    );

    $stmt->execute([
        $userId
    ]);

    $verification = $stmt->fetch(PDO::FETCH_ASSOC);


    /*
     * ---------------------------------------------------------------
     * DEFAULT STATE
     * ---------------------------------------------------------------
     */

    $paymentStatus = 'unpaid';

    $verificationStatus = 'not_requested';

    $paymentId = null;

    $verificationId = null;

    $amount = null;

    $paidAt = null;

    $requestedAt = null;

    $startedAt = null;

    $completedAt = null;


    /*
     * ---------------------------------------------------------------
     * MAP DATABASE VALUES
     * ---------------------------------------------------------------
     */

    if ($verification) {

        $verificationId =
            isset($verification['verification_id'])
                ? (int)$verification['verification_id']
                : null;


        $paymentId =
            isset($verification['payment_id'])
                ? (int)$verification['payment_id']
                : null;


        $amount =
            isset($verification['amount'])
                ? (float)$verification['amount']
                : null;


        $paidAt =
            $verification['paid_at'] ?? null;


        $requestedAt =
            $verification['requested_at'] ?? null;


        $startedAt =
            $verification['started_at'] ?? null;


        $completedAt =
            $verification['completed_at'] ?? null;


        /*
         * Payment status
         */

        $paymentStatus =
            match (
                $verification['payment_status'] ?? null
            ) {

                'success' =>
                    'paid',

                'pending' =>
                    'pending',

                'failed' =>
                    'failed',

                'refunded' =>
                    'refunded',

                default =>
                    'unpaid'
            };


        /*
         * Verification status
         */

        $verificationStatus =
            match (
                $verification['verification_status'] ?? null
            ) {

                'pending' =>
                    'pending',

                'in_progress' =>
                    'in_progress',

                'verified' =>
                    'verified',

                'rejected' =>
                    'rejected',

                'cancelled' =>
                    'cancelled',

                default =>
                    'not_requested'
            };
    }


    /*
     * ---------------------------------------------------------------
     * IMPORTANT BUSINESS STATE
     * ---------------------------------------------------------------
     *
     * payment_status = paid
     * verification_status = pending
     *
     * In this state the user must NOT see the payment form again.
     */

    $paymentReceivedUnderReview =
        (
            $paymentStatus === 'paid'
            &&
            $verificationStatus === 'pending'
        );


    /*
     * ---------------------------------------------------------------
     * VERIFICATION COMPLETED
     * ---------------------------------------------------------------
     *
     * If already verified, do not show payment form.
     */

    $verificationCompleted =
        (
            $verificationStatus === 'verified'
            ||
            (int)$profile['home_verified'] === 1
        );


    /*
     * ---------------------------------------------------------------
     * RESPONSE
     * ---------------------------------------------------------------
     */

    success_response(
        'Verification status fetched successfully.',
        [

            'payment_status' =>
                $paymentStatus,

            'verification_status' =>
                $verificationStatus,

            'payment_received_under_review' =>
                $paymentReceivedUnderReview,

            'verification_completed' =>
                $verificationCompleted,

            'payment' => [
                'id' => $paymentId,
                'amount' => $amount,
                'paid_at' => $paidAt
            ],

            'verification' => [
                'id' => $verificationId,
                'requested_at' => $requestedAt,
                'started_at' => $startedAt,
                'completed_at' => $completedAt
            ],

            'profile' => [
                'district' =>
                    $profile['district'],

                'state' =>
                    $profile['state'],

                'registration_completed' =>
                    (int)$profile['registration_completed'],

                'profile_status' =>
                    $profile['profile_status'],

                'home_verified' =>
                    (int)$profile['home_verified'],

                /*
                 * Registration/profile creation time
                 * Used by frontend for 3-day offer calculation.
                 */

                'registered_at' =>
                    $profile['created_at']
            ]
        ]
    );
}