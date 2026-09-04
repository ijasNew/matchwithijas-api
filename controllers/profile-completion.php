<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PROFILE COMPLETION STATUS
|--------------------------------------------------------------------------
|
| Two separate concepts are returned:
|
| 1. overall_percentage
|    Registration data + later profile data are included here.
|
| 2. profile_complete
|    Final completion gate. These 8 items are mandatory:
|      - Physical Status
|      - Work Location
|      - Preferred Family Status
|      - Preferred Physical Status
|      - Preferred Location Radius
|      - Family Background
|      - WhatsApp Number
|      - Profile Photo
|
| Expectations remain optional.
|--------------------------------------------------------------------------
*/

function get_profile_completion_status(): never
{
    $user = current_user(true);
    $userId = (int)$user['id'];
    $pdo = db();

    // ---------------------------------------------------------
    // USER / PROFILE DATA
    // ---------------------------------------------------------
    $stmt = $pdo->prepare(
        'SELECT
            p.*,
            u.phone AS account_phone
         FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         WHERE p.user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // ---------------------------------------------------------
    // PREFERENCE VALUES
    // ---------------------------------------------------------
    $stmt = $pdo->prepare(
        'SELECT preference_type, value
         FROM preference_values
         WHERE user_id = ?'
    );
    $stmt->execute([$userId]);
    $preferenceRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $preferences = [];
    foreach ($preferenceRows as $row) {
        $type = trim((string)($row['preference_type'] ?? ''));
        $value = trim((string)($row['value'] ?? ''));

        if ($type === '' || $value === '') {
            continue;
        }

        $preferences[$type][] = $value;
    }

    $values = static function (string $type) use ($preferences): array {
        return $preferences[$type] ?? [];
    };

    // ---------------------------------------------------------
    // ACTIVE PHOTOS
    // ---------------------------------------------------------
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM profile_photos
         WHERE user_id = ?
         AND status = "active"'
    );
    $stmt->execute([$userId]);
    $photoCount = (int)$stmt->fetchColumn();

    // ---------------------------------------------------------
    // REQUIRED 8
    // ---------------------------------------------------------
    $physicalStatusCompleted =
        trim((string)($profile['physical_status'] ?? '')) !== '';

    $workLocationType =
        trim((string)($profile['work_location_type'] ?? ''));
    $workState =
        trim((string)($profile['work_state'] ?? ''));
    $workDistrict =
        trim((string)($profile['work_district'] ?? ''));
    $workCountry =
        trim((string)($profile['work_country'] ?? ''));
    $workCity =
        trim((string)($profile['work_city'] ?? ''));

    $workLocationCompleted = false;

    if (
        in_array(
            $workLocationType,
            ['india_same_state', 'india_other_state'],
            true
        )
        && $workState !== ''
        && $workDistrict !== ''
    ) {
        $workLocationCompleted = true;
    } elseif (
        $workLocationType === 'outside_india'
        && $workCountry !== ''
        && $workCity !== ''
    ) {
        $workLocationCompleted = true;
    }

    $preferredFamilyStatusCompleted =
        count($values('family_status')) > 0;

    $preferredPhysicalStatusCompleted =
        count($values('physical_status')) > 0;

    $preferredLocationRadiusCompleted =
        count($values('location_radius')) > 0;

    $familyBackgroundCompleted =
        trim((string)($profile['family_status'] ?? '')) !== '';

    $photoCompleted = $photoCount > 0;

    // WhatsApp is mandatory for profile completion.
    $whatsappCompleted =
        trim((string)($profile['whatsapp_number'] ?? '')) !== '';

    $required = [
        'physical_status' => $physicalStatusCompleted,
        'work_location' => $workLocationCompleted,
        'preferred_family_status' => $preferredFamilyStatusCompleted,
        'preferred_physical_status' => $preferredPhysicalStatusCompleted,
        'preferred_location_radius' => $preferredLocationRadiusCompleted,
        'family_background' => $familyBackgroundCompleted,
        'whatsapp_number' => $whatsappCompleted,
        'photo' => $photoCompleted
    ];

    $requiredCompletedCount = count(
        array_filter(
            $required,
            static fn(bool $value): bool => $value
        )
    );

    $requiredCount = count($required);
    $requiredPercentage = (int)round(
        ($requiredCompletedCount / $requiredCount) * 100
    );

    $profileComplete =
        $requiredCompletedCount === $requiredCount;

    // ---------------------------------------------------------
    // OVERALL PROFILE COMPLETION
    // Registration-time data is counted here.
    // ---------------------------------------------------------
    $basic =
        trim((string)($profile['full_name'] ?? '')) !== ''
        && trim((string)($profile['gender'] ?? '')) !== ''
        && trim((string)($profile['marital_status'] ?? '')) !== ''
        && !empty($profile['date_of_birth'])
        && !empty($profile['height']);

    $location =
        trim((string)($profile['house_name'] ?? '')) !== ''
        && trim((string)($profile['place'] ?? '')) !== ''
        && trim((string)($profile['district'] ?? '')) !== ''
        && trim((string)($profile['pincode'] ?? '')) !== '';

    $religion =
        trim((string)($profile['religion'] ?? '')) !== '';

    $education =
        trim((string)($profile['highest_education'] ?? '')) !== ''
        && trim((string)($profile['job_title'] ?? '')) !== ''
        && trim((string)($profile['job_sector'] ?? '')) !== '';

    $preference =
        $profile['age_min'] ?? null;
    // Partner preference numeric/text fields live in profile_preferences.
    $stmt = $pdo->prepare(
        'SELECT age_min, age_max, height_min, height_max, preferred_religion
         FROM profile_preferences
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId]);
    $preferenceRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $partnerPreference =
        $preferenceRow['age_min'] !== null
        && $preferenceRow['age_max'] !== null
        && $preferenceRow['height_min'] !== null
        && $preferenceRow['height_max'] !== null
        && trim((string)($preferenceRow['preferred_religion'] ?? '')) !== ''
        && count($values('marital_status')) > 0
        && count($values('location')) > 0;

    $physical =
        trim((string)($profile['weight'] ?? '')) !== ''
        && trim((string)($profile['body_type'] ?? '')) !== ''
        && trim((string)($profile['complexion'] ?? '')) !== ''
        && $physicalStatusCompleted;

    $contact =
        trim((string)($profile['account_phone'] ?? '')) !== ''
        || trim((string)($profile['whatsapp_number'] ?? '')) !== ''
        || trim((string)($profile['secondary_mobile'] ?? '')) !== ''
        || trim((string)($profile['email'] ?? '')) !== '';

    $work =
        trim((string)($profile['college_university'] ?? '')) !== ''
        || trim((string)($profile['company_name'] ?? '')) !== ''
        || $workLocationCompleted
        || trim((string)($profile['annual_income'] ?? '')) !== '';

    $family =
        trim((string)($profile['father_name'] ?? '')) !== ''
        || trim((string)($profile['mother_name'] ?? '')) !== ''
        || $profile['brothers'] !== null
        || $profile['sisters'] !== null
        || $familyBackgroundCompleted
        || trim((string)($profile['home_type'] ?? '')) !== '';

    $additionalPreferences =
        count($values('family_status')) > 0
        || count($values('physical_status')) > 0
        || count($values('location_radius')) > 0
        || count($values('income')) > 0
        || count($values('complexion')) > 0
        || count($values('star')) > 0
        || array_key_exists('horoscope_required', $profile);

    $expectations =
        trim((string)($profile['expectations'] ?? '')) !== '';

    $photos = $photoCompleted;

    $overallSections = [
        'basic' => $basic,
        'location' => $location,
        'religion' => $religion,
        'education' => $education,
        'preference' => $partnerPreference,
        'physical' => $physical,
        'contact' => $contact,
        'work' => $work,
        'family' => $family,
        'additional_preferences' => $additionalPreferences,
        'expectations' => $expectations,
        'photos' => $photos
    ];

    $overallCompletedCount = count(
        array_filter(
            $overallSections,
            static fn(bool $value): bool => $value
        )
    );

    $overallCount = count($overallSections);
    $overallPercentage = (int)round(
        ($overallCompletedCount / $overallCount) * 100
    );

    success_response(
        'Profile completion status fetched successfully.',
        [
            'profile_complete' => $profileComplete,
            'percentage' => $overallPercentage,
            'required_percentage' => $requiredPercentage,
            'completed_count' => $requiredCompletedCount,
            'required_count' => $requiredCount,
            'required' => $required,
            'photo_count' => $photoCount,
            'overall_completed_count' => $overallCompletedCount,
            'overall_count' => $overallCount,
            'overall_sections' => $overallSections
        ]
    );
}
