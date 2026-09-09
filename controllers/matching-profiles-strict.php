<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/photo-security.php';

/*
|--------------------------------------------------------------------------
| STRICT MATCHING ENGINE
|--------------------------------------------------------------------------
|
| This file is intentionally separate from matching-profiles.php.
| The existing matching engine is not modified.
|
| Strict matching is preference-driven and one-directional, exactly like
| the current matching endpoint: source user's preferences are checked
| against each candidate's actual profile data.
|--------------------------------------------------------------------------
*/

function strict_match_normalize(mixed $value): string
{
    return strtolower(trim((string)$value));
}

function strict_match_age(?string $dob): ?int
{
    if (!$dob) {
        return null;
    }

    try {
        return (new DateTime($dob))->diff(new DateTime('today'))->y;
    } catch (Throwable) {
        return null;
    }
}

function strict_match_values(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT preference_type, value
         FROM preference_values
         WHERE user_id = ?
         ORDER BY id ASC'
    );
    $stmt->execute([$userId]);

    $values = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = trim((string)($row['preference_type'] ?? ''));
        $value = trim((string)($row['value'] ?? ''));

        if ($type === '' || $value === '') {
            continue;
        }

        $values[$type][] = $value;
    }

    return $values;
}

function strict_match_preferences(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT age_min, age_max, height_min, height_max,
                preferred_religion, acceptance_of_kids, horoscope_required
         FROM profile_preferences
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'ageMin' => ($row['age_min'] ?? '') !== '' ? (int)$row['age_min'] : null,
        'ageMax' => ($row['age_max'] ?? '') !== '' ? (int)$row['age_max'] : null,
        'heightMin' => ($row['height_min'] ?? '') !== '' ? (float)$row['height_min'] : null,
        'heightMax' => ($row['height_max'] ?? '') !== '' ? (float)$row['height_max'] : null,
        'preferredReligion' => trim((string)($row['preferred_religion'] ?? '')),
        'acceptanceOfKids' => trim((string)($row['acceptance_of_kids'] ?? '')),
        'horoscopeRequired' => trim((string)($row['horoscope_required'] ?? '')),
        'values' => strict_match_values($pdo, $userId),
    ];
}

function strict_match_preference_values_match(
    array $preferences,
    string $type,
    mixed $candidateValue
): bool {
    $wanted = $preferences['values'][$type] ?? [];

    // No preference means this criterion is not restrictive.
    if (!$wanted) {
        return true;
    }

    $normalizedWanted = array_map(
        'strict_match_normalize',
        $wanted
    );

    // "any" explicitly disables this criterion.
    if (in_array('any', $normalizedWanted, true)) {
        return true;
    }

    $candidate = strict_match_normalize($candidateValue);

    // A specified preference cannot match a missing candidate value.
    if ($candidate === '') {
        return false;
    }

    return in_array($candidate, $normalizedWanted, true);
}

function strict_match_location(
    array $preferences,
    array $candidate
): bool {
    $wanted = $preferences['values']['location'] ?? [];

    if (!$wanted) {
        return true;
    }

    $normalizedWanted = array_map(
        'strict_match_normalize',
        $wanted
    );

    if (in_array('all kerala', $normalizedWanted, true) ||
        in_array('any', $normalizedWanted, true)) {
        return true;
    }

    $place = strict_match_normalize($candidate['place'] ?? '');
    $district = strict_match_normalize($candidate['district'] ?? '');

    if ($place === '' && $district === '') {
        return false;
    }

    foreach ($normalizedWanted as $value) {
        if ($value === $place || $value === $district) {
            return true;
        }
    }

    return false;
}

function strict_match_radius_km(?string $value): ?float
{
    $value = strict_match_normalize($value);

    if ($value === '' || $value === 'any' || $value === 'anywhere in kerala') {
        return null;
    }

    if (preg_match('/within\s+(\d+(?:\.\d+)?)\s*km/i', $value, $matches)) {
        return (float)$matches[1];
    }

    return null;
}

function strict_match_distance_km(
    float $lat1,
    float $lon1,
    float $lat2,
    float $lon2
): float {
    $earthKm = 6371.0;
    $lat1Rad = deg2rad($lat1);
    $lat2Rad = deg2rad($lat2);
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) ** 2
        + cos($lat1Rad) * cos($lat2Rad) * sin($dLon / 2) ** 2;

    return 2 * $earthKm * asin(min(1.0, sqrt($a)));
}

function strict_match_radius(
    array $preferences,
    array $source,
    array $candidate
): bool {
    $radiusValues = $preferences['values']['location_radius'] ?? [];

    if (!$radiusValues) {
        return true;
    }

    $radius = strict_match_radius_km((string)$radiusValues[0]);

    // Any / Anywhere in Kerala does not impose a radius restriction.
    if ($radius === null) {
        return true;
    }

    $sourceLat = $source['latitude'] ?? null;
    $sourceLon = $source['longitude'] ?? null;
    $candidateLat = $candidate['latitude'] ?? null;
    $candidateLon = $candidate['longitude'] ?? null;

    // Strict means we must be able to verify the requested radius.
    if ($sourceLat === null || $sourceLon === null ||
        $candidateLat === null || $candidateLon === null ||
        $sourceLat === '' || $sourceLon === '' ||
        $candidateLat === '' || $candidateLon === '') {
        return false;
    }

    $distance = strict_match_distance_km(
        (float)$sourceLat,
        (float)$sourceLon,
        (float)$candidateLat,
        (float)$candidateLon
    );

    return $distance <= $radius;
}

function strict_match_income(array $preferences, ?string $candidateIncome): bool
{
    $wanted = $preferences['values']['income'] ?? [];

    if (!$wanted) {
        return true;
    }

    $normalizedWanted = array_map(
        'strict_match_normalize',
        $wanted
    );

    if (in_array('any', $normalizedWanted, true)) {
        return true;
    }

    $candidate = strict_match_normalize($candidateIncome);

    if ($candidate === '') {
        return false;
    }

    return in_array($candidate, $normalizedWanted, true);
}

function strict_match_kids(array $preferences, array $candidate): bool
{
    $wanted = strict_match_normalize($preferences['acceptanceOfKids'] ?? '');

    if ($wanted === '') {
        return true;
    }

    $hasKids = strict_match_normalize($candidate['has_kids'] ?? '');

    if ($hasKids === '') {
        return false;
    }

    if ($wanted === 'no') {
        return $hasKids === 'no';
    }

    if ($wanted === 'yes') {
        return $hasKids === 'yes';
    }

    if ($hasKids !== 'yes') {
        return false;
    }

    $living = strict_match_normalize($candidate['kids_living_status'] ?? '');

    if ($wanted === 'yes_living') {
        return $living === 'with_me';
    }

    if ($wanted === 'yes_not_living') {
        return $living === 'not_with_me';
    }

    return false;
}

function strict_match_horoscope(array $preferences, array $candidate): bool
{
    $wanted = strict_match_normalize($preferences['horoscopeRequired'] ?? '');

    if ($wanted !== 'yes') {
        return true;
    }

    // Horoscope requirement is fulfilled only when the candidate has a
    // stored Nakshatra. The explicit preferred-star criterion is checked
    // separately through preference_values('star').
    return strict_match_normalize($candidate['nakshatra'] ?? '') !== '';
}

function strict_match_candidate(
    array $source,
    array $preferences,
    array $candidate
): bool {
    $age = strict_match_age($candidate['date_of_birth'] ?? null);

    if ($preferences['ageMin'] !== null &&
        ($age === null || $age < $preferences['ageMin'])) {
        return false;
    }

    if ($preferences['ageMax'] !== null &&
        ($age === null || $age > $preferences['ageMax'])) {
        return false;
    }

    if ($preferences['heightMin'] !== null &&
        ((float)($candidate['height'] ?? 0) < $preferences['heightMin'])) {
        return false;
    }

    if ($preferences['heightMax'] !== null &&
        ((float)($candidate['height'] ?? 0) > $preferences['heightMax'])) {
        return false;
    }

    $preferredReligion = strict_match_normalize($preferences['preferredReligion'] ?? '');

    if ($preferredReligion !== '' && $preferredReligion !== 'any' &&
        strict_match_normalize($candidate['religion'] ?? '') !== $preferredReligion) {
        return false;
    }

    if (!strict_match_preference_values_match($preferences, 'marital_status', $candidate['marital_status'] ?? null)) {
        return false;
    }

    if (!strict_match_location($preferences, $candidate)) {
        return false;
    }

    if (!strict_match_preference_values_match($preferences, 'education', $candidate['highest_education'] ?? null)) {
        return false;
    }

    $fieldMap = [
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

    foreach ($fieldMap as $preferenceType => $column) {
        if (!strict_match_preference_values_match(
            $preferences,
            $preferenceType,
            $candidate[$column] ?? null
        )) {
            return false;
        }
    }

    if (!strict_match_income(
        $preferences,
        $candidate['annual_income'] ?? null
    )) {
        return false;
    }

    if (!strict_match_kids($preferences, $candidate)) {
        return false;
    }

    if (!strict_match_horoscope($preferences, $candidate)) {
        return false;
    }

    if (!strict_match_radius($preferences, $source, $candidate)) {
        return false;
    }

    return true;
}

function strict_match_fetch_candidates(
    PDO $pdo,
    int $sourceUserId,
    string $sourceGender
): array {
    $stmt = $pdo->prepare(
        'SELECT
            p.*,
            u.member_id,
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
         ORDER BY
            photo_id IS NULL ASC,
            p.home_verified DESC,
            p.created_at DESC'
    );

    $stmt->execute([
        $sourceUserId,
        $sourceGender,
        $sourceUserId,
        $sourceUserId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function strict_match_result_profile(
    array $row,
    bool $adminViewer = false
): array {
    $age = strict_match_age($row['date_of_birth'] ?? null);
    $photoUrl = null;

    if (!empty($row['photo_id'])) {
        $photoUrl = photo_url_for_viewer((int)$row['photo_id']);
    }

    return [
        'memberId' => (string)($row['member_id'] ?? ''),
        'name' => (string)($row['full_name'] ?? ''),
        'age' => $age,
        'place' => (string)($row['place'] ?? ''),
        'district' => (string)($row['district'] ?? ''),
        'maritalStatus' => (string)($row['marital_status'] ?? ''),
        'religion' => (string)($row['religion'] ?? ''),
        'sect' => (string)($row['sect'] ?? ''),
        'sunniGroup' => (string)($row['muslim_group'] ?? ''),
        'salafiGroup' => (string)($row['salafi_group'] ?? ''),
        'caste' => (string)($row['caste'] ?? ''),
        'subCaste' => (string)($row['sub_caste'] ?? ''),
        'education' => (string)($row['highest_education'] ?? ''),
        'specialization' => (string)($row['specialization'] ?? ''),
        'jobTitle' => (string)($row['job_title'] ?? ''),
        'jobSector' => (string)($row['job_sector'] ?? ''),
        'familyStatus' => (string)($row['family_status'] ?? ''),
        'physicalStatus' => (string)($row['physical_status'] ?? ''),
        'complexion' => (string)($row['complexion'] ?? ''),
        'star' => (string)($row['nakshatra'] ?? ''),
        'income' => (string)($row['annual_income'] ?? ''),
        'hasKids' => (string)($row['has_kids'] ?? ''),
        'kidsLivingStatus' => (string)($row['kids_living_status'] ?? ''),
        'height' => (float)($row['height'] ?? 0),
        'homeVerified' => ((int)($row['home_verified'] ?? 0)) === 1,
        'photoUrl' => $photoUrl,
    ];
}

function get_strict_matching_profiles_for_user(int $userId): array
{
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT id, user_id, gender, date_of_birth, home_verified,
                latitude, longitude
         FROM profiles
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId]);
    $source = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$source) {
        error_response('Your profile was not found.', [], 404);
    }

    $preferences = strict_match_preferences($pdo, $userId);
    $sourceGender = trim((string)($source['gender'] ?? ''));
    $viewerHomeVerified = ((int)($source['home_verified'] ?? 0)) === 1;

    $candidates = strict_match_fetch_candidates(
        $pdo,
        $userId,
        $sourceGender
    );

    $profiles = [];

    foreach ($candidates as $candidate) {
        if (!strict_match_candidate($source, $preferences, $candidate)) {
            continue;
        }

        $profiles[] = strict_match_result_profile(
            $candidate,
            $viewerHomeVerified
        );
    }

    return [
        'profiles' => $profiles,
        'count' => count($profiles),
        'mode' => 'strict',
    ];
}

function get_matching_profiles_strict()
{
    $user = current_user(true);
    $result = get_strict_matching_profiles_for_user((int)$user['id']);

    success_response(
        'Strict matching profiles fetched successfully.',
        $result
    );
}
