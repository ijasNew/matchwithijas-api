<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


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
            date_of_birth
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
        trim((string) ($currentProfile['gender'] ?? ''));


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
        trim(
            (string) (
                $preferences['preferred_religion']
                ?? ''
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
                        return trim(
                            (string) ($row['value'] ?? '')
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

            (
                SELECT pp.file_path
                FROM profile_photos pp
                WHERE pp.user_id = p.user_id
                  AND pp.status = "active"
                  AND pp.is_primary = 1
                ORDER BY pp.id ASC
                LIMIT 1
            ) AS photo_path

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
    ';


    $params = [
        ':current_user_id' => $userId,
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

        $sql .= '
            AND TIMESTAMPDIFF(
                YEAR,
                p.date_of_birth,
                CURDATE()
            ) BETWEEN :age_min AND :age_max
        ';

        $params[':age_min'] = $ageMin;
        $params[':age_max'] = $ageMax;
    }


    /*
    |--------------------------------------------------------------------------
    | RELIGION FILTER
    |--------------------------------------------------------------------------
    */

    if ($preferredReligion !== '') {

        $sql .= '
            AND LOWER(TRIM(p.religion))
                = LOWER(TRIM(:preferred_religion))
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
            AND LOWER(TRIM(p.marital_status))
                IN (
                    ' .
                    implode(
                        ', ',
                        array_map(
                            static function ($placeholder) {
                                return 'LOWER(TRIM(' .
                                    $placeholder .
                                    '))';
                            },
                            $placeholders
                        )
                    )
                . '
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
            AND LOWER(TRIM(p.highest_education))
                IN (
                    ' .
                    implode(
                        ', ',
                        array_map(
                            static function ($placeholder) {
                                return 'LOWER(TRIM(' .
                                    $placeholder .
                                    '))';
                            },
                            $placeholders
                        )
                    )
                . '
                )
        ';
    }


    /*
    |--------------------------------------------------------------------------
    | DISTRICT FILTER
    |--------------------------------------------------------------------------
    */

    if (count($preferredLocations) > 0) {

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
            AND LOWER(TRIM(p.district))
                IN (
                    ' .
                    implode(
                        ', ',
                        array_map(
                            static function ($placeholder) {
                                return 'LOWER(TRIM(' .
                                    $placeholder .
                                    '))';
                            },
                            $placeholders
                        )
                    )
                . '
                )
        ';
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE DUPLICATES
    |--------------------------------------------------------------------------
    */

    $sql .= '
        GROUP BY
            p.user_id,
            u.member_id,
            p.full_name,
            p.gender,
            p.marital_status,
            p.date_of_birth,
            p.district,
            p.religion,
            p.highest_education,
            p.home_verified
    ';


    /*
    |--------------------------------------------------------------------------
    | ORDER
    |--------------------------------------------------------------------------
    */

    $sql .= '
        ORDER BY
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

            $today =
                new DateTime();

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


        if (
            !empty($row['photo_path'])
        ) {

            $photoUrl =
                $row['photo_path'];
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