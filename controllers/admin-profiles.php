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

    /*
     * Admin profile view:
     * Return ALL columns currently present in profiles, plus the complete
     * profile_preferences row and all preference_values rows.
     *
     * No conditional filtering is applied here. If a value exists in DB,
     * it is returned to the admin frontend.
     *
     * Keep the commonly-used camelCase aliases below for compatibility with
     * the existing Angular admin profile view.
     */
    $profile = $row;

    // Remove internal/join-only values from the top-level profile object.
    unset($profile['user_id']);

    // Common camelCase aliases used by the existing frontend.
    $profile['id'] = $row['member_id'];
    $profile['userId'] = (int)$row['user_id'];
    $profile['profileFor'] = $row['profile_for'] ?? '';
    $profile['fullName'] = $row['full_name'] ?? '';
    $profile['name'] = $row['full_name'] ?? '';
    $profile['maritalStatus'] = $row['marital_status'] ?? '';
    $profile['hasKids'] = $row['has_kids'] ?? '';
    $profile['numberOfKids'] = $row['number_of_kids'] !== null ? (int)$row['number_of_kids'] : null;
    $profile['kidsLivingStatus'] = $row['kids_living_status'] ?? '';
    $profile['dateOfBirth'] = $row['date_of_birth'] ?? null;
    $profile['age'] = $age;
    $profile['height'] = $row['height'] !== null ? (float)$row['height'] : null;
    $profile['collegeUniversity'] = $row['college_university'] ?? '';
    $profile['specialization'] = $row['specialization'] ?? '';
    $profile['weight'] = $row['weight'] !== null ? (float)$row['weight'] : null;
    $profile['complexion'] = $row['complexion'] ?? '';
    $profile['nakshatra'] = $row['nakshatra'] ?? '';
    $profile['rashi'] = $row['rashi'] ?? '';
    $profile['dosham'] = $row['dosham'] ?? '';

    $profile['houseName'] = $row['house_name'] ?? '';
    $profile['place'] = $row['place'] ?? '';
    $profile['district'] = $row['district'] ?? '';
    $profile['state'] = $row['state'] ?? '';
    $profile['pincode'] = $row['pincode'] ?? '';

    $profile['muslimGroup'] = $row['muslim_group'] ?? '';
    $profile['salafiGroup'] = $row['salafi_group'] ?? '';
    $profile['subCaste'] = $row['sub_caste'] ?? '';
    $profile['christianDenomination'] = $row['denomination'] ?? '';
    $profile['christianSubGroup'] = $row['christian_sub_group'] ?? '';
    $profile['parishName'] = $row['parish_name'] ?? '';

    $profile['highestEducation'] = $row['highest_education'] ?? '';
    $profile['education'] = $row['highest_education'] ?? '';
    $profile['jobTitle'] = $row['job_title'] ?? '';
    $profile['jobSector'] = $row['job_sector'] ?? '';
    $profile['companyName'] = $row['company_name'] ?? '';
    $profile['company'] = $row['company_name'] ?? '';
    $profile['workLocation'] = $row['work_location'] ?? '';
    $profile['annualIncome'] = $row['annual_income'] ?? '';
    $profile['workLocationType'] = $row['work_location_type'] ?? '';
    $profile['workState'] = $row['work_state'] ?? '';
    $profile['workDistrict'] = $row['work_district'] ?? '';
    $profile['workCountry'] = $row['work_country'] ?? '';
    $profile['workCity'] = $row['work_city'] ?? '';

    $profile['bodyType'] = $row['body_type'] ?? '';
    $profile['physicalStatus'] = $row['physical_status'] ?? '';

    $profile['fatherName'] = $row['father_name'] ?? '';
    $profile['motherName'] = $row['mother_name'] ?? '';
    $profile['fatherOccupation'] = $row['father_occupation'] ?? '';
    $profile['fatherStatus'] = $row['father_status'] ?? '';
    $profile['motherOccupation'] = $row['mother_occupation'] ?? '';
    $profile['motherStatus'] = $row['mother_status'] ?? '';
    $profile['brothers'] = $row['brothers'] !== null ? (int)$row['brothers'] : null;
    $profile['sisters'] = $row['sisters'] !== null ? (int)$row['sisters'] : null;
    $profile['marriedBrothers'] = $row['married_brothers'] !== null ? (int)$row['married_brothers'] : null;
    $profile['marriedSisters'] = $row['married_sisters'] !== null ? (int)$row['married_sisters'] : null;
    $profile['familyStatus'] = $row['family_status'] ?? '';
    $profile['homeType'] = $row['home_type'] ?? '';

    $profile['secondaryMobile'] = $row['secondary_mobile'] ?? '';
    $profile['whatsappCountryCode'] = $row['whatsapp_country_code'] ?? '+91';
    $profile['whatsappNumber'] = $row['whatsapp_number'] ?? '';
    $profile['email'] = $row['email'] ?? '';
    $profile['expectations'] = $row['expectations'] ?? '';

    $profile['accountStatus'] = $row['account_status'] ?? '';
    $profile['verificationStatus'] = $status;
    $profile['homeVerified'] = (bool)($row['home_verified'] ?? false);

    // Existing photo API expects these fields.
    $profile['photos'] = $photos;
    $profile['primaryPhoto'] = $photos[0] ?? '';

    /*
     * Keep complete DB preference row.
     * Also expose the existing frontend-friendly scalar aliases.
     */
    $profile['profilePreferences'] = $preferences;

    $profile['preferredAgeMin'] = isset($preferences['age_min']) && $preferences['age_min'] !== null
        ? (int)$preferences['age_min'] : null;
    $profile['preferredAgeMax'] = isset($preferences['age_max']) && $preferences['age_max'] !== null
        ? (int)$preferences['age_max'] : null;
    $profile['preferredHeightMin'] = isset($preferences['height_min']) && $preferences['height_min'] !== null
        ? (float)$preferences['height_min'] : null;
    $profile['preferredHeightMax'] = isset($preferences['height_max']) && $preferences['height_max'] !== null
        ? (float)$preferences['height_max'] : null;
    $profile['preferredReligion'] = $preferences['preferred_religion'] ?? '';
    $profile['acceptanceOfKids'] = $preferences['acceptance_of_kids'] ?? '';
    $profile['horoscopeRequired'] = $preferences['horoscope_required'] ?? '';

    /*
     * Return ALL preference_values, grouped by preference_type.
     * No hard-coded type filtering.
     */
    $profile['preferenceValues'] = $preferenceValues;

    // Existing frontend aliases.
    $profile['preferredMaritalStatuses'] = $preferenceValues['marital_status'] ?? [];
    $profile['preferredMaritalStatus'] = $preferenceValues['marital_status'] ?? [];
    $profile['preferredSects'] = $preferenceValues['sect'] ?? [];
    $profile['preferredSunniGroups'] = $preferenceValues['sunni_group'] ?? [];
    $profile['preferredSalafiGroups'] = $preferenceValues['salafi_group'] ?? [];
    $profile['preferredCastes'] = $preferenceValues['caste'] ?? [];
    $profile['preferredSubCastes'] = $preferenceValues['sub_caste'] ?? [];
    $profile['preferredEducation'] = $preferenceValues['education'] ?? [];
    $profile['preferredEducationSpecific'] = $preferenceValues['education_specific'] ?? [];
    $profile['preferredCareerSectors'] = $preferenceValues['career_sector'] ?? [];
    $profile['preferredLocations'] = $preferenceValues['location'] ?? [];
    $profile['preferredLocationRadius'] = $preferenceValues['location_radius'] ?? [];

    /*
     * Additional-preference aliases.
     * "any" is now stored as a real preference_values row.
     * The fallback keeps older profiles (created before this change) working.
     */
    $profile['preferredFamilyStatus'] = array_key_exists('family_status', $preferenceValues)
        && count($preferenceValues['family_status']) > 0
        ? $preferenceValues['family_status'] : ['Any'];

    $profile['preferredPhysicalStatus'] = array_key_exists('physical_status', $preferenceValues)
        && count($preferenceValues['physical_status']) > 0
        ? $preferenceValues['physical_status'] : ['Any'];

    $profile['preferredIncome'] = array_key_exists('income', $preferenceValues)
        && count($preferenceValues['income']) > 0
        ? $preferenceValues['income'] : ['Any'];

    $profile['preferredComplexion'] = array_key_exists('complexion', $preferenceValues)
        && count($preferenceValues['complexion']) > 0
        ? $preferenceValues['complexion'] : ['Any'];

    $profile['preferredStar'] = array_key_exists('star', $preferenceValues)
        && count($preferenceValues['star']) > 0
        ? $preferenceValues['star']
        : (array_key_exists('nakshatra', $preferenceValues) && count($preferenceValues['nakshatra']) > 0
            ? $preferenceValues['nakshatra']
            : ['Any']);

    // Keep plan/status fields available as convenient aliases.
    $profile['plan'] = $row['plan'] ?? 'Free';

    success_response(
        'Admin profile fetched successfully.',
        ['profile' => $profile]
    );
}


/*
 * --------------------------------------------------------------------------
 * Admin profile-edit validation helpers
 * --------------------------------------------------------------------------
 *
 * IMPORTANT:
 * These values mirror the existing registration/profile.php business rules.
 * This is validation only; the existing admin update flow and field map stay
 * unchanged.
 */

const ADMIN_PREF_MARITAL_STATUS_OPTIONS = [
    'never_married', 'divorced', 'nikah_divorce',
    'widowed', 'separated', 'awaiting_divorce', 'Any'
];

const ADMIN_PREF_MUSLIM_SECT_OPTIONS = [
    'Sunni', 'Salafi', 'Jamat Islami', 'Hanafi', 'Shafi', 'Any'
];

const ADMIN_PREF_SUNNI_GROUP_OPTIONS = [
    'AP-Sunni', 'EK-Sunni', 'Sunni', 'Any'
];

const ADMIN_PREF_SALAFI_GROUP_OPTIONS = [
    'KNM (Mainstream)', 'KNM Markazu Dawa', 'Wisdom',
    'Salafi Independent', 'Other Salafi / Mujahid', 'Any'
];

const ADMIN_PREF_HINDU_CASTE_OPTIONS = [
    'Thiyya / Ezhava', 'Namboothiri', 'Nair', 'Viswakarma', 'SC', 'ST', 'Any'
];

const ADMIN_PREF_CHRISTIAN_DENOMINATION_OPTIONS = [
    'Catholic', 'Orthodox', 'Protestant', 'Pentecostal', 'Any'
];

const ADMIN_PREF_HINDU_SUBCASTE_OPTIONS = [
    'Menon', 'Pillai', 'Panikkar', 'Nambiar', 'Kurupp', 'Vilakithala Nair',
    'Veluthedath Nair', 'Asari (Carpenters)', 'Kollan (Blacksmiths)',
    'Moosari (Bell metal and brass smiths)', 'Thattan (Goldsmiths)',
    'Kallassary (Stonemasons)', 'Pulayan / Pulayar', 'Cheruman', 'Kanakkan',
    'Kuravan', 'Parayan', 'Others', 'Paniyan', 'Irular', 'Kurichiar',
    'Kanikkaran', 'Other'
];

const ADMIN_PREF_CHRISTIAN_SUBGROUP_OPTIONS = [
    'Syro-Malabar Catholic', 'Latin Catholic', 'Syro-Malankara Catholic',
    'Malankara Orthodox Syrian Church', 'Jacobite Syrian Christian Church',
    'Church of South India (CSI)', 'Mar Thoma Syrian Church',
    'St. Thomas Evangelical Church', 'Lutheran',
    'Indian Pentecostal Church of God (IPC)', 'Assemblies of God (AG)',
    'Church of God (Full Gospel) in India', 'The Pentecostal Mission (TPM)',
    'Sharon Fellowship Church', 'New India Church of God', 'Other Pentecostal',
    'Chaldean Syrian Church', 'Malabar Independent Syrian Church',
    'Seventh-day Adventist', 'Salvation Army', 'Brethren',
    "Jehovah's Witnesses", 'Non-denominational', 'Other', 'Any'
];

const ADMIN_PREF_EDUCATION_OPTIONS = [
    'Any',
    'PhD / Doctorate', "Master's Degree", 'Professional Degree',
    "Bachelor's Degree", 'Diploma', 'ITI / Technical Certificate',
    'Plus Two / Higher Secondary', 'Religious / Islamic Education',
    'Others / Below 10th'
];

const ADMIN_PREF_EDUCATION_SPECIFIC_OPTIONS = [
    'MBBS', 'MD / MS / DNB', 'BDS / MDS', 'BAMS / BHMS / BUMS',
    'BE / B.Tech', 'ME / M.Tech', 'B.Pharm / Pharm.D', 'BPT / MPT',
    'CA / CMA / CS / ACCA', 'LLB / LLM', 'Other Professional'
];

const ADMIN_PREF_CAREER_SECTOR_OPTIONS = [
    'Business / Self Employed', 'Private', 'Government', 'Freelance','Student', 'Any'
];

const ADMIN_PREF_LOCATION_OPTIONS = [
    'All Kerala', 'Alappuzha', 'Ernakulam', 'Idukki', 'Kannur', 'Kasaragod',
    'Kollam', 'Kottayam', 'Kozhikode', 'Malappuram', 'Palakkad',
    'Pathanamthitta', 'Thiruvananthapuram', 'Thrissur', 'Wayanad'
];

const ADMIN_PREF_FAMILY_STATUS_OPTIONS = [
    'Any', 'Lower Middle Class', 'Middle Class', 'Upper Middle Class', 'Affluent'
];

const ADMIN_PREF_PHYSICAL_STATUS_OPTIONS = [
    'Any', 'Normal', 'Physically Challenged', 'Other'
];

const ADMIN_PREF_LOCATION_RADIUS_OPTIONS = [
    'Any', 'Within 10 km', 'Within 25 km', 'Within 50 km',
    'Within 100 km', 'Anywhere in Kerala'
];

const ADMIN_PREF_INCOME_OPTIONS = [
    'Any', 'Below ₹2 Lakh', '₹2 - ₹5 Lakh', '₹5 - ₹10 Lakh',
    '₹10 - ₹15 Lakh', '₹15 - ₹25 Lakh', 'Above ₹25 Lakh'
];

const ADMIN_PREF_COMPLEXION_OPTIONS = [
    'Any', 'Very Fair', 'Fair', 'Wheatish', 'Medium', 'Dusky', 'Dark'
];

const ADMIN_PREF_STAR_OPTIONS = [
    'Any',
    'Ashwini (Aswathi)', 'Bharani', 'Krittika (Karthika)', 'Rohini',
    'Mrigashirsha (Makayiram)', 'Ardra (Thiruvathira)',
    'Punarvasu (Punartham)', 'Pushya (Pooyam)', 'Ashlesha (Ayilyam)',
    'Magha (Makam)', 'Purva Phalguni (Pooram)', 'Uttara Phalguni (Uthram)',
    'Hasta (Atham)', 'Chitra', 'Swati (Chothi)', 'Vishakha (Vishakam)',
    'Anuradha (Anizham)', 'Jyeshtha (Thriketta)', 'Mula (Moolam)',
    'Purva Ashadha (Pooradam)', 'Uttara Ashadha (Uthradam)',
    'Shravana (Thiruvonam)', 'Dhanishtha (Avittam)',
    'Shatabhisha (Chathayam)', 'Purva Bhadrapada (Pooruruttathi)',
    'Uttara Bhadrapada (Uthrattathi)', 'Revati'
];

function admin_validate_choice($value, array $options, string $label): void
{
    if (!is_string($value) || !in_array($value, $options, true)) {
        error_response("Invalid $label selected.", [], 422);
    }
}

function admin_validate_choice_array(
    $values,
    array $options,
    string $label,
    bool $allowAny = false
): array {
    if (!is_array($values)) {
        error_response("Invalid $label format.", [], 422);
    }

    $values = array_values(array_unique($values));

    if (count($values) > 30) {
        error_response("Too many $label values.", [], 422);
    }

    foreach ($values as $value) {
        if (!is_string($value) || !in_array($value, $options, true)) {
            error_response("Invalid $label selected.", [], 422);
        }
    }

    $any = $allowAny ? 'Any' : 'any';
    if (in_array($any, $values, true) && count($values) > 1) {
        error_response(
            '"' . $any . '" cannot be combined with other ' . $label . ' values.',
            [],
            422
        );
    }

    return $values;
}

function admin_validate_dob(string $dateOfBirth, string $gender): void
{
    $dob = DateTime::createFromFormat('!Y-m-d', $dateOfBirth);

    if (!$dob || $dob->format('Y-m-d') !== $dateOfBirth) {
        error_response('Invalid date of birth.', [], 422);
    }

    $today = new DateTime('today');

    if ($dob > $today) {
        error_response('Date of birth cannot be in the future.', [], 422);
    }

    $age = $dob->diff($today)->y;

    if ($age > 60) {
        error_response('Please enter a valid date of birth.', [], 422);
    }

    $minimumAge = $gender === 'male' ? 21 : 18;

    if ($age < $minimumAge) {
        error_response(
            $gender === 'male'
                ? 'A male profile must be at least 21 years old.'
                : 'A female profile must be at least 18 years old.',
            [],
            422
        );
    }
}

function admin_normalize_preference_array($value, string $label): array
{
    if (!is_array($value)) {
        error_response("Invalid $label format.", [], 422);
    }

    return array_values(array_unique($value));
}

function admin_validate_preference_payload(array &$data, array $existingProfile): void
{
    /*
     * Basic/profile validation.
     * Partial updates are supported: missing fields use the existing DB value.
     */
    $gender = array_key_exists('gender', $data)
        ? (string)$data['gender']
        : (string)($existingProfile['gender'] ?? '');

    $profileFor = array_key_exists('profileFor', $data)
        ? (string)$data['profileFor']
        : (string)($existingProfile['profile_for'] ?? '');

    $maritalStatus = array_key_exists('maritalStatus', $data)
        ? (string)$data['maritalStatus']
        : (string)($existingProfile['marital_status'] ?? '');

    if ($gender !== '') {
        admin_validate_choice($gender, ['male', 'female'], 'gender');
    }

    if ($profileFor !== '') {
        admin_validate_choice(
            $profileFor,
            ['self', 'son', 'daughter', 'brother', 'sister', 'relative', 'friend'],
            'profile-for option'
        );

        // Keep the same registration business rule:
        // sister/daughter => female, brother/son => male.
        if (in_array($profileFor, ['sister', 'daughter'], true) && $gender !== 'female') {
            error_response('Female gender is required for the selected profile-for option.', [], 422);
        }
        if (in_array($profileFor, ['brother', 'son'], true) && $gender !== 'male') {
            error_response('Male gender is required for the selected profile-for option.', [], 422);
        }
    }

    if ($maritalStatus !== '') {
        admin_validate_choice(
            $maritalStatus,
            ADMIN_PREF_MARITAL_STATUS_OPTIONS,
            'marital status'
        );
    }

    if (array_key_exists('fullName', $data)) {
        if (!is_string($data['fullName'])) {
            error_response('Invalid name.', [], 422);
        }
        $name = trim($data['fullName']);
        $length = mb_strlen($name);
        if ($length < 2 || $length > 100) {
            error_response('Name must be between 2 and 100 characters.', [], 422);
        }
        $data['fullName'] = $name;
    }

    if (array_key_exists('dateOfBirth', $data)
        && $data['dateOfBirth'] !== null
        && $data['dateOfBirth'] !== ''
    ) {
        admin_validate_dob((string)$data['dateOfBirth'], $gender);
    }

    if (array_key_exists('height', $data)
        && $data['height'] !== null
        && $data['height'] !== ''
    ) {
        if (!is_numeric($data['height']) || (float)$data['height'] < 48 || (float)$data['height'] > 87) {
            error_response('Height must be between 48 and 87 inches.', [], 422);
        }
    }

    /*
     * Highest Education -> Specialization dependency.
     * Use the effective value so a partial admin update cannot bypass this rule.
     */
    $highestEducation = array_key_exists('highestEducation', $data)
        ? trim((string)$data['highestEducation'])
        : trim((string)($existingProfile['highest_education'] ?? ''));

    $specialization = array_key_exists('specialization', $data)
        ? trim((string)$data['specialization'])
        : trim((string)($existingProfile['specialization'] ?? ''));

    if ($highestEducation !== '' && $specialization === '') {
        error_response('Please select a specialization for the selected highest education.', [], 422);
    }

    if (array_key_exists('highestEducation', $data)) {
        $data['highestEducation'] = $highestEducation;
    }
    if (array_key_exists('specialization', $data)) {
        $data['specialization'] = $specialization;
    }

    /*
     * Marital-status / kids dependency.
     * Same registration rule: kids are applicable only to
     * divorced, widowed, separated and awaiting_divorce.
     */
    $kidsRequiredStatuses = [
        'divorced', 'widowed', 'separated', 'awaiting_divorce'
    ];

    if (in_array($maritalStatus, $kidsRequiredStatuses, true)) {
        if (array_key_exists('hasKids', $data)) {
            admin_validate_choice($data['hasKids'], ['yes', 'no'], 'kids option');

            if ($data['hasKids'] === 'yes') {
                if (!array_key_exists('numberOfKids', $data)
                    || $data['numberOfKids'] === null
                    || $data['numberOfKids'] === ''
                ) {
                    error_response('Please select the number of kids.', [], 422);
                }

                $kids = $data['numberOfKids'] === '3_plus'
                    ? 3
                    : (int)$data['numberOfKids'];

                if (!in_array($kids, [1, 2, 3], true)) {
                    error_response('Invalid number of kids selected.', [], 422);
                }

                $data['numberOfKids'] = $kids;

                if (empty($data['kidsLivingStatus'])) {
                    error_response('Please select kids living status.', [], 422);
                }

                admin_validate_choice(
                    $data['kidsLivingStatus'],
                    ['with_me', 'not_with_me'],
                    'kids living status'
                );
            } else {
                $data['numberOfKids'] = null;
                $data['kidsLivingStatus'] = null;
            }
        }
    } else {
        /*
         * Not applicable for never_married / nikah_divorce.
         * Do not keep stale kid data in DB.
         */
        if (array_key_exists('maritalStatus', $data)
            || array_key_exists('hasKids', $data)
            || array_key_exists('numberOfKids', $data)
            || array_key_exists('kidsLivingStatus', $data)
        ) {
            $data['hasKids'] = null;
            $data['numberOfKids'] = null;
            $data['kidsLivingStatus'] = null;
        }
    }

    // Required location fields for admin profile save.
    foreach (['houseName', 'place', 'pincode'] as $field) {
        if (!array_key_exists($field, $data) || trim((string)$data[$field]) === '') {
            error_response(
                $field === 'houseName' ? 'House Name is required.' : ($field === 'place' ? 'Place is required.' : 'Pincode is required.'),
                [], 422
            );
        }
    }
    $data['houseName'] = trim((string)$data['houseName']);
    $data['place'] = trim((string)$data['place']);
    $data['pincode'] = trim((string)$data['pincode']);
    if (!preg_match('/^[0-9]{6}$/', $data['pincode'])) {
        error_response('Please enter a valid 6 digit pincode.', [], 422);
    }

    if (array_key_exists('email', $data) && $data['email'] !== '' && $data['email'] !== null) {
        if (!is_string($data['email']) || !filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            error_response('Please enter a valid email address.', [], 422);
        }
        $data['email'] = trim($data['email']);
    }

    foreach (['secondaryMobile', 'whatsappNumber'] as $phoneField) {
        if (array_key_exists($phoneField, $data) && $data[$phoneField] !== '' && $data[$phoneField] !== null) {
            if (!is_string($data[$phoneField]) || !preg_match('/^[0-9]{7,15}$/', trim($data[$phoneField]))) {
                error_response("Invalid $phoneField.", [], 422);
            }
            $data[$phoneField] = trim($data[$phoneField]);
        }
    }

    /*
     * Religion-specific profile values.
     * Existing DB value is used when religion is not part of a partial update.
     */
    $religion = array_key_exists('religion', $data)
        ? (string)$data['religion']
        : (string)($existingProfile['religion'] ?? '');

    if ($religion !== '') {
        admin_validate_choice(
            $religion,
            ['Muslim', 'Hindu', 'Christian'],
            'religion'
        );
    }

    /*
     * A selected religion must have its corresponding required sub-option.
     * This mirrors the registration form dependency.
     */
    if ($religion === 'Muslim') {
        $sect = array_key_exists('sect', $data)
            ? trim((string)$data['sect'])
            : trim((string)($existingProfile['sect'] ?? ''));

        if ($sect === '') {
            error_response('Please select a Sect for Muslim.', [], 422);
        }

        if (array_key_exists('sect', $data)) {
            $data['sect'] = $sect;
        }
    }

    if ($religion === 'Hindu') {
        $caste = array_key_exists('caste', $data)
            ? trim((string)$data['caste'])
            : trim((string)($existingProfile['caste'] ?? ''));

        if ($caste === '') {
            error_response('Please select a Caste for Hindu.', [], 422);
        }

        if (array_key_exists('caste', $data)) {
            $data['caste'] = $caste;
        }
    }

    if ($religion === 'Christian') {
        $denomination = array_key_exists('christianDenomination', $data)
            ? trim((string)$data['christianDenomination'])
            : trim((string)($existingProfile['denomination'] ?? ''));

        if ($denomination === '') {
            error_response('Please select a Denomination for Christian.', [], 422);
        }

        if (array_key_exists('christianDenomination', $data)) {
            $data['christianDenomination'] = $denomination;
        }
    }

    if ($religion !== 'Muslim') {
        if (array_key_exists('sect', $data)
            || array_key_exists('muslimGroup', $data)
            || array_key_exists('salafiGroup', $data)
        ) {
            $data['sect'] = null;
            $data['muslimGroup'] = null;
            $data['salafiGroup'] = null;
        }
    } else {
        if (array_key_exists('sect', $data) && $data['sect'] !== '' && $data['sect'] !== null) {
            admin_validate_choice(
                $data['sect'],
                ['Sunni', 'Salafi', 'Jamat Islami', 'Hanafi', 'Shafi', 'Other'],
                'sect'
            );

            if ($data['sect'] !== 'Sunni') {
                $data['muslimGroup'] = null;
            }
            if ($data['sect'] !== 'Salafi') {
                $data['salafiGroup'] = null;
            }
        }
    }

    if ($religion !== 'Hindu') {
        foreach (['caste', 'subCaste', 'nakshatra', 'rashi', 'dosham'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = null;
            }
        }
    } else {
        if (array_key_exists('caste', $data) && $data['caste'] !== '' && $data['caste'] !== null) {
            admin_validate_choice(
                $data['caste'],
                ['Thiyya / Ezhava', 'Namboothiri', 'Nair', 'Viswakarma', 'SC', 'ST', 'Other'],
                'caste'
            );
        }

        if (array_key_exists('nakshatra', $data) && $data['nakshatra'] !== '' && $data['nakshatra'] !== null) {
            admin_validate_choice($data['nakshatra'], ADMIN_PREF_STAR_OPTIONS, 'nakshatra');
        }
    }

    if ($religion !== 'Christian') {
        foreach (['christianDenomination', 'christianSubGroup', 'parishName'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = null;
            }
        }
    } else {
        if (array_key_exists('christianDenomination', $data)
            && $data['christianDenomination'] !== ''
            && $data['christianDenomination'] !== null
        ) {
            admin_validate_choice(
                $data['christianDenomination'],
                ['Catholic', 'Orthodox', 'Protestant', 'Pentecostal', 'Other'],
                'denomination'
            );
        }

        /*
         * Christian Church Group is dependent on Denomination.
         * When Church Group is Other, parishName is used as the
         * "Specify" value, matching the existing DB field.
         */
        $churchGroup = array_key_exists('christianSubGroup', $data)
            ? trim((string)$data['christianSubGroup'])
            : trim((string)($existingProfile['christian_sub_group'] ?? ''));

        if ($churchGroup === '') {
            error_response('Please select a Church Group for Christian.', [], 422);
        }

        if (array_key_exists('christianSubGroup', $data)) {
            $data['christianSubGroup'] = $churchGroup;
        }

        if ($churchGroup === 'Other') {
            $parishName = array_key_exists('parishName', $data)
                ? trim((string)$data['parishName'])
                : trim((string)($existingProfile['parish_name'] ?? ''));

            if ($parishName === '') {
                error_response('Please specify the Church Group when Other is selected.', [], 422);
            }

            if (array_key_exists('parishName', $data)) {
                $data['parishName'] = $parishName;
            }
        }
    }

    /*
     * Scalar partner preferences. Required for admin profile save.
     */
    foreach (['preferredAgeMin','preferredAgeMax','preferredHeightMin','preferredHeightMax'] as $field) {
        if (!array_key_exists($field, $data) || trim((string)$data[$field]) === '') {
            error_response(ucwords(preg_replace('/([A-Z])/', ' $1', $field)) . ' is required.', [], 422);
        }
    }
    foreach (['preferredAgeMin', 'preferredAgeMax'] as $key) {
        if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
            if (!is_numeric($data[$key]) || (int)$data[$key] < 18 || (int)$data[$key] > 60) {
                error_response("Invalid $key.", [], 422);
            }
        }
    }

    if (array_key_exists('preferredAgeMin', $data)
        && array_key_exists('preferredAgeMax', $data)
        && $data['preferredAgeMin'] !== null && $data['preferredAgeMin'] !== ''
        && $data['preferredAgeMax'] !== null && $data['preferredAgeMax'] !== ''
        && (int)$data['preferredAgeMin'] > (int)$data['preferredAgeMax']
    ) {
        error_response('Minimum preferred age cannot be greater than maximum.', [], 422);
    }

    foreach (['preferredHeightMin', 'preferredHeightMax'] as $key) {
        if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
            if (!is_numeric($data[$key]) || (float)$data[$key] < 48 || (float)$data[$key] > 87) {
                error_response("Invalid $key.", [], 422);
            }
        }
    }

    if (array_key_exists('preferredHeightMin', $data)
        && array_key_exists('preferredHeightMax', $data)
        && $data['preferredHeightMin'] !== null && $data['preferredHeightMin'] !== ''
        && $data['preferredHeightMax'] !== null && $data['preferredHeightMax'] !== ''
        && (float)$data['preferredHeightMin'] > (float)$data['preferredHeightMax']
    ) {
        error_response('Minimum preferred height cannot be greater than maximum.', [], 422);
    }

    if (!array_key_exists('preferredMaritalStatuses', $data) || !is_array($data['preferredMaritalStatuses']) || count($data['preferredMaritalStatuses']) === 0) {
        error_response('Preferred Marital Status is required.', [], 422);
    }

    /*
 * Preferred marital status -> Acceptance of Kids dependency.
 *
 * Acceptance of Kids is required when:
 * - Any is selected
 * - Divorced is selected
 * - Widowed is selected
 * - Separated is selected
 * - Awaiting Divorce is selected
 */

$kidsAcceptanceRequiredStatuses = [
    'Any',
    'divorced',
    'widowed',
    'separated',
    'awaiting_divorce'
];

$acceptanceOfKidsRequired = false;

foreach ($data['preferredMaritalStatuses'] as $status) {
    if (in_array($status, $kidsAcceptanceRequiredStatuses, true)) {
        $acceptanceOfKidsRequired = true;
        break;
    }
}

if ($acceptanceOfKidsRequired) {

    if (
        !array_key_exists('acceptanceOfKids', $data) ||
        $data['acceptanceOfKids'] === '' ||
        $data['acceptanceOfKids'] === null
    ) {
        error_response(
            'Please select acceptance of kids.',
            [],
            422
        );
    }

    admin_validate_choice(
        $data['acceptanceOfKids'],
        ['yes', 'no', 'yes_not_living', 'yes_living'],
        'kids acceptance'
    );

} else {

    if (
        array_key_exists('acceptanceOfKids', $data) &&
        $data['acceptanceOfKids'] !== '' &&
        $data['acceptanceOfKids'] !== null
    ) {
        error_response(
            'Acceptance of kids is not applicable for the selected preferred marital statuses.',
            [],
            422
        );
    }
}

    $preferredReligion = array_key_exists('preferredReligion', $data)
        ? trim((string)$data['preferredReligion'])
        : trim((string)($existingProfile['preferred_religion'] ?? ''));

    if ($preferredReligion !== '') {
        admin_validate_choice(
            $preferredReligion,
            ['Muslim', 'Hindu', 'Christian'],
            'preferred religion'
        );
    }

    if ($preferredReligion === 'Muslim' && (!array_key_exists('preferredSects', $data) || !is_array($data['preferredSects']) || count($data['preferredSects']) === 0)) {
        error_response('Preferred Sect is required for Muslim.', [], 422);
    }
    if ($preferredReligion === 'Hindu' && (!array_key_exists('preferredCastes', $data) || !is_array($data['preferredCastes']) || count($data['preferredCastes']) === 0)) {
        error_response('Preferred Caste is required for Hindu.', [], 422);
    }
    if ($preferredReligion === 'Christian' && (!array_key_exists('preferredSects', $data) || !is_array($data['preferredSects']) || count($data['preferredSects']) === 0)) {
        error_response('Preferred Denomination is required for Christian.', [], 422);
    }

    if (array_key_exists('preferredReligion', $data)
        && $data['preferredReligion'] !== ''
        && $data['preferredReligion'] !== null
    ) {
        admin_validate_choice(
            $data['preferredReligion'],
            ['Muslim', 'Hindu', 'Christian'],
            'preferred religion'
        );
    }

    

    /*
     * Array partner preferences.
     * "Any" is a wildcard value in these fields and is mutually exclusive.
     */
    $arrayRules = [
        'preferredMaritalStatuses' => [ADMIN_PREF_MARITAL_STATUS_OPTIONS, true],
        'preferredSects' => [
            array_merge(ADMIN_PREF_MUSLIM_SECT_OPTIONS, ADMIN_PREF_CHRISTIAN_DENOMINATION_OPTIONS),
            true
        ],
        'preferredSunniGroups' => [ADMIN_PREF_SUNNI_GROUP_OPTIONS, true],
        'preferredSalafiGroups' => [ADMIN_PREF_SALAFI_GROUP_OPTIONS, true],
        'preferredCastes' => [ADMIN_PREF_HINDU_CASTE_OPTIONS, true],
        'preferredSubCastes' => [
            array_merge(
                ['Any'],
                ADMIN_PREF_HINDU_SUBCASTE_OPTIONS,
                ADMIN_PREF_CHRISTIAN_SUBGROUP_OPTIONS
            ),
            true
        ],
        'preferredEducation' => [ADMIN_PREF_EDUCATION_OPTIONS, true],
        'preferredEducationSpecific' => [ADMIN_PREF_EDUCATION_SPECIFIC_OPTIONS, false],
        'preferredCareerSectors' => [ADMIN_PREF_CAREER_SECTOR_OPTIONS, true],
        'preferredLocations' => [ADMIN_PREF_LOCATION_OPTIONS, false],
        'preferredFamilyStatus' => [ADMIN_PREF_FAMILY_STATUS_OPTIONS, true],
        'preferredPhysicalStatus' => [ADMIN_PREF_PHYSICAL_STATUS_OPTIONS, true],
        'preferredLocationRadius' => [ADMIN_PREF_LOCATION_RADIUS_OPTIONS, true],
        'preferredIncome' => [ADMIN_PREF_INCOME_OPTIONS, true],
        'preferredComplexion' => [ADMIN_PREF_COMPLEXION_OPTIONS, true]
    ];

    foreach ($arrayRules as $field => [$options, $allowAny]) {
        if (array_key_exists($field, $data)) {
            $data[$field] = admin_validate_choice_array(
                $data[$field],
                $options,
                $field,
                $allowAny
            );
        }
    }

    if (array_key_exists('preferredLocations', $data)
        && (!is_array($data['preferredLocations']) || count($data['preferredLocations']) === 0)
    ) {
        error_response('Preferred location is required.', [], 422);
    }

    /*
     * Parent -> child cleanup, exactly as registration:
     * multiple/Any parent means child selection is not applicable.
     */
    if (
        $religion === 'Muslim' &&
        array_key_exists('preferredSects', $data) &&
        (
            count($data['preferredSects']) > 1 ||
            in_array('Any', $data['preferredSects'], true)
        )
    ) {
        $data['preferredSunniGroups'] = [];
        $data['preferredSalafiGroups'] = [];
    }

    if (
        $religion === 'Hindu' &&
        array_key_exists('preferredCastes', $data) &&
        (
            count($data['preferredCastes']) > 1 ||
            in_array('Any', $data['preferredCastes'], true)
        )
    ) {
        $data['preferredSubCastes'] = [];
    }

    if (
        $religion === 'Christian' &&
        array_key_exists('preferredSects', $data) &&
        (
            count($data['preferredSects']) > 1 ||
            in_array('Any', $data['preferredSects'], true)
        )
    ) {
        $data['preferredSubCastes'] = [];
    }

    /*
     * Additional Preferences.
     * "any" is a real stored preference value.
     * This keeps the Any selection persistent across save/reload.
     */
    if (array_key_exists('preferredStar', $data)) {
        $data['preferredStar'] = admin_validate_choice_array(
            $data['preferredStar'],
            ADMIN_PREF_STAR_OPTIONS,
            'preferred star',
            true
        );
    }

    $horoscopeRequired = array_key_exists('horoscopeRequired', $data)
        ? (string)$data['horoscopeRequired']
        : '';

    if ($horoscopeRequired !== '' && !in_array($horoscopeRequired, ['yes', 'no'], true)) {
        error_response('Invalid horoscope preference.', [], 422);
    }

    if ($religion !== 'Hindu') {
        $data['horoscopeRequired'] = '';
        $data['preferredStar'] = [];
    } elseif ($horoscopeRequired !== 'yes') {
        /*
         * Same registration/additional-preference behavior:
         * stars are stored only when horoscopeRequired is yes.
         */
        $data['preferredStar'] = [];
    }
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
        'SELECT p.*, u.id AS user_id
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

    /*
     * Validate against the same business rules used by registration.
     * This runs before the transaction/update, so invalid admin input cannot
     * partially change the profile.
     */
    admin_validate_preference_payload($data, $user);

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

        'college_university' => 'collegeUniversity',

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
        'work_location_type' => 'workLocationType',
        'work_state' => 'workState',
        'work_district' => 'workDistrict',
        'work_country' => 'workCountry',
        'work_city' => 'workCity',
        'annual_income' => 'annualIncome',

        'weight' => 'weight',
        'body_type' => 'bodyType',
        'complexion' => 'complexion',
        'physical_status' => 'physicalStatus',

        'father_name' => 'fatherName',
        'father_occupation' => 'fatherOccupation',
        'father_status' => 'fatherStatus',
        'mother_name' => 'motherName',
        'mother_occupation' => 'motherOccupation',
        'mother_status' => 'motherStatus',
        'brothers' => 'brothers',
        'sisters' => 'sisters',
        'married_brothers' => 'marriedBrothers',
        'married_sisters' => 'marriedSisters',
        'family_status' => 'familyStatus',
        'home_type' => 'homeType',

        'secondary_mobile' => 'secondaryMobile',
        'whatsapp_country_code' => 'whatsappCountryCode',
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

        if (in_array($column, ['brothers', 'sisters', 'married_brothers', 'married_sisters', 'number_of_kids'], true)) {
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
        array_key_exists('preferredCareerSectors', $data) ||
        array_key_exists('preferredFamilyStatus', $data) ||
        array_key_exists('preferredPhysicalStatus', $data) ||
        array_key_exists('preferredLocationRadius', $data) ||
        array_key_exists('preferredIncome', $data) ||
        array_key_exists('preferredComplexion', $data) ||
        array_key_exists('preferredStar', $data) ||
        array_key_exists('horoscopeRequired', $data);

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
            array_key_exists('acceptanceOfKids', $data) ||
            array_key_exists('horoscopeRequired', $data);

        if ($preferenceFieldsSupplied) {
            $prefStmt = $pdo->prepare(
                'INSERT INTO profile_preferences
                    (user_id, age_min, age_max, height_min, height_max,
                     preferred_religion, acceptance_of_kids, horoscope_required)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    age_min = VALUES(age_min),
                    age_max = VALUES(age_max),
                    height_min = VALUES(height_min),
                    height_max = VALUES(height_max),
                    preferred_religion = VALUES(preferred_religion),
                    acceptance_of_kids = VALUES(acceptance_of_kids),
                     horoscope_required = VALUES(horoscope_required)'
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
                $data['acceptanceOfKids'] ?? null,
                $data['horoscopeRequired'] ?? null
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
            'location' => 'preferredLocations',
            'location_radius' => 'preferredLocationRadius',
            'family_status' => 'preferredFamilyStatus',
            'physical_status' => 'preferredPhysicalStatus',
            'income' => 'preferredIncome',
            'complexion' => 'preferredComplexion',
            'star' => 'preferredStar'
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

            /*
             * All preference fields are arrays in the admin editor.
             * Empty arrays intentionally mean "clear this preference group".
             */
            $values = admin_normalize_preference_array($data[$key], $key);

            /*
             * "Any" is a real stored wildcard value for additional
             * preferences. Normalize legacy lowercase "any" to the
             * canonical uppercase value and keep it mutually exclusive.
             */
            if (in_array('any', $values, true)) {
                $values = array_map(
                    static fn($value) => $value === 'any' ? 'Any' : $value,
                    $values
                );
            }

            if (in_array('Any', $values, true)) {
                if (count($values) > 1) {
                    error_response('Any cannot be combined with other preference values.', ['field' => $key], 422);
                }

                $values = ['Any'];
            }

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
