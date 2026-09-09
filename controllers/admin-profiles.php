<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


function get_admin_profiles(): never
{
    $admin = current_user(true);

    /*
     * Only admin users can access this endpoint.
     */
    if (($admin['role'] ?? '') !== 'admin') {
        error_response(
            'Admin access required.',
            [],
            403
        );
    }


    /*
     * Verify admin_users access.
     */
    $stmt = db()->prepare(
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


    /*
     * Fetch only registration-completed profiles.
     *
     * Latest profile first:
     * created_at DESC
     *
     * id DESC is used as a secondary sort when
     * two profiles have the same created_at.
     */
    $stmt = db()->prepare(
        "SELECT
            p.id,
            p.user_id,
            u.member_id,
            u.phone,
            p.full_name,
            p.gender,
            p.place,
            p.district,
            p.profile_status,
            p.registration_completed,
            p.created_at,
            COALESCE(pl.name, 'Free') AS plan
         FROM profiles p

         INNER JOIN users u
            ON u.id = p.user_id

         LEFT JOIN payments pay
            ON pay.user_id = p.user_id
            AND pay.payment_status = 'success'
            AND pay.id = (
                SELECT MAX(pay2.id)
                FROM payments pay2
                WHERE pay2.user_id = p.user_id
                  AND pay2.payment_status = 'success'
            )

         LEFT JOIN plans pl
            ON pl.id = pay.plan_id

         WHERE p.registration_completed = 1

         ORDER BY
            p.created_at DESC,
            p.id DESC"
    );

    $stmt->execute();

    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * Convert database status to frontend display status.
     */
    $result = [];

    foreach ($profiles as $profile) {

        $status = match ($profile['profile_status'] ?? '') {

            'verified' => 'Verified',

            'pending_verification' => 'Pending',

            'new' => 'New',

            'rejected' => 'Rejected',

            'blocked' => 'Blocked',

            default => 'New'
        };


        $result[] = [
            'id' => $profile['member_id'],
            'name' => $profile['full_name'],
            'gender' => $profile['gender'],
            'place' => $profile['place'],
            'district' => $profile['district'],
            'phone' => $profile['phone'],
            'status' => $status,
            'plan' => $profile['plan'],
            'created_at' => $profile['created_at']
        ];
    }


    success_response(
        'Registered profiles fetched successfully.',
        [
            'profiles' => $result,
            'count' => count($result)
        ]
    );
}

function require_admin_profile_access(): array
{
    $admin = current_user(true);
    if (($admin['role'] ?? '') !== 'admin') {
        error_response('Admin access required.', [], 403);
    }

    $stmt = db()->prepare(
        'SELECT admin_role, status FROM admin_users WHERE user_id = ? LIMIT 1'
    );
    $stmt->execute([(int)$admin['id']]);
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$access || ($admin['account_status'] ?? '') !== 'active' || ($access['status'] ?? '') !== 'active') {
        error_response('Admin access is not active.', [], 403);
    }

    return $admin;
}

function get_admin_profile(string $memberId): never
{
    require_admin_profile_access();

    $memberId = trim($memberId);
    if ($memberId === '' || !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $memberId)) {
        error_response('Invalid member ID.', [], 422);
    }

    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT p.*, u.id AS user_id, u.member_id, u.phone, u.account_status,
                COALESCE(pl.name, \'Free\') AS plan
         FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         LEFT JOIN (
             SELECT pay.user_id, pay.plan_id
             FROM payments pay
             INNER JOIN (
                 SELECT user_id, MAX(id) max_id
                 FROM payments
                 WHERE payment_status = \'success\'
                 GROUP BY user_id
             ) latest
               ON latest.user_id = pay.user_id AND latest.max_id = pay.id
         ) latest_success ON latest_success.user_id = u.id
         LEFT JOIN plans pl ON pl.id = latest_success.plan_id
         WHERE u.member_id = ? AND u.role = \'user\'
         LIMIT 1'
    );

    $stmt->execute([$memberId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        error_response('Profile not found.', [], 404);
    }

    $photosStmt = $pdo->prepare(
        'SELECT file_path
         FROM profile_photos
         WHERE user_id = ? AND status = \'active\'
         ORDER BY is_primary DESC, display_order ASC, id ASC'
    );
    $photosStmt->execute([(int)$row['user_id']]);

    $photos = array_values(
        array_filter(
            array_map(
                fn($r) => trim((string)$r['file_path']),
                $photosStmt->fetchAll(PDO::FETCH_ASSOC)
            )
        )
    );

    $prefStmt = $pdo->prepare(
        'SELECT *
         FROM profile_preferences
         WHERE user_id = ?
         LIMIT 1'
    );
    $prefStmt->execute([(int)$row['user_id']]);
    $preferences = $prefStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $valuesStmt = $pdo->prepare(
        'SELECT preference_type, value
         FROM preference_values
         WHERE user_id = ?
         ORDER BY id ASC'
    );
    $valuesStmt->execute([(int)$row['user_id']]);

    $preferenceValues = [];
    foreach ($valuesStmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
        $type = (string)$v['preference_type'];
        $value = trim((string)$v['value']);
        if ($value !== '') {
            $preferenceValues[$type][] = $value;
        }
    }

    $age = null;
    if (!empty($row['date_of_birth'])) {
        try {
            $dob = new DateTime((string)$row['date_of_birth']);
            $today = new DateTime('today');
            if ($dob <= $today) {
                $age = $dob->diff($today)->y;
            }
        } catch (Throwable $e) {
            $age = null;
        }
    }

    $status = match ($row['profile_status'] ?? '') {
        'verified' => 'Verified',
        'pending_verification' => 'Pending',
        'new' => 'New',
        'rejected' => 'Rejected',
        'blocked' => 'Blocked',
        default => 'New'
    };

    $profile = [
        'id' => $row['member_id'],
        'userId' => (int)$row['user_id'],

        'profileFor' => $row['profile_for'] ?? '',
        'fullName' => $row['full_name'] ?? '',
        'name' => $row['full_name'] ?? '',
        'gender' => $row['gender'] ?? '',
        'maritalStatus' => $row['marital_status'] ?? '',
        'hasKids' => $row['has_kids'] ?? '',
        'numberOfKids' => $row['number_of_kids'] !== null ? (int)$row['number_of_kids'] : null,
        'kidsLivingStatus' => $row['kids_living_status'] ?? '',
        'dateOfBirth' => $row['date_of_birth'] ?? null,
        'age' => $age,
        'height' => $row['height'] !== null ? (float)$row['height'] : null,

        'houseName' => $row['house_name'] ?? '',
        'place' => $row['place'] ?? '',
        'district' => $row['district'] ?? '',
        'state' => $row['state'] ?? '',
        'pincode' => $row['pincode'] ?? '',

        'religion' => $row['religion'] ?? '',
        'sect' => $row['sect'] ?? '',
        'community' => $row['sect'] ?: ($row['caste'] ?? ''),
        'muslimGroup' => $row['muslim_group'] ?? '',
        'salafiGroup' => $row['salafi_group'] ?? '',
        'caste' => $row['caste'] ?? '',
        'subCaste' => $row['sub_caste'] ?? '',
        'nakshatra' => $row['nakshatra'] ?? '',
        'rashi' => $row['rashi'] ?? '',
        'dosham' => $row['dosham'] ?? '',
        'denomination' => $row['denomination'] ?? '',
        'christianDenomination' => $row['denomination'] ?? '',
        'christianSubGroup' => $row['christian_sub_group'] ?? '',
        'parishName' => $row['parish_name'] ?? '',

        'highestEducation' => $row['highest_education'] ?? '',
        'education' => $row['highest_education'] ?? '',
        'specialization' => $row['specialization'] ?? '',
        'jobTitle' => $row['job_title'] ?? '',
        'jobSector' => $row['job_sector'] ?? '',
        'companyName' => $row['company_name'] ?? '',
        'company' => $row['company_name'] ?? '',
        'workLocation' => $row['work_location'] ?? '',
        'annualIncome' => $row['annual_income'] ?? '',

        'weight' => $row['weight'] !== null ? (float)$row['weight'] : null,
        'bodyType' => $row['body_type'] ?? '',
        'complexion' => $row['complexion'] ?? '',
        'physicalStatus' => $row['physical_status'] ?? '',

        'fatherName' => $row['father_name'] ?? '',
        'motherName' => $row['mother_name'] ?? '',
        'brothers' => $row['brothers'] !== null ? (int)$row['brothers'] : null,
        'sisters' => $row['sisters'] !== null ? (int)$row['sisters'] : null,
        'familyStatus' => $row['family_status'] ?? '',
        'homeType' => $row['home_type'] ?? '',

        'phone' => $row['phone'] ?? '',
        'secondaryMobile' => $row['secondary_mobile'] ?? '',
        'whatsappNumber' => $row['whatsapp_number'] ?? '',
        'email' => $row['email'] ?? '',
        'expectations' => $row['expectations'] ?? '',

        'accountStatus' => $row['account_status'] ?? '',
        'plan' => $row['plan'] ?? 'Free',
        'verificationStatus' => $status,
        'homeVerified' => (bool)($row['home_verified'] ?? false),
        'photos' => $photos,
        'primaryPhoto' => $photos[0] ?? '',

        'preferredAgeMin' => isset($preferences['age_min']) && $preferences['age_min'] !== null
            ? (int)$preferences['age_min'] : null,
        'preferredAgeMax' => isset($preferences['age_max']) && $preferences['age_max'] !== null
            ? (int)$preferences['age_max'] : null,
        'preferredHeightMin' => isset($preferences['height_min']) && $preferences['height_min'] !== null
            ? (float)$preferences['height_min'] : null,
        'preferredHeightMax' => isset($preferences['height_max']) && $preferences['height_max'] !== null
            ? (float)$preferences['height_max'] : null,
        'preferredReligion' => $preferences['preferred_religion'] ?? '',
        'acceptanceOfKids' => $preferences['acceptance_of_kids'] ?? '',

        'preferredMaritalStatuses' => $preferenceValues['marital_status'] ?? [],
        'preferredMaritalStatus' => $preferenceValues['marital_status'] ?? [],
        'preferredSects' => $preferenceValues['sect'] ?? [],
        'preferredSunniGroups' => $preferenceValues['sunni_group'] ?? [],
        'preferredSalafiGroups' => $preferenceValues['salafi_group'] ?? [],
        'preferredCastes' => $preferenceValues['caste'] ?? [],
        'preferredSubCastes' => $preferenceValues['sub_caste'] ?? [],
        'preferredEducation' => $preferenceValues['education'] ?? [],
        'preferredEducationSpecific' => $preferenceValues['education_specific'] ?? [],
        'preferredCareerSectors' => $preferenceValues['career_sector'] ?? [],
        'preferredLocations' => $preferenceValues['location'] ?? [],
        'preferredLocationRadius' => $preferenceValues['location_radius'] ?? []
    ];

    success_response(
        'Admin profile fetched successfully.',
        ['profile' => $profile]
    );
}

function update_admin_profile(string $memberId): never
{
    require_admin_profile_access();

    $memberId = trim($memberId);
    if ($memberId === '' || !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $memberId)) {
        error_response('Invalid member ID.', [], 422);
    }

    $data = request_json();
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT p.user_id
         FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         WHERE u.member_id = ? AND u.role = \'user\'
         LIMIT 1'
    );
    $stmt->execute([$memberId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        error_response('Profile not found.', [], 404);
    }

    $userId = (int)$user['user_id'];

    // Same camelCase field names used by the new admin registration-style editor.
    $profileMap = [
        'profile_for' => 'profileFor',
        'full_name' => 'fullName',
        'gender' => 'gender',
        'marital_status' => 'maritalStatus',
        'has_kids' => 'hasKids',
        'number_of_kids' => 'numberOfKids',
        'kids_living_status' => 'kidsLivingStatus',
        'date_of_birth' => 'dateOfBirth',
        'height' => 'height',

        'house_name' => 'houseName',
        'place' => 'place',
        'district' => 'district',
        'state' => 'state',
        'pincode' => 'pincode',

        'religion' => 'religion',
        'sect' => 'sect',
        'muslim_group' => 'muslimGroup',
        'salafi_group' => 'salafiGroup',
        'caste' => 'caste',
        'sub_caste' => 'subCaste',
        'nakshatra' => 'nakshatra',
        'rashi' => 'rashi',
        'dosham' => 'dosham',
        'denomination' => 'christianDenomination',
        'christian_sub_group' => 'christianSubGroup',
        'parish_name' => 'parishName',

        'highest_education' => 'highestEducation',
        'specialization' => 'specialization',
        'job_title' => 'jobTitle',
        'job_sector' => 'jobSector',
        'company_name' => 'companyName',
        'work_location' => 'workLocation',
        'annual_income' => 'annualIncome',

        'weight' => 'weight',
        'body_type' => 'bodyType',
        'complexion' => 'complexion',
        'physical_status' => 'physicalStatus',

        'father_name' => 'fatherName',
        'mother_name' => 'motherName',
        'brothers' => 'brothers',
        'sisters' => 'sisters',
        'family_status' => 'familyStatus',
        'home_type' => 'homeType',

        'secondary_mobile' => 'secondaryMobile',
        'whatsapp_number' => 'whatsappNumber',
        'email' => 'email',
        'expectations' => 'expectations'
    ];

    // Backward compatibility with the previous admin editor payload.
    if (!array_key_exists('sect', $data) && array_key_exists('community', $data)) {
        $data['sect'] = $data['community'];
    }
    if (!array_key_exists('companyName', $data) && array_key_exists('company', $data)) {
        $data['companyName'] = $data['company'];
    }

    if (array_key_exists('gender', $data)
        && !in_array($data['gender'], ['male', 'female'], true)
    ) {
        error_response('Invalid gender.', [], 422);
    }

    if (array_key_exists('profileFor', $data)
        && $data['profileFor'] !== ''
        && !in_array(
            $data['profileFor'],
            ['self', 'sister', 'brother', 'son', 'daughter', 'friend', 'relative'],
            true
        )
    ) {
        error_response('Invalid profile for value.', [], 422);
    }

    if (array_key_exists('dateOfBirth', $data)
        && $data['dateOfBirth'] !== null
        && $data['dateOfBirth'] !== ''
    ) {
        $dob = DateTime::createFromFormat('!Y-m-d', (string)$data['dateOfBirth']);

        if (!$dob || $dob->format('Y-m-d') !== (string)$data['dateOfBirth']) {
            error_response('Invalid date of birth.', [], 422);
        }

        if ($dob > new DateTime('today')) {
            error_response('Date of birth cannot be in the future.', [], 422);
        }
    }

    if (array_key_exists('height', $data)
        && $data['height'] !== null
        && $data['height'] !== ''
    ) {
        $height = (float)$data['height'];
        if ($height < 48 || $height > 87) {
            error_response('Invalid height.', [], 422);
        }
    }

    if (array_key_exists('numberOfKids', $data)
        && $data['numberOfKids'] !== null
        && $data['numberOfKids'] !== ''
    ) {
        $kids = (int)$data['numberOfKids'];
        if ($kids < 0 || $kids > 20) {
            error_response('Invalid number of kids.', [], 422);
        }
    }

    if (array_key_exists('weight', $data)
        && $data['weight'] !== null
        && $data['weight'] !== ''
    ) {
        $weight = (float)$data['weight'];
        if ($weight < 1 || $weight > 500) {
            error_response('Invalid weight.', [], 422);
        }
    }

    $sets = [];
    $params = [];

    foreach ($profileMap as $column => $key) {
        if (!array_key_exists($key, $data)) {
            continue;
        }

        $value = $data[$key];

        if (in_array($column, ['height', 'weight'], true)) {
            $value = ($value === null || $value === '') ? null : (float)$value;
        }

        if (in_array($column, ['brothers', 'sisters', 'number_of_kids'], true)) {
            $value = ($value === null || $value === '') ? null : (int)$value;
        }

        $sets[] = $column . ' = ?';
        $params[] = $value;
    }

    $hasPreferenceData =
        array_key_exists('preferredAgeMin', $data) ||
        array_key_exists('preferredAgeMax', $data) ||
        array_key_exists('preferredHeightMin', $data) ||
        array_key_exists('preferredHeightMax', $data) ||
        array_key_exists('preferredReligion', $data) ||
        array_key_exists('acceptanceOfKids', $data) ||
        array_key_exists('preferredMaritalStatuses', $data) ||
        array_key_exists('preferredSects', $data) ||
        array_key_exists('preferredSunniGroups', $data) ||
        array_key_exists('preferredSalafiGroups', $data) ||
        array_key_exists('preferredCastes', $data) ||
        array_key_exists('preferredSubCastes', $data) ||
        array_key_exists('preferredLocations', $data) ||
        array_key_exists('preferredEducation', $data) ||
        array_key_exists('preferredEducationSpecific', $data) ||
        array_key_exists('preferredCareerSectors', $data);

    if (!$sets && !$hasPreferenceData) {
        error_response('No editable profile fields were supplied.', [], 422);
    }

    // Age is derived from date_of_birth and is intentionally not stored.
    unset($data['age']);

    $pdo->beginTransaction();

    try {
        if ($sets) {
            $params[] = $userId;

            $pdo->prepare(
                'UPDATE profiles
                 SET ' . implode(', ', $sets) . '
                 WHERE user_id = ?'
            )->execute($params);
        }

        // Same profile_preferences structure as registration.
        $preferenceFieldsSupplied =
            array_key_exists('preferredAgeMin', $data) ||
            array_key_exists('preferredAgeMax', $data) ||
            array_key_exists('preferredHeightMin', $data) ||
            array_key_exists('preferredHeightMax', $data) ||
            array_key_exists('preferredReligion', $data) ||
            array_key_exists('acceptanceOfKids', $data);

        if ($preferenceFieldsSupplied) {
            $prefStmt = $pdo->prepare(
                'INSERT INTO profile_preferences
                    (user_id, age_min, age_max, height_min, height_max,
                     preferred_religion, acceptance_of_kids)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    age_min = VALUES(age_min),
                    age_max = VALUES(age_max),
                    height_min = VALUES(height_min),
                    height_max = VALUES(height_max),
                    preferred_religion = VALUES(preferred_religion),
                    acceptance_of_kids = VALUES(acceptance_of_kids)'
            );

            $prefStmt->execute([
                $userId,
                (array_key_exists('preferredAgeMin', $data)
                    && $data['preferredAgeMin'] !== null
                    && $data['preferredAgeMin'] !== '')
                    ? (int)$data['preferredAgeMin'] : null,
                (array_key_exists('preferredAgeMax', $data)
                    && $data['preferredAgeMax'] !== null
                    && $data['preferredAgeMax'] !== '')
                    ? (int)$data['preferredAgeMax'] : null,
                (array_key_exists('preferredHeightMin', $data)
                    && $data['preferredHeightMin'] !== null
                    && $data['preferredHeightMin'] !== '')
                    ? (float)$data['preferredHeightMin'] : null,
                (array_key_exists('preferredHeightMax', $data)
                    && $data['preferredHeightMax'] !== null
                    && $data['preferredHeightMax'] !== '')
                    ? (float)$data['preferredHeightMax'] : null,
                $data['preferredReligion'] ?? null,
                $data['acceptanceOfKids'] ?? null
            ]);
        }

        // Same preference_types as registration.
        $preferenceGroups = [
            'marital_status' => 'preferredMaritalStatuses',
            'sect' => 'preferredSects',
            'sunni_group' => 'preferredSunniGroups',
            'salafi_group' => 'preferredSalafiGroups',
            'caste' => 'preferredCastes',
            'sub_caste' => 'preferredSubCastes',
            'education' => 'preferredEducation',
            'education_specific' => 'preferredEducationSpecific',
            'career_sector' => 'preferredCareerSectors',
            'location' => 'preferredLocations'
        ];

        $deleteStmt = $pdo->prepare(
            'DELETE FROM preference_values
             WHERE user_id = ? AND preference_type = ?'
        );

        $valueStmt = $pdo->prepare(
            'INSERT INTO preference_values
                (user_id, preference_type, value)
             VALUES (?, ?, ?)'
        );

        foreach ($preferenceGroups as $type => $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $values = is_array($data[$key]) ? $data[$key] : [$data[$key]];

            $deleteStmt->execute([$userId, $type]);

            foreach ($values as $value) {
                $value = trim((string)$value);
                if ($value === '') {
                    continue;
                }

                $valueStmt->execute([$userId, $type, $value]);
            }
        }

        $pdo->commit();

        success_response(
            'Profile updated successfully.',
            ['member_id' => $memberId]
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_response('Unable to update profile.', [], 500);
    }
}
