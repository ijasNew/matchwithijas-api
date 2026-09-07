<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/photo-security.php';

function require_admin_find_match_access(): array
{
    $admin = current_user(true);

    if (($admin['role'] ?? '') !== 'admin') {
        error_response('Admin access required.', [], 403);
    }

    $stmt = db()->prepare(
        'SELECT admin_role, status AS admin_status
         FROM admin_users
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([(int)$admin['id']]);
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$access) error_response('Admin access not found.', [], 403);
    if (($admin['account_status'] ?? '') !== 'active') error_response('Admin account is not active.', [], 403);
    if (($access['admin_status'] ?? '') !== 'active') error_response('Admin access is not active.', [], 403);

    return ['admin' => $admin, 'access' => $access];
}

function admin_find_match_profile(PDO $pdo, string $memberId): array
{
    $stmt = $pdo->prepare(
        'SELECT
            u.id AS user_id,
            u.member_id,
            u.phone,
            u.account_status,
            p.*,
            (
                SELECT pp.file_path
                FROM profile_photos pp
                WHERE pp.user_id = p.user_id
                  AND pp.status = "active"
                  AND pp.is_primary = 1
                ORDER BY pp.id ASC
                LIMIT 1
            ) AS photo_path
         FROM users u
         INNER JOIN profiles p ON p.user_id = u.id
         WHERE u.member_id = ?
           AND u.role = "user"
         LIMIT 1'
    );
    $stmt->execute([$memberId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) error_response('No profile found with this Member ID.', [], 404);
    if ((int)$profile['registration_completed'] !== 1) error_response('This profile registration is not completed.', [], 422);

    return $profile;
}

function admin_find_match_preferences(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT age_min, age_max, height_min, height_max, preferred_religion,
                acceptance_of_kids, horoscope_required
         FROM profile_preferences
         WHERE user_id = ? LIMIT 1'
    );
    $stmt->execute([$userId]);
    $base = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $values = [];
    $stmt = $pdo->prepare(
        'SELECT preference_type, value
         FROM preference_values
         WHERE user_id = ?
         ORDER BY id ASC'
    );
    $stmt->execute([$userId]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = (string)$row['preference_type'];
        $value = trim((string)$row['value']);
        if ($value === '') continue;
        $values[$type][] = $value;
    }

    return [
        'ageMin' => isset($base['age_min']) && $base['age_min'] !== '' ? (int)$base['age_min'] : null,
        'ageMax' => isset($base['age_max']) && $base['age_max'] !== '' ? (int)$base['age_max'] : null,
        'heightMin' => isset($base['height_min']) && $base['height_min'] !== '' ? (float)$base['height_min'] : null,
        'heightMax' => isset($base['height_max']) && $base['height_max'] !== '' ? (float)$base['height_max'] : null,
        'preferredReligion' => (string)($base['preferred_religion'] ?? ''),
        'acceptanceOfKids' => (string)($base['acceptance_of_kids'] ?? ''),
        'horoscopeRequired' => (string)($base['horoscope_required'] ?? ''),
        'values' => $values,
    ];
}

function admin_find_match_age(?string $dob): ?int
{
    if (!$dob) return null;
    try {
        return (new DateTime($dob))->diff(new DateTime('today'))->y;
    } catch (Throwable) {
        return null;
    }
}

function admin_find_match_normalize(string $value): string
{
    return strtolower(trim($value));
}

function admin_find_match_value(array $preferenceValues, string $type, ?string $candidateValue): bool
{
    if (!$candidateValue) return true;
    $wanted = $preferenceValues[$type] ?? [];
    if (!$wanted) return true;

    $candidate = admin_find_match_normalize($candidateValue);
    foreach ($wanted as $value) {
        if ($candidate === admin_find_match_normalize((string)$value)) return true;
    }
    return false;
}

function admin_find_match_location(array $preferenceValues, array $candidate): bool
{
    $wanted = $preferenceValues['location'] ?? [];
    if (!$wanted) return true;
    if (in_array('all kerala', array_map('admin_find_match_normalize', $wanted), true)) return true;

    $place = admin_find_match_normalize((string)($candidate['place'] ?? ''));
    $district = admin_find_match_normalize((string)($candidate['district'] ?? ''));
    foreach ($wanted as $value) {
        $v = admin_find_match_normalize((string)$value);
        if ($v === $place || $v === $district) return true;
    }
    return false;
}

function admin_find_match_candidate_matches(array $source, array $preferences, array $candidate, bool $advanced): bool
{
    $age = admin_find_match_age($candidate['date_of_birth'] ?? null);

    if ($preferences['ageMin'] !== null && ($age === null || $age < $preferences['ageMin'])) return false;
    if ($preferences['ageMax'] !== null && ($age === null || $age > $preferences['ageMax'])) return false;

    if ($advanced) {
        if ($preferences['heightMin'] !== null && (float)$candidate['height'] < $preferences['heightMin']) return false;
        if ($preferences['heightMax'] !== null && (float)$candidate['height'] > $preferences['heightMax']) return false;
    }

    if ($preferences['preferredReligion'] !== '' &&
        admin_find_match_normalize((string)$candidate['religion']) !== admin_find_match_normalize($preferences['preferredReligion'])) return false;

    if (!admin_find_match_value($preferences['values'], 'marital_status', $candidate['marital_status'] ?? null)) return false;
    if (!admin_find_match_location($preferences['values'], $candidate)) return false;
    if (!admin_find_match_value($preferences['values'], 'education', $candidate['highest_education'] ?? null)) return false;

    if (!$advanced) return true;

    $advancedMap = [
        'sect' => 'sect',
        'sunni_group' => 'muslim_group',
        'salafi_group' => 'salafi_group',
        'caste' => 'caste',
        'sub_caste' => 'sub_caste',
        'education_specific' => 'specialization',
        'career_sector' => 'job_sector',
        'family_status' => 'family_status',
        'physical_status' => 'physical_status',
        'complexion' => 'complexion',
        'star' => 'nakshatra',
    ];

    foreach ($advancedMap as $preferenceType => $column) {
        if (!admin_find_match_value($preferences['values'], $preferenceType, $candidate[$column] ?? null)) return false;
    }

    // These preference fields are stored as scalar values in profile_preferences.
    if ($preferences['acceptanceOfKids'] !== '') {
        $hasKids = strtolower(trim((string)($candidate['has_kids'] ?? '')));
        $wanted = strtolower(trim($preferences['acceptanceOfKids']));
        if ($wanted !== '' && $hasKids !== '' && $wanted !== $hasKids) return false;
    }

    // Horoscope/star preference is handled above through preference_values('star').
    // Location radius is intentionally not guessed: it requires valid coordinates and a numeric radius.
    // If supplied and usable, apply it as an additional geographic filter.
    $radiusValues = $preferences['values']['location_radius'] ?? [];
    if ($radiusValues && isset($source['latitude'], $source['longitude'], $candidate['latitude'], $candidate['longitude'])) {
        $radius = (float)$radiusValues[0];
        if ($radius > 0 && $source['latitude'] !== null && $source['longitude'] !== null &&
            $candidate['latitude'] !== null && $candidate['longitude'] !== null) {
            $earthKm = 6371.0;
            $lat1 = deg2rad((float)$source['latitude']);
            $lat2 = deg2rad((float)$candidate['latitude']);
            $dLat = deg2rad((float)$candidate['latitude'] - (float)$source['latitude']);
            $dLon = deg2rad((float)$candidate['longitude'] - (float)$source['longitude']);
            $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
            $distance = 2 * $earthKm * asin(min(1, sqrt($a)));
            if ($distance > $radius) return false;
        }
    }

    return true;
}

function admin_find_match_result_profile(array $row): array
{
    $age = admin_find_match_age($row['date_of_birth'] ?? null);
    $photoUrl = null;
    if (!empty($row['photo_path'])) {
        $photoUrl = photo_url_for_viewer((string)$row['photo_path'], true);
    }

    return [
        'memberId' => (string)$row['member_id'],
        'name' => (string)$row['full_name'],
        'age' => $age,
        'place' => (string)$row['place'],
        'district' => (string)$row['district'],
        'maritalStatus' => (string)$row['marital_status'],
        'religion' => (string)$row['religion'],
        'sect' => (string)($row['sect'] ?? ''),
        'education' => (string)$row['highest_education'],
        'specialization' => (string)($row['specialization'] ?? ''),
        'jobTitle' => (string)($row['job_title'] ?? ''),
        'jobSector' => (string)($row['job_sector'] ?? ''),
        'height' => (float)$row['height'],
        'homeVerified' => ((int)$row['home_verified']) === 1,
        'photoUrl' => $photoUrl,
    ];
}

function admin_find_match_fetch_candidates(PDO $pdo, int $sourceUserId, string $sourceGender): array
{
    $stmt = $pdo->prepare(
        'SELECT
            p.*, u.member_id,
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
         INNER JOIN users u ON u.id = p.user_id
         WHERE p.user_id <> ?
           AND LOWER(p.gender) <> LOWER(?)
           AND u.role = "user"
           AND u.account_status = "active"
           AND p.registration_completed = 1
           AND p.profile_status IN ("new", "pending_verification", "verified")
           AND NOT EXISTS (
                SELECT 1
                FROM interests i
                WHERE (
                    (i.sender_user_id = ? AND i.receiver_user_id = p.user_id)
                    OR
                    (i.sender_user_id = p.user_id AND i.receiver_user_id = ?)
                )
                AND i.status = "declined"
           )
         ORDER BY p.home_verified DESC, p.created_at DESC'
    );
    $stmt->execute([$sourceUserId, $sourceGender, $sourceUserId, $sourceUserId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function admin_find_match_basic(PDO $pdo, array $source, array $preferences): array
{
    $candidates = admin_find_match_fetch_candidates($pdo, (int)$source['user_id'], (string)$source['gender']);
    $results = [];

    foreach ($candidates as $candidate) {
        if (!admin_find_match_candidate_matches($source, $preferences, $candidate, false)) continue;
        $results[] = admin_find_match_result_profile($candidate);
    }

    return $results;
}

function admin_find_match_advanced(PDO $pdo, array $source, array $preferences): array
{
    $candidates = admin_find_match_fetch_candidates($pdo, (int)$source['user_id'], (string)$source['gender']);
    $results = [];

    foreach ($candidates as $candidate) {
        if (!admin_find_match_candidate_matches($source, $preferences, $candidate, true)) continue;
        $results[] = admin_find_match_result_profile($candidate);
    }

    return $results;
}

function admin_find_match_payload(array $source, array $preferences, array $results, string $mode): array
{
    return [
        'sourceProfile' => admin_find_match_result_profile($source),
        'preferences' => $preferences,
        'mode' => $mode,
        'profiles' => $results,
        'count' => count($results),
    ];
}

function get_admin_find_match(string $memberId): never
{
    require_admin_find_match_access();

    $memberId = trim($memberId);
    if ($memberId === '') error_response('Member ID is required.', [], 422);

    $pdo = db();
    $source = admin_find_match_profile($pdo, $memberId);
    $preferences = admin_find_match_preferences($pdo, (int)$source['user_id']);

    $mode = strtolower(trim((string)($_GET['mode'] ?? 'basic')));
    if (!in_array($mode, ['basic', 'customize', 'advanced'], true)) {
        error_response('Invalid match mode.', [], 422);
    }

    if ($mode === 'customize') {
        success_response('Profile preferences loaded successfully.', admin_find_match_payload($source, $preferences, [], $mode));
    }

    $results = $mode === 'advanced'
        ? admin_find_match_advanced($pdo, $source, $preferences)
        : admin_find_match_basic($pdo, $source, $preferences);

    success_response('Admin matching profiles fetched successfully.', admin_find_match_payload($source, $preferences, $results, $mode));
}

function post_admin_find_match_customize(): never
{
    require_admin_find_match_access();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) error_response('Invalid request body.', [], 422);

    $memberId = trim((string)($input['member_id'] ?? ''));
    if ($memberId === '') error_response('Member ID is required.', [], 422);

    $pdo = db();
    $source = admin_find_match_profile($pdo, $memberId);
    $stored = admin_find_match_preferences($pdo, (int)$source['user_id']);

    $custom = is_array($input['preferences'] ?? null) ? $input['preferences'] : [];
    $preferences = $stored;

    foreach (['ageMin','ageMax','heightMin','heightMax','preferredReligion','acceptanceOfKids','horoscopeRequired'] as $key) {
        if (array_key_exists($key, $custom)) $preferences[$key] = $custom[$key];
    }

    if (isset($custom['values']) && is_array($custom['values'])) {
        $preferences['values'] = [];
        foreach ($custom['values'] as $type => $values) {
            if (!is_array($values)) continue;
            $clean = [];
            foreach ($values as $value) {
                $value = trim((string)$value);
                if ($value !== '') $clean[] = $value;
            }
            if ($clean) $preferences['values'][(string)$type] = array_values(array_unique($clean));
        }
    }

    $results = admin_find_match_basic($pdo, $source, $preferences);
    success_response('Customized matching profiles fetched successfully.', admin_find_match_payload($source, $preferences, $results, 'customize'));
}
