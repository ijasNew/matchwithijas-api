<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

/*
|--------------------------------------------------------------------------
| CONTINUE VERIFICATION CLICK TRACKING
|--------------------------------------------------------------------------
|
| Records every valid click on the user's
| "Continue Verification" button.
|
| The authenticated user is used for both:
|   profile_user_id
|   clicked_by_user_id
|
| The user ID is NEVER accepted from the frontend.
|
*/

function log_verification_continue_click()
{
    $user = current_user(true);

    $userId = (int)$user['id'];

    if ($userId <= 0) {
        error_response(
            'Invalid user.',
            [],
            401
        );
    }

    $stmt = db()->prepare(
        'INSERT INTO verification_continue_clicks
            (
                profile_user_id,
                clicked_by_user_id,
                clicked_at
            )
         VALUES
            (?, ?, NOW())'
    );

    $stmt->execute([
        $userId,
        $userId
    ]);

    success_response(
        'Verification click recorded successfully.',
        [
            'profile_user_id' => $userId,
            'clicked_by_user_id' => $userId,
            'clicked_at' => date('Y-m-d H:i:s')
        ]
    );
}