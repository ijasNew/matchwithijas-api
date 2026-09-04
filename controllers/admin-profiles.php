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
                COALESCE(pl.name, "Free") AS plan
         FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         LEFT JOIN (
             SELECT pay.user_id, pay.plan_id
             FROM payments pay
             INNER JOIN (SELECT user_id, MAX(id) max_id FROM payments WHERE payment_status = "success" GROUP BY user_id) latest
               ON latest.user_id = pay.user_id AND latest.max_id = pay.id
         ) latest_success ON latest_success.user_id = u.id
         LEFT JOIN plans pl ON pl.id = latest_success.plan_id
         WHERE u.member_id = ? AND u.role = "user"
         LIMIT 1'
    );
    $stmt->execute([$memberId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) error_response('Profile not found.', [], 404);

    $photosStmt = $pdo->prepare('SELECT file_path FROM profile_photos WHERE user_id = ? AND status = "active" ORDER BY is_primary DESC, display_order ASC, id ASC');
    $photosStmt->execute([(int)$row['user_id']]);
    $photos = array_values(array_filter(array_map(fn($r) => trim((string)$r['file_path']), $photosStmt->fetchAll(PDO::FETCH_ASSOC))));

    $prefStmt = $pdo->prepare('SELECT * FROM profile_preferences WHERE user_id = ? LIMIT 1');
    $prefStmt->execute([(int)$row['user_id']]);
    $preferences = $prefStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $valuesStmt = $pdo->prepare('SELECT preference_type, value FROM preference_values WHERE user_id = ? ORDER BY id ASC');
    $valuesStmt->execute([(int)$row['user_id']]);
    $preferenceValues = [];
    foreach ($valuesStmt->fetchAll(PDO::FETCH_ASSOC) as $v) { $preferenceValues[$v['preference_type']][] = $v['value']; }

    $age = null;
    if (!empty($row['date_of_birth'])) { try { $age = (new DateTime($row['date_of_birth']))->diff(new DateTime())->y; } catch (Throwable $e) {} }

    $status = match ($row['profile_status'] ?? '') {
        'verified' => 'Verified', 'pending_verification' => 'Pending', 'new' => 'New',
        'rejected' => 'Rejected', 'blocked' => 'Blocked', default => 'New'
    };

    $profile = [
        'id' => $row['member_id'], 'userId' => (int)$row['user_id'], 'name' => $row['full_name'],
        'fullName' => $row['full_name'], 'gender' => $row['gender'], 'age' => $age,
        'maritalStatus' => $row['marital_status'], 'height' => $row['height'] !== null ? (float)$row['height'] : 0,
        'place' => $row['place'], 'district' => $row['district'], 'state' => $row['state'], 'pincode' => $row['pincode'],
        'religion' => $row['religion'], 'community' => $row['sect'] ?: ($row['caste'] ?? ''),
        'sect' => $row['sect'] ?? '', 'caste' => $row['caste'] ?? '', 'subCaste' => $row['sub_caste'] ?? '',
        'education' => $row['highest_education'], 'highestEducation' => $row['highest_education'], 'specialization' => $row['specialization'] ?? '',
        'jobTitle' => $row['job_title'], 'jobSector' => $row['job_sector'], 'company' => $row['company_name'] ?? '',
        'companyName' => $row['company_name'] ?? '', 'workLocation' => $row['work_location'] ?? '', 'annualIncome' => $row['annual_income'] ?? '',
        'weight' => $row['weight'] !== null ? (float)$row['weight'] : 0, 'bodyType' => $row['body_type'] ?? '',
        'complexion' => $row['complexion'] ?? '', 'physicalStatus' => $row['physical_status'] ?? '',
        'fatherName' => $row['father_name'] ?? '', 'motherName' => $row['mother_name'] ?? '',
        'brothers' => $row['brothers'] !== null ? (int)$row['brothers'] : 0, 'sisters' => $row['sisters'] !== null ? (int)$row['sisters'] : 0,
        'familyStatus' => $row['family_status'] ?? '', 'homeType' => $row['home_type'] ?? '',
        'phone' => $row['phone'] ?? '', 'secondaryMobile' => $row['secondary_mobile'] ?? '', 'whatsappNumber' => $row['whatsapp_number'] ?? '', 'email' => $row['email'] ?? '',
        'expectations' => $row['expectations'] ?? '', 'plan' => $row['plan'] ?? 'Free', 'verificationStatus' => $status,
        'photos' => $photos, 'primaryPhoto' => $photos[0] ?? '',
        'preferredAgeMin' => isset($preferences['age_min']) ? (int)$preferences['age_min'] : 0,
        'preferredAgeMax' => isset($preferences['age_max']) ? (int)$preferences['age_max'] : 0,
        'preferredLocations' => $preferenceValues['location'] ?? [],
        'preferredEducation' => $preferenceValues['education'] ?? [],
        'preferredMaritalStatus' => $preferenceValues['marital_status'] ?? [],
        'preferredLocationRadius' => $preferenceValues['location_radius'] ?? [],
    ];

    success_response('Admin profile fetched successfully.', ['profile' => $profile]);
}

function update_admin_profile(string $memberId): never
{
    require_admin_profile_access();
    $memberId = trim($memberId);
    if ($memberId === '' || !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $memberId)) error_response('Invalid member ID.', [], 422);
    $data = request_json();
    $pdo = db();

    $allowed = [
        'full_name' => 'fullName', 'gender' => 'gender', 'marital_status' => 'maritalStatus', 'height' => 'height',
        'place' => 'place', 'district' => 'district', 'state' => 'state', 'pincode' => 'pincode',
        'religion' => 'religion', 'sect' => 'community', 'highest_education' => 'highestEducation', 'specialization' => 'specialization',
        'job_title' => 'jobTitle', 'job_sector' => 'jobSector', 'company_name' => 'companyName', 'work_location' => 'workLocation',
        'annual_income' => 'annualIncome', 'weight' => 'weight', 'body_type' => 'bodyType', 'complexion' => 'complexion',
        'physical_status' => 'physicalStatus', 'father_name' => 'fatherName', 'mother_name' => 'motherName', 'brothers' => 'brothers',
        'sisters' => 'sisters', 'family_status' => 'familyStatus', 'home_type' => 'homeType', 'secondary_mobile' => 'secondaryMobile',
        'whatsapp_number' => 'whatsappNumber', 'email' => 'email', 'expectations' => 'expectations'
    ];

    $stmt = $pdo->prepare('SELECT p.user_id FROM profiles p INNER JOIN users u ON u.id = p.user_id WHERE u.member_id = ? AND u.role = "user" LIMIT 1');
    $stmt->execute([$memberId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) error_response('Profile not found.', [], 404);

    $sets = []; $params = [];
    foreach ($allowed as $column => $key) {
        if (array_key_exists($key, $data)) { $sets[] = $column . ' = ?'; $params[] = $data[$key]; }
    }
    if (!$sets) error_response('No editable profile fields were supplied.', [], 422);

    if (isset($data['gender']) && !in_array($data['gender'], ['male','female'], true)) error_response('Invalid gender.', [], 422);
    if (isset($data['age'])) { /* ignored: age is derived from DOB */ }

    $pdo->beginTransaction();
    try {
        $params[] = (int)$user['user_id'];
        $pdo->prepare('UPDATE profiles SET ' . implode(', ', $sets) . ' WHERE user_id = ?')->execute($params);
        $pdo->commit();
        success_response('Profile updated successfully.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_response('Unable to update profile.', [], 500);
    }
}
