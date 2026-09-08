<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/photo-security.php';


/*
|--------------------------------------------------------------------------
| GET MATCHING PROFILES
|--------------------------------------------------------------------------
|
| GET /matching-profiles
|
| Authentication:
| Bearer token required
|
| Matching preferences:
| - Age
| - Marital Status
| - District
| - Religion
| - Education
|
| Excluded:
| - Same user
| - Same gender
| - Blocked / rejected / incomplete profiles
|
*/


function get_matching_profiles(): never
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
    | CURRENT USER PROFILE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'SELECT
            id,
            user_id,
            gender,
            date_of_birth,
            home_verified
         FROM profiles
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $currentProfile = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$currentProfile) {

        error_response(
            'Your profile was not found.',
            [],
            404
        );
    }

    $viewerHomeVerified = ((int)($currentProfile['home_verified'] ?? 0)) === 1;


    /*
    |--------------------------------------------------------------------------
    | CURRENT USER PREFERENCES
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'SELECT
            age_min,
            age_max,
            preferred_religion
         FROM profile_preferences
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $preferences =
        $stmt->fetch(PDO::FETCH_ASSOC) ?: [];


    /*
    |--------------------------------------------------------------------------
    | CURRENT USER BASIC VALUES
    |--------------------------------------------------------------------------
    */

    $currentGender =
        strtolower(
            trim((string) ($currentProfile['gender'] ?? ''))
        );


    /*
    |--------------------------------------------------------------------------
    | AGE PREFERENCE
    |--------------------------------------------------------------------------
    */

    $ageMin = null;
    $ageMax = null;

    if (
        isset($preferences['age_min']) &&
        $preferences['age_min'] !== ''
    ) {
        $ageMin = (int) $preferences['age_min'];
    }

    if (
        isset($preferences['age_max']) &&
        $preferences['age_max'] !== ''
    ) {
        $ageMax = (int) $preferences['age_max'];
    }


    /*
    |--------------------------------------------------------------------------
    | RELIGION PREFERENCE
    |--------------------------------------------------------------------------
    */

    $preferredReligion =
        strtolower(
            trim(
                (string) (
                    $preferences['preferred_religion']
                    ?? ''
                )
            )
        );


    /*
    |--------------------------------------------------------------------------
    | MULTI-VALUE PREFERENCES
    |--------------------------------------------------------------------------
    */

    $getPreferenceValues = function (
        PDO $pdo,
        int $userId,
        string $type
    ): array {

        $stmt = $pdo->prepare(
            'SELECT value
             FROM preference_values
             WHERE user_id = ?
               AND preference_type = ?
             ORDER BY id ASC'
        );

        $stmt->execute([
            $userId,
            $type
        ]);

        return array_values(
            array_filter(
                array_map(
                    static function ($row) {
                        return strtolower(
                            trim(
                                (string) ($row['value'] ?? '')
                            )
                        );
                    },
                    $stmt->fetchAll(PDO::FETCH_ASSOC)
                ),
                static function ($value) {
                    return $value !== '';
                }
            )
        );
    };


    $preferredMaritalStatuses =
        $getPreferenceValues(
            $pdo,
            $userId,
            'marital_status'
        );


    $preferredEducation =
        $getPreferenceValues(
            $pdo,
            $userId,
            'education'
        );


    $preferredLocations =
        $getPreferenceValues(
            $pdo,
            $userId,
            'location'
        );


    /*
    |--------------------------------------------------------------------------
    | BUILD QUERY
    |--------------------------------------------------------------------------
    */

    $sql = '
        SELECT
            p.user_id,
            u.member_id,
            p.full_name,
            p.gender,
            p.marital_status,
            p.date_of_birth,
            p.district,
            p.religion,
            p.highest_education,
            p.home_verified,
            p.created_at,

            (
                SELECT pp.id
                FROM profile_photos pp
                WHERE pp.user_id = p.user_id
                AND pp.status = "active"
                AND pp.is_primary = 1
                ORDER BY pp.id ASC
                LIMIT 1
            ) AS photo_id

        FROM profiles p

        INNER JOIN users u
            ON u.id = p.user_id

        WHERE p.user_id <> :current_user_id

          AND p.gender <> :current_gender

          

          AND u.role = "user"

          AND u.account_status = "active"

          AND p.registration_completed = 1

          AND p.profile_status IN (
              "new",
              "pending_verification",
              "verified"
          )




          AND NOT EXISTS (
            SELECT 1
            FROM interests i
            WHERE
                (
                   (
                i.sender_user_id = :current_user_id_1
                AND i.receiver_user_id = p.user_id
            )
            OR
            (
                i.sender_user_id = p.user_id
                AND i.receiver_user_id = :current_user_id_2
            )
                )
                AND i.status =  \'declined\'
)
    ';


    $params = [
        ':current_user_id' => $userId,
    ':current_user_id_1' => $userId,
    ':current_user_id_2' => $userId,
    ':current_gender' => $currentGender
    ];


    /*
    |--------------------------------------------------------------------------
    | AGE FILTER
    |--------------------------------------------------------------------------
    */

    if (
        $ageMin !== null &&
        $ageMax !== null &&
        $ageMin > 0 &&
        $ageMax > 0
    ) {

        // Convert the age range into a DOB range so MySQL can use
        // an index on p.date_of_birth instead of applying a function
        // to every candidate row.
        $today = new DateTimeImmutable('today');

        $maxBirthDateExclusive = $today
            ->modify('-' . ($ageMax + 1) . ' years')
            ->format('Y-m-d');

        $minBirthDateInclusive = $today
            ->modify('-' . $ageMin . ' years')
            ->format('Y-m-d');

        $sql .= '
            AND p.date_of_birth > :max_birth_date_exclusive
            AND p.date_of_birth <= :min_birth_date_inclusive
        ';

        $params[':max_birth_date_exclusive'] =
            $maxBirthDateExclusive;
        $params[':min_birth_date_inclusive'] =
            $minBirthDateInclusive;
    }


    /*
    |--------------------------------------------------------------------------
    | RELIGION FILTER
    |--------------------------------------------------------------------------
    */

    if ($preferredReligion !== '') {

        $sql .= '
            AND p.religion = :preferred_religion
        ';

        $params[':preferred_religion'] =
            $preferredReligion;
    }


    /*
    |--------------------------------------------------------------------------
    | MARITAL STATUS FILTER
    |--------------------------------------------------------------------------
    */

    if (count($preferredMaritalStatuses) > 0) {

        $placeholders = [];

        foreach (
            $preferredMaritalStatuses
            as $index => $status
        ) {

            $placeholder =
                ':marital_' . $index;

            $placeholders[] =
                $placeholder;

            $params[$placeholder] =
                $status;
        }

        $sql .= '
            AND p.marital_status IN (
                ' . implode(', ', $placeholders) . '
            )
        ';
    }


    /*
    |--------------------------------------------------------------------------
    | EDUCATION FILTER
    |--------------------------------------------------------------------------
    */

    if (count($preferredEducation) > 0) {

        $placeholders = [];

        foreach (
            $preferredEducation
            as $index => $education
        ) {

            $placeholder =
                ':education_' . $index;

            $placeholders[] =
                $placeholder;

            $params[$placeholder] =
                $education;
        }

        $sql .= '
            AND p.highest_education IN (
                ' . implode(', ', $placeholders) . '
            )
        ';
    }


    /*
    |--------------------------------------------------------------------------
    | DISTRICT FILTER
    |--------------------------------------------------------------------------
    */

    if (count($preferredLocations) > 0) {

    // "All Kerala" means every Kerala district is accepted.
    if (in_array('all kerala', $preferredLocations, true)) {

        // No district filter required.

    } else {

        $placeholders = [];

        foreach (
            $preferredLocations
            as $index => $location
        ) {

            $placeholder =
                ':location_' . $index;

            $placeholders[] =
                $placeholder;

            $params[$placeholder] =
                $location;
        }

        $sql .= '
            AND p.district IN (
                ' . implode(', ', $placeholders) . '
            )
        ';
    }
}

    /*
    |--------------------------------------------------------------------------
    | ORDER
    |--------------------------------------------------------------------------
    */

    $sql .= '
        ORDER BY
             photo_id IS NULL ASC,
        p.home_verified DESC,
        p.created_at DESC
    ';


    /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $rows =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | FORMAT RESPONSE
    |--------------------------------------------------------------------------
    */

    $profiles = [];

    $today = new DateTimeImmutable('today');


    foreach ($rows as $row) {

        /*
        |--------------------------------------------------------------------------
        | AGE
        |--------------------------------------------------------------------------
        */

        $age = null;

        if (!empty($row['date_of_birth'])) {

            $birthDate =
                new DateTime(
                    $row['date_of_birth']
                );

            $age =
                $birthDate->diff($today)->y;
        }


        /*
        |--------------------------------------------------------------------------
        | PHOTO
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Original photo is NOT returned yet.
        | We will connect the backend blurred-photo
        | endpoint in the next step.
        |
        */

        $photoUrl = null;

        if (!empty($row['photo_id'])) {
            $photoUrl = photo_url_for_viewer(
                (int)$row['photo_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL CARD DATA
        |--------------------------------------------------------------------------
        */

        $profiles[] = [

            'memberId' =>
                (string) $row['member_id'],

            'name' =>
                (string) $row['full_name'],

            'age' =>
                $age,

            'maritalStatus' =>
                (string) $row['marital_status'],

            'district' =>
                (string) $row['district'],

            'religion' =>
                (string) $row['religion'],

            'education' =>
                (string) $row['highest_education'],

            'photoUrl' =>
                $photoUrl,

            'verified' =>
                ((int) $row['home_verified']) === 1
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    success_response(
        'Matching profiles fetched successfully.',
        [
            'profiles' => $profiles,
            'count' => count($profiles)
        ]
    );
}