<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


/*
|--------------------------------------------------------------------------
| GET CURRENT USER PROFILE DETAILS
|--------------------------------------------------------------------------
|
| GET /profile-details
|
| Authentication:
| Bearer token required
|
| The user ID is taken from the authenticated token.
| Frontend does NOT send user_id.
|
*/


function get_profile_details(): never
{
    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED USER
    |--------------------------------------------------------------------------
    */

    $user = current_user(true);

    $userId = (int) $user['id'];

    $pdo = db();


    /*
    |--------------------------------------------------------------------------
    | USER ACCOUNT DATA
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Do NOT return password_hash.
    |
    */

    $stmt = $pdo->prepare(
        'SELECT
            id,
            member_id,
            phone,
            role,
            account_status,
            otp_verified,
            created_at,
            updated_at,
            last_login_at
         FROM users
         WHERE id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$userData) {

        error_response(
            'User account not found.',
            [],
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROFILE DATA
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'SELECT *
         FROM profiles
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$profileData) {

        error_response(
            'Profile details not found.',
            [],
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROFILE PREFERENCES
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'SELECT *
         FROM profile_preferences
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $profilePreferences =
        $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | PREFERENCE VALUES
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'SELECT *
         FROM preference_values
         WHERE user_id = ?
         ORDER BY id ASC'
    );

    $stmt->execute([$userId]);

    $preferenceValues =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    success_response(
        'Profile details fetched successfully.',
        [

            'user' => $userData ?: [],

            'profile' => $profileData ?: [],

            'preferences' =>
                $profilePreferences ?: [],

            'preference_values' =>
                $preferenceValues

        ]
    );
}