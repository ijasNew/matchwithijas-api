<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

/*
|--------------------------------------------------------------------------
| CORS
|--------------------------------------------------------------------------
*/

$allowedOrigin = FRONTEND_ORIGIN;

if (
    isset($_SERVER['HTTP_ORIGIN']) &&
    $_SERVER['HTTP_ORIGIN'] === $allowedOrigin
) {
    header("Access-Control-Allow-Origin: {$allowedOrigin}");
    header('Vary: Origin');
}

header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/controllers/auth.php';
require_once __DIR__ . '/controllers/admin-auth.php';
require_once __DIR__ . '/controllers/profile.php';
require_once __DIR__ . '/controllers/profile-details.php';
require_once __DIR__ . '/controllers/forgot-password.php';
require_once __DIR__ . '/controllers/feedback.php';
require_once __DIR__ . '/controllers/matching-profiles.php';
require_once __DIR__ . '/controllers/admin-profiles.php';
require_once __DIR__ . '/controllers/admin-profile-delete.php';
require_once __DIR__ . '/controllers/admin-feedback.php';
require_once __DIR__ . '/controllers/admin-find-match.php';
require_once __DIR__ . '/controllers/admin-dashboard.php';
require_once __DIR__ . '/controllers/admin-plans.php';
require_once __DIR__ . '/controllers/verification-status.php';
require_once __DIR__ . '/controllers/admin-verification.php';
require_once __DIR__ . '/controllers/profile-photos.php';
require_once __DIR__ . '/controllers/profile-completion.php';
require_once __DIR__ . '/controllers/interests.php';
require_once __DIR__ . '/controllers/shortlist.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);

$path = trim(
    parse_url(
        $_SERVER['REQUEST_URI'] ?? '/',
        PHP_URL_PATH
    ),
    '/'
);

$base = trim(
    dirname($_SERVER['SCRIPT_NAME'] ?? ''),
    '/'
);

if (
    $base !== '' &&
    str_starts_with($path, $base)
) {
    $path = trim(
        substr($path, strlen($base)),
        '/'
    );
}

$segments =
    $path === ''
        ? []
        : explode('/', $path);

try {


/*
|--------------------------------------------------------------------------
| ADMIN AUTH ROUTES
|--------------------------------------------------------------------------
*/
if (($segments[0] ?? '') === 'admin') {

    $action = $segments[1] ?? '';
    $subAction = $segments[2] ?? '';

    if (
        $method === 'POST' &&
        $action === 'login'
    ) {
        admin_login();
    }

    if (
        $method === 'GET' &&
        $action === 'me'
    ) {
        admin_me();
    }

    if (
        $method === 'POST' &&
        $action === 'logout'
    ) {
        admin_logout();
    }

    if (
        $method === 'GET' &&
        $action === 'dashboard'
    ) {
        get_admin_dashboard();
    }

    if (
        $method === 'GET' &&
        $action === 'profiles' &&
        !isset($segments[2])
    ) {
        get_admin_profiles();
    }

    if (
        $method === 'GET' &&
        $action === 'profiles' &&
        isset($segments[2]) &&
        $segments[2] !== ''
    ) {
        get_admin_profile($segments[2]);
    }

    if (
        $method === 'PUT' &&
        $action === 'profiles' &&
        isset($segments[2]) &&
        $segments[2] !== ''
    ) {
        update_admin_profile($segments[2]);
    }

    if (
        $method === 'POST' &&
        $action === 'profiles' &&
        $subAction === 'delete'
    ) {
        delete_admin_profile();
    }

    if (
        $method === 'GET' &&
        $action === 'plans'
    ) {
        get_admin_plan_users();
    }

    if (
        $method === 'POST' &&
        $action === 'plans' &&
        $subAction === 'change'
    ) {
        change_admin_user_plan();
    }

    if (
        $method === 'GET' &&
        $action === 'verification'
    ) {
        get_admin_verification_requests();
    }

    if (
        $method === 'POST' &&
        $action === 'verification' &&
        $subAction === 'start'
    ) {
        start_admin_verification();
    }

    if (
        $method === 'POST' &&
        $action === 'verification' &&
        $subAction === 'complete'
    ) {
        complete_admin_verification();
    }
    if (
        $method === 'GET' &&
        $action === 'feedback' &&
        !isset($segments[2])
    ) {
        get_admin_feedback();
    }

    if (
        $method === 'GET' &&
        $action === 'find-match' &&
        isset($segments[2]) &&
        $segments[2] !== ''
    ) {
        get_admin_find_match($segments[2]);
    }

    if (
        $method === 'POST' &&
        $action === 'find-match' &&
        $subAction === 'customize'
    ) {
        post_admin_find_match_customize();
    }
    error_response(
        'Endpoint not found.',
        [],
        404
    );
}

/*
|--------------------------------------------------------------------------
| VERIFICATION STATUS ROUTE
|--------------------------------------------------------------------------
*/

if (
    ($segments[0] ?? '') === 'verification' &&
    $method === 'GET' &&
    ($segments[1] ?? '') === 'status'
) {
    get_verification_status();
}
    /*
    |--------------------------------------------------------------------------
    | AUTH ROUTES
    |--------------------------------------------------------------------------
    */

    if (($segments[0] ?? '') === 'auth') {

        $action = $segments[1] ?? '';

        if (
            $method === 'POST' &&
            $action === 'register'
        ) {
            register_user();
        }

        if (
            $method === 'POST' &&
            $action === 'login'
        ) {
            login_user();
        }

        if (
            $method === 'POST' &&
            $action === 'logout'
        ) {
            logout_user();
        }

        if (
            $method === 'POST' &&
            $action === 'send-otp'
        ) {
            send_otp();
        }

        if (
            $method === 'POST' &&
            $action === 'verify-otp'
        ) {
            verify_otp();
        }
        if (
            $method === 'POST' &&
            $action === 'reset-password'
        ) {
            reset_password();
            }
            if (
    $method === 'POST' &&
    $action === 'change-password'
) {
    change_password();
}
            if (
            $method === 'GET' &&
            $action === 'me'
        ) {
            me();
        }

       

        error_response(
            'Endpoint not found.',
            [],
            404
        );
    }

/*
|--------------------------------------------------------------------------
| INTEREST ROUTES
|--------------------------------------------------------------------------
*/
if (($segments[0] ?? '') === 'interests') {
    $action = $segments[1] ?? '';
    if ($method === 'POST' && $action === 'send') send_interest();
    if ($method === 'GET' && $action === 'received') get_received_interests();
    if ($method === 'GET' && $action === 'sent') get_sent_interests();
    if ($method === 'POST' && $action === 'respond') respond_interest();
    if ($method === 'POST' && $action === 'cancel') cancel_interest();
    error_response('Endpoint not found.', [], 404);
}

/*
|--------------------------------------------------------------------------
| SHORTLIST ROUTES
|--------------------------------------------------------------------------
*/
if (($segments[0] ?? '') === 'shortlist') {
    $action = $segments[1] ?? '';
    if ($method === 'POST' && $action === 'add') add_shortlist();
    if ($method === 'POST' && $action === 'remove') remove_shortlist();
    if ($method === 'GET' && $action === '') get_shortlist();
    error_response('Endpoint not found.', [], 404);
}

/*
|--------------------------------------------------------------------------
| FEEDBACK ROUTES
|--------------------------------------------------------------------------
*/

if (($segments[0] ?? '') === 'feedback') {

    $action = $segments[1] ?? '';

    if (
        $method === 'POST' &&
        $action === 'submit'
    ) {
        submit_feedback();
    }

    error_response(
        'Endpoint not found.',
        [],
        404
    );
}



    /*
|--------------------------------------------------------------------------
| PROFILE DETAILS ROUTE
|--------------------------------------------------------------------------
*/

if (
    ($segments[0] ?? '') === 'profile-details' &&
    $method === 'GET'
) {
    get_profile_details();
}

    /*
    |--------------------------------------------------------------------------
    | PROFILE / REGISTRATION ROUTES
    |--------------------------------------------------------------------------
    */

    if (($segments[0] ?? '') === 'profile') {

        $action = $segments[1] ?? '';
        // GET /profile
        if (
            $method === 'GET' &&
            $action === ''
        ) {
            get_profile();
            // delte /profile() after work finish
        }

         
        /*
        |--------------------------------------------------------------------------
        | Basic Details
        |--------------------------------------------------------------------------
        */

        if (
            $method === 'POST' &&
            $action === 'basic'
        ) {
            save_basic();
        }


        /*
        |--------------------------------------------------------------------------
        | Location
        |--------------------------------------------------------------------------
        */

        if (
            $method === 'POST' &&
            $action === 'location'
        ) {
            save_location();
        }


        /*
        |--------------------------------------------------------------------------
        | Religion
        |--------------------------------------------------------------------------
        */

        if (
            $method === 'POST' &&
            $action === 'religion'
        ) {
            save_religion();
        }


        /*
        |--------------------------------------------------------------------------
        | Education / Career
        |--------------------------------------------------------------------------
        */

        if (
            $method === 'POST' &&
            $action === 'education'
        ) {
            save_education();
        }


        /*
        |--------------------------------------------------------------------------
        | Preferences
        |--------------------------------------------------------------------------
        */

        if (
            $method === 'POST' &&
            $action === 'preferences'
        ) {
            save_preferences();
        }


        /*
        |--------------------------------------------------------------------------
        | Final Registration
        |--------------------------------------------------------------------------
        */

        if (
            $method === 'POST' &&
            $action === 'complete'
        ) {
            complete_registration();
        }

        /*
        |--------------------------------------------------------------------------
        | Profile Section Editing
        |--------------------------------------------------------------------------
        */
        if (
            $method === 'POST' &&
            $action === 'update-section'
        ) {
            update_profile_section();
        }

        if (
            $method === 'GET' &&
            $action === 'photos'
        ) {
            get_profile_photos();
        }

        if (
            $method === 'POST' &&
            $action === 'photos'
        ) {
            save_profile_photos();
        }
        if (
            $method === 'GET' &&
            $action === 'completion-status'
        ) {
            get_profile_completion_status();
        }

        error_response(
            'Endpoint not found.',
            [],
            404
        );
    }
    
    /*
|--------------------------------------------------------------------------
| MATCHING PROFILES ROUTE
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| PROFILE VIEW ROUTE
|--------------------------------------------------------------------------
*/
if (
    ($segments[0] ?? '') === 'profile-view' &&
    $method === 'GET'
) {
    get_profile_view($segments[1] ?? '');
}

if (
    ($segments[0] ?? '') === 'matching-profiles' &&
    $method === 'GET'
) {
    get_matching_profiles();
}

    /*
    |--------------------------------------------------------------------------
    | UNKNOWN ROUTE
    |--------------------------------------------------------------------------
    */

    error_response(
        'Endpoint not found.',
        [],
        404
    );

} catch (PDOException $e) {

    error_response(
        'Database connection or query failed.',
        [],
        500
    );

} catch (Throwable $e) {

    error_response(
        'Unexpected server error.',
        [],
        500
    );
}