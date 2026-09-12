<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/photo-security.php';

 

const GENDER_OPTIONS = ['male', 'female'];

const PROFILE_FOR_OPTIONS = [
    'self', 'son', 'daughter', 'brother', 'sister', 'relative', 'friend'
];

const MARITAL_STATUS_OPTIONS = [
    'never_married', 'divorced', 'nikah_divorce',
    'widowed', 'separated', 'awaiting_divorce'
];

const PREFERRED_MARITAL_STATUS_OPTIONS = [
    'never_married',
    'divorced',
    'nikah_divorce',
    'widowed',
    'separated',
    'awaiting_divorce',
    'Any'
];

const KIDS_REQUIRED_MARITAL_STATUSES = [
    'divorced', 'widowed', 'separated', 'awaiting_divorce'
];

const HAS_KIDS_OPTIONS = ['yes', 'no'];

const KIDS_LIVING_STATUS_OPTIONS = [
    'with_me',
    'not_with_me'
];

const ACCEPTANCE_OF_KIDS_OPTIONS = [
    'yes',
    'no',
    'yes_not_living',
    'yes_living'
];

const LOCATION_METHOD_OPTIONS = ['current', 'map', 'manual'];

const RELIGION_OPTIONS = ['Muslim', 'Hindu', 'Christian', 'Other'];

const MUSLIM_SECT_OPTIONS = [
    'Sunni', 'Salafi', 'Jamat Islami', 'Hanafi', 'Shafi', 'Other'
];

const SUNNI_GROUP_OPTIONS = ['AP-Sunni', 'EK-Sunni', 'Sunni'];

const SALAFI_GROUP_OPTIONS = [
    'KNM (Mainstream)', 'KNM Markazu Dawa', 'Wisdom',
    'Salafi Independent', 'Other Salafi / Mujahid'
];

const HINDU_CASTE_OPTIONS = [
    'Thiyya / Ezhava', 'Namboothiri', 'Nair', 'Viswakarma', 'SC', 'ST', 'Other'
];

const HINDU_SUB_CASTE_OPTIONS_BY_CASTE = [
    'Nair' => [
        'Menon', 'Pillai', 'Panikkar', 'Nambiar',
        'Kurupp', 'Vilakithala Nair', 'Veluthedath Nair'
    ],
    'Viswakarma' => [
        'Asari (Carpenters)', 'Kollan (Blacksmiths)',
        'Moosari (Bell metal and brass smiths)',
        'Thattan (Goldsmiths)', 'Kallassary (Stonemasons)'
    ],
    'SC' => [
        'Pulayan / Pulayar', 'Cheruman', 'Kanakkan',
        'Kuravan', 'Parayan', 'Others'
    ],
    'ST' => [
        'Paniyan', 'Irular', 'Kurichiar', 'Kanikkaran', 'Other'
    ]
];

const NAKSHATRA_OPTIONS = [
    'Ashwini (Aswathi)', 'Bharani', 'Krittika (Karthika)', 'Rohini',
    'Mrigashirsha (Makayiram)', 'Ardra (Thiruvathira)',
    'Punarvasu (Punartham)', 'Pushya (Pooyam)', 'Ashlesha (Ayilyam)',
    'Magha (Makam)', 'Purva Phalguni (Pooram)', 'Uttara Phalguni (Uthram)',
    'Hasta (Atham)', 'Chitra', 'Swati (Chothi)', 'Vishakha (Vishakam)',
    'Anuradha (Anizham)', 'Jyeshtha (Thriketta)', 'Mula (Moolam)',
    'Purva Ashadha (Pooradam)', 'Uttara Ashadha (Uthradam)',
    'Shravana (Thiruvonam)', 'Dhanishtha (Avittam)', 'Shatabhisha (Chathayam)',
    'Purva Bhadrapada (Pooruruttathi)', 'Uttara Bhadrapada (Uthrattathi)', 'Revati'
];

const RASHI_OPTIONS = [
    'Medam (Aries)', 'Idavam (Taurus)', 'Midhunam (Gemini)',
    'Karkkidakam (Cancer)', 'Chingam (Leo)', 'Kanni (Virgo)',
    'Thulam (Libra)', 'Vrischikam (Scorpio)', 'Dhanu (Sagittarius)',
    'Makaram (Capricorn)', 'Kumbham (Aquarius)', 'Meenam (Pisces)'
];

const CHRISTIAN_DENOMINATION_OPTIONS = [
    'Catholic', 'Orthodox', 'Protestant', 'Pentecostal', 'Other'
];

/*
 * Preference-only options.
 * These are intentionally separate from actual profile options so that
 * profile "Other" remains a real profile value, while preference "Any"
 * acts only as a wildcard preference.
 */
const PREFERRED_MUSLIM_SECT_OPTIONS = [
    'Sunni', 'Salafi', 'Jamat Islami', 'Hanafi', 'Shafi', 'Any'
];

const PREFERRED_SUNNI_GROUP_OPTIONS = [
    'AP-Sunni', 'EK-Sunni', 'Sunni', 'Any'
];

const PREFERRED_SALAFI_GROUP_OPTIONS = [
    'KNM (Mainstream)', 'KNM Markazu Dawa', 'Wisdom',
    'Salafi Independent', 'Other Salafi / Mujahid', 'Any'
];

const PREFERRED_HINDU_CASTE_OPTIONS = [
    'Thiyya / Ezhava', 'Namboothiri', 'Nair', 'Viswakarma', 'SC', 'ST', 'Any'
];

const PREFERRED_CHRISTIAN_DENOMINATION_OPTIONS = [
    'Catholic', 'Orthodox', 'Protestant', 'Pentecostal', 'Any'
];

const CHRISTIAN_SUB_GROUP_OPTIONS_BY_DENOMINATION = [
    'Catholic' => [
        'Syro-Malabar Catholic', 'Latin Catholic', 'Syro-Malankara Catholic'
    ],
    'Orthodox' => [
        'Malankara Orthodox Syrian Church', 'Jacobite Syrian Christian Church'
    ],
    'Protestant' => [
        'Church of South India (CSI)', 'Mar Thoma Syrian Church',
        'St. Thomas Evangelical Church', 'Lutheran'
    ],
    'Pentecostal' => [
        'Indian Pentecostal Church of God (IPC)', 'Assemblies of God (AG)',
        'Church of God (Full Gospel) in India', 'The Pentecostal Mission (TPM)',
        'Sharon Fellowship Church', 'New India Church of God', 'Other Pentecostal'
    ],
    'Other' => [
        'Chaldean Syrian Church', 'Malabar Independent Syrian Church',
        'Seventh-day Adventist', 'Salvation Army', 'Brethren',
        "Jehovah's Witnesses", 'Non-denominational', 'Other'
    ]
];

const EDUCATION_OPTIONS = [
    'PhD / Doctorate', "Master's Degree", 'Professional Degree',
    'General Degree (Bachelors)', 'Diploma', 'ITI / Technical Certificate',
    'Plus Two / Higher Secondary', 'Religious / Islamic Education',
    'Others / Below 10th'
];

const SPECIALIZATION_OPTIONS_BY_EDUCATION = [
    'PhD / Doctorate' => [
        'Engineering', 'Medicine', 'Science', 'Arts', 'Commerce',
        'Management', 'Law', 'Education', 'Social Science',
        'Computer Science / IT', 'Other'
    ],
    "Master's Degree" => [
        'MA', 'MSc', 'MCom', 'MBA', 'MCA', 'MSW', 'MEd', 'LLM', 'MTech', 'Other'
    ],
    'Professional Degree' => [
       'MBBS',
  'MD / MS / DNB',
  'BDS / MDS',
  'BAMS / BHMS / BUMS',
  'BE / B.Tech',
  'ME / M.Tech',
  'B.Pharm / Pharm.D',
  'BPT / MPT',
  'CA / CMA / CS / ACCA',
  'LLB / LLM',
  'Other Professional'
    ],
    'General Degree (Bachelors)' => [
        'BA', 'BSc', 'BCom', 'BBA', 'BCA', 'BBM', 'BSW', 'Other Bachelor’s'
    ],
    'Diploma' => [
        'Engineering Diploma', 'Medical / Paramedical', 'Computer / IT',
        'Management', 'Design', 'Hospitality', 'Other Diploma'
    ],
    'ITI / Technical Certificate' => [
        'Electrician', 'Fitter', 'Mechanic', 'Welder', 'Plumber',
        'Computer / IT', 'Automobile', 'Electronics', 'Other Technical'
    ],
    'Plus Two / Higher Secondary' => [
        'Science', 'Commerce', 'Humanities', 'Vocational', 'Other'
    ],
    'Religious / Islamic Education' => [
        'Madrasa', 'Dars', 'Alim', 'Hifz', 'Islamic Studies', 'Other'
    ],
    'Others / Below 10th' => [
        'SSLC', 'Below SSLC', 'Other'
    ]
];

const JOB_SECTOR_OPTIONS = [
    'Business / Self Employed', 'Private', 'Government', 'Freelance', 'Student'
];

// Note: this list genuinely differs from EDUCATION_OPTIONS on the frontend
// ("Bachelor's Degree" here vs "General Degree (Bachelors)" above) -- kept
// as-is to match register.ts exactly rather than "fixing" a possible typo.
const PREFERRED_EDUCATION_OPTIONS = [
    'PhD / Doctorate', "Master's Degree", 'Professional Degree',
    "Bachelor's Degree", 'Diploma', 'ITI / Technical Certificate',
    'Plus Two / Higher Secondary', 'Religious / Islamic Education',
    'Others / Below 10th','Any'
];

const PREFERRED_EDUCATION_SPECIFIC_OPTIONS = [
'MBBS',
  'MD / MS / DNB',
  'BDS / MDS',
  'BAMS / BHMS / BUMS',
  'BE / B.Tech',
  'ME / M.Tech',
  'B.Pharm / Pharm.D',
  'BPT / MPT',
  'CA / CMA / CS / ACCA',
  'LLB / LLM',
  'Other Professional'
];

const PREFERRED_CAREER_SECTOR_OPTIONS = [
    'Business / Self Employed', 'Private', 'Government', 'Freelance', 'Any'
];

const DISTRICT_OPTIONS = [
    'Alappuzha', 'Ernakulam', 'Idukki', 'Kannur', 'Kasaragod',
    'Kollam', 'Kottayam', 'Kozhikode', 'Malappuram', 'Palakkad',
    'Pathanamthitta', 'Thiruvananthapuram', 'Thrissur', 'Wayanad'
];

const MIN_AGE_MALE = 21;
const MIN_AGE_FEMALE = 18;
const MAX_AGE = 60;

const MIN_HEIGHT_INCHES = 48;  // 4'0"
const MAX_HEIGHT_INCHES = 87;   // 7'3"
 


/*
|--------------------------------------------------------------------------
| Validation helpers
|--------------------------------------------------------------------------
*/

function validate_choice(
    $value,
    array $options,
    string $fieldLabel
): void {
    if (!in_array($value, $options, true)) {
        error_response(
            "Invalid $fieldLabel selected.",
            [],
            422
        );
    }
}

function validate_choice_array(
    $values,
    array $options,
    string $fieldLabel
): void {
    if (!is_array($values)) {
        error_response(
            "Invalid $fieldLabel format.",
            [],
            422
        );
    }

    foreach ($values as $value) {
        if (!in_array($value, $options, true)) {
            error_response(
                "Invalid $fieldLabel selected.",
                [],
                422
            );
        }
    }
}

function validate_text_length(
    $value,
    int $min,
    int $max,
    string $fieldLabel
): string {
    if (!is_string($value)) {
        error_response(
            "Invalid $fieldLabel.",
            [],
            422
        );
    }

    $value = trim($value);
    $length = mb_strlen($value);

    if ($length < $min || $length > $max) {
        error_response(
            "$fieldLabel must be between $min and $max characters.",
            [],
            422
        );
    }

    return $value;
}

function validate_pincode(string $pincode): void
{
    if (!preg_match('/^[0-9]{6}$/', $pincode)) {
        error_response(
            'Please enter a valid 6 digit pincode.',
            [],
            422
        );
    }
}

function validate_numeric_range(
    $value,
    float $min,
    float $max,
    string $fieldLabel
): float {
    if (!is_numeric($value)) {
        error_response(
            "Invalid $fieldLabel.",
            [],
            422
        );
    }

    $number = (float)$value;

    if ($number < $min || $number > $max) {
        error_response(
            "$fieldLabel must be between $min and $max.",
            [],
            422
        );
    }

    return $number;
}

// Same age rule as the frontend: male >= 21, female >= 18.
// Also rejects impossible calendar dates and future dates, neither of
// which the frontend rejects today.
function validate_dob_and_age(
    int $day,
    int $month,
    int $year,
    string $gender
): string {
    if (!checkdate($month, $day, $year)) {
        error_response(
            'Please enter a valid date of birth.',
            [],
            422
        );
    }

    $dateOfBirth = sprintf('%04d-%02d-%02d', $year, $month, $day);

    $today = new DateTime();
    $birthDate = DateTime::createFromFormat('Y-m-d', $dateOfBirth);

    if ($birthDate > $today) {
        error_response(
            'Date of birth cannot be in the future.',
            [],
            422
        );
    }

    $age = $today->diff($birthDate)->y;

    if ($age > MAX_AGE) {
        error_response(
            'Please enter a valid date of birth.',
            [],
            422
        );
    }

    $minimumAge = $gender === 'male' ? MIN_AGE_MALE : MIN_AGE_FEMALE;

    if ($age < $minimumAge) {
        error_response(
            $gender === 'male'
                ? 'A male profile must be at least ' . MIN_AGE_MALE . ' years old.'
                : 'A female profile must be at least ' . MIN_AGE_FEMALE . ' years old.',
            [],
            422
        );
    }

    return $dateOfBirth;
}


/*
|--------------------------------------------------------------------------
| Common helpers
|--------------------------------------------------------------------------
*/

function registration_user(): array
{
    return current_user(true);
}

function registration_user_id(): int
{
    $user = registration_user();

    return (int)$user['id'];
}

function require_fields(array $data, array $fields, string $message): void
{
    foreach ($fields as $field) {

        if (
            !array_key_exists($field, $data) ||
            $data[$field] === null ||
            $data[$field] === ''
        ) {
            error_response(
                $message,
                [],
                422
            );
        }
    }
}

function get_existing_profile(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM profiles WHERE user_id = ? LIMIT 1'
    );

    $stmt->execute([$userId]);

    $profile = $stmt->fetch();

    return $profile ?: null;
}


/*
|--------------------------------------------------------------------------
| 1. SAVE BASIC DETAILS
|--------------------------------------------------------------------------
*/

function save_basic(): never
{
    $user = registration_user();
    $userId = (int)$user['id'];

    $data = request_json();

    require_fields(
        $data,
        [
            'profileFor',
            'gender',
            'fullName',
            'maritalStatus',
            'dobDay',
            'dobMonth',
            'dobYear',
            'heightInches'
        ],
        'Basic details are incomplete.'
    );

    validate_choice($data['profileFor'], PROFILE_FOR_OPTIONS, 'profile-for option');
    validate_choice($data['gender'], GENDER_OPTIONS, 'gender');
    validate_choice($data['maritalStatus'], MARITAL_STATUS_OPTIONS, 'marital status');

    $data['fullName'] = validate_text_length($data['fullName'], 2, 100, 'Name');

    $kidsRequired = in_array(
        $data['maritalStatus'],
        KIDS_REQUIRED_MARITAL_STATUSES,
        true
    );

    if ($kidsRequired) {

        if (empty($data['hasKids'])) {
            error_response('Please select whether the person has kids.', [], 422);
        }

        validate_choice($data['hasKids'], HAS_KIDS_OPTIONS, 'kids option');

        if ($data['hasKids'] === 'yes') {

            if (empty($data['numberOfKids'])) {
                error_response('Please select the number of kids.', [], 422);
            }

                    if ($data['numberOfKids'] === '3_plus') {
                $data['numberOfKids'] = 3;
            }

            if (!in_array($data['numberOfKids'], [1, 2, 3], true)) {
                error_response(
                    'Invalid number of kids selected.',
                    [],
                    422
                );
            }

            if (empty($data['kidsLivingStatus'])) {
                error_response('Please select kids living status.', [], 422);
            }

            if (!empty(KIDS_LIVING_STATUS_OPTIONS)) {
                validate_choice(
                    $data['kidsLivingStatus'],
                    KIDS_LIVING_STATUS_OPTIONS,
                    'kids living status'
                );
            } else {
                validate_text_length($data['kidsLivingStatus'], 1, 100, 'Kids living status');
            }
        }
    } else {
        // Not applicable for this marital status -- ignore anything the
        // client sent for these fields rather than trusting it verbatim.
        $data['hasKids'] = null;
        $data['numberOfKids'] = null;
        $data['kidsLivingStatus'] = null;
    }

    validate_numeric_range(
        $data['heightInches'],
        MIN_HEIGHT_INCHES,
        MAX_HEIGHT_INCHES,
        'Height'
    );

    $dateOfBirth = validate_dob_and_age(
        (int)$data['dobDay'],
        (int)$data['dobMonth'],
        (int)$data['dobYear'],
        $data['gender']
    );

    $pdo = db();

    try {

        $existing = get_existing_profile(
            $pdo,
            $userId
        );

        if ($existing) {

            $sql = "
                UPDATE profiles SET
                    profile_for = :profile_for,
                    gender = :gender,
                    full_name = :full_name,
                    marital_status = :marital_status,
                    has_kids = :has_kids,
                    number_of_kids = :number_of_kids,
                    kids_living_status = :kids_living_status,
                    date_of_birth = :date_of_birth,
                    height = :height,
                    completion_percentage = GREATEST(
                        completion_percentage,
                        20
                    )
                WHERE user_id = :user_id
            ";

        } else {

            $sql = "
                INSERT INTO profiles (
                    user_id,
                    profile_for,
                    gender,
                    full_name,
                    marital_status,
                    has_kids,
                    number_of_kids,
                    kids_living_status,
                    date_of_birth,
                    height,
                    registration_completed,
                    profile_status,
                    completion_percentage,
                    home_verified
                )
                VALUES (
                    :user_id,
                    :profile_for,
                    :gender,
                    :full_name,
                    :marital_status,
                    :has_kids,
                    :number_of_kids,
                    :kids_living_status,
                    :date_of_birth,
                    :height,
                    0,
                    'new',
                    20,
                    0
                )
            ";
        }

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':user_id' =>
                $userId,

            ':profile_for' =>
                $data['profileFor'],

            ':gender' =>
                $data['gender'],

            ':full_name' =>
                trim((string)$data['fullName']),

            ':marital_status' =>
                $data['maritalStatus'],

            ':has_kids' =>
                $data['hasKids'] ?? null,

            ':number_of_kids' =>
                !empty($data['numberOfKids'])
                    ? (int)$data['numberOfKids']
                    : null,

            ':kids_living_status' =>
                $data['kidsLivingStatus'] ?? null,

            ':date_of_birth' =>
                $dateOfBirth,

            ':height' =>
                (float)$data['heightInches']
        ]);

        success_response(
            'Basic details saved successfully.',
            [
                'member_id' =>
                    $user['member_id'],

                'section' =>
                    'basic',

                'completion_percentage' =>
                    20,

                'registration_completed' =>
                    false,

                'account_status' =>
                    'pending'
            ]
        );

    } catch (Throwable $e) {

        error_response(
            'Unable to save basic details.',
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| 2. SAVE LOCATION
|--------------------------------------------------------------------------
*/

function save_location(): never
{
    $userId = registration_user_id();

    $data = request_json();

    require_fields(
        $data,
        [
            'district',
            'state',
            'pincode',
            'place',
            'houseName'
        ],
        'Location details are incomplete.'
    );

    validate_choice($data['district'], DISTRICT_OPTIONS, 'district');
    validate_pincode((string)$data['pincode']);

    $data['houseName'] = validate_text_length($data['houseName'], 1, 150, 'House name');
    $data['place'] = validate_text_length($data['place'], 1, 150, 'Place');
    $data['state'] = validate_text_length($data['state'] ?? 'Kerala', 1, 50, 'State');

    if (!empty($data['locationMethod'])) {
        validate_choice($data['locationMethod'], LOCATION_METHOD_OPTIONS, 'location method');
    }

    if (isset($data['latitude']) && $data['latitude'] !== null) {
        validate_numeric_range($data['latitude'], -90, 90, 'Latitude');
    }

    if (isset($data['longitude']) && $data['longitude'] !== null) {
        validate_numeric_range($data['longitude'], -180, 180, 'Longitude');
    }

    $pdo = db();

    try {

        $existing = get_existing_profile(
            $pdo,
            $userId
        );

        if (!$existing) {
            error_response(
                'Please save basic details first.',
                [],
                422
            );
        }

        $sql = "
            UPDATE profiles SET
                district = :district,
                state = :state,
                pincode = :pincode,
                house_name = :house_name,
                place = :place,
                latitude = :latitude,
                longitude = :longitude,
                location_source = :location_source,
                completion_percentage = GREATEST(
                    completion_percentage,
                    40
                )
            WHERE user_id = :user_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':district' =>
                $data['district'],

            ':state' =>
                $data['state'] ?? 'Kerala',

            ':pincode' =>
                $data['pincode'],

            ':house_name' =>
                $data['houseName'],

            ':place' =>
                $data['place'],

            ':latitude' =>
                $data['latitude'] ?? null,

            ':longitude' =>
                $data['longitude'] ?? null,

            ':location_source' =>
                $data['locationMethod'] ?? null,

            ':user_id' =>
                $userId
        ]);

        success_response(
            'Location details saved successfully.',
            [
                'section' =>
                    'location',

                'completion_percentage' =>
                    40,

                'registration_completed' =>
                    false
            ]
        );

    } catch (Throwable $e) {

        error_response(
            'Unable to save location details.',
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| 3. SAVE RELIGION
|--------------------------------------------------------------------------
*/

function save_religion(): never
{
    $userId = registration_user_id();

    $data = request_json();

    require_fields(
        $data,
        [
            'religion'
        ],
        'Religion details are incomplete.'
    );

    validate_choice($data['religion'], RELIGION_OPTIONS, 'religion');

    if ($data['religion'] === 'Muslim') {

        if (!empty($data['muslimSect'])) {
            validate_choice($data['muslimSect'], MUSLIM_SECT_OPTIONS, 'sect');
        }

        if ($data['muslimSect'] === 'Sunni' && !empty($data['sunniGroup'])) {
            validate_choice($data['sunniGroup'], SUNNI_GROUP_OPTIONS, 'Sunni group');
        }

        if ($data['muslimSect'] === 'Salafi' && !empty($data['salafiGroup'])) {
            validate_choice($data['salafiGroup'], SALAFI_GROUP_OPTIONS, 'Salafi group');
        }

    } elseif ($data['religion'] === 'Hindu') {

        if (!empty($data['hinduCaste'])) {
            validate_choice($data['hinduCaste'], HINDU_CASTE_OPTIONS, 'caste');
        }

        if (!empty($data['hinduSubCaste'])) {

            $validSubCastes = HINDU_SUB_CASTE_OPTIONS_BY_CASTE[$data['hinduCaste'] ?? ''] ?? [];

            if (empty($validSubCastes) || !in_array($data['hinduSubCaste'], $validSubCastes, true)) {
                error_response('Invalid sub-caste selected.', [], 422);
            }
        }

        if (!empty($data['nakshatra'])) {
            validate_choice($data['nakshatra'], NAKSHATRA_OPTIONS, 'nakshatra');
        }

        if (!empty($data['rashi'])) {
            validate_choice($data['rashi'], RASHI_OPTIONS, 'rashi');
        }

    } elseif ($data['religion'] === 'Christian') {

        if (!empty($data['christianDenomination'])) {
            validate_choice(
                $data['christianDenomination'],
                CHRISTIAN_DENOMINATION_OPTIONS,
                'denomination'
            );
        }

        if (!empty($data['christianSubDenomination'])) {

            $validSubGroups = CHRISTIAN_SUB_GROUP_OPTIONS_BY_DENOMINATION[
                $data['christianDenomination'] ?? ''
            ] ?? [];

            if (
                empty($validSubGroups) ||
                !in_array($data['christianSubDenomination'], $validSubGroups, true)
            ) {
                error_response('Invalid sub-denomination selected.', [], 422);
            }
        }

        if (!empty($data['otherChristianChurch'])) {
            validate_text_length($data['otherChristianChurch'], 1, 150, 'Church name');
        }
    }

    $pdo = db();

    try {

        $existing = get_existing_profile(
            $pdo,
            $userId
        );

        if (!$existing) {
            error_response(
                'Please save basic details first.',
                [],
                422
            );
        }

        $sql = "
            UPDATE profiles SET

                religion = :religion,

                sect = :sect,

                muslim_group = :muslim_group,

                salafi_group = :salafi_group,

                caste = :caste,

                sub_caste = :sub_caste,

                nakshatra = :nakshatra,

                rashi = :rashi,

                dosham = :dosham,

                denomination = :denomination,

                christian_sub_group =
                    :christian_sub_group,

                parish_name =
                    :parish_name,

                completion_percentage =
                    GREATEST(
                        completion_percentage,
                        60
                    )

            WHERE user_id = :user_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ':religion' =>
                $data['religion'],

            ':sect' =>
                $data['muslimSect'] ?? null,

            ':muslim_group' =>
                $data['sunniGroup'] ?? null,

            ':salafi_group' =>
                $data['salafiGroup'] ?? null,

            ':caste' =>
                $data['hinduCaste'] ?? null,

            ':sub_caste' =>
                $data['hinduSubCaste'] ?? null,

            ':nakshatra' =>
                $data['nakshatra'] ?? null,

            ':rashi' =>
                $data['rashi'] ?? null,

            ':dosham' =>
                $data['dosham'] ?? null,

            ':denomination' =>
                $data['christianDenomination']
                ?? null,

            ':christian_sub_group' =>
                $data['christianSubDenomination']
                ?? null,

            ':parish_name' =>
                $data['otherChristianChurch']
                ?? null,

            ':user_id' =>
                $userId
        ]);

        success_response(
            'Religion details saved successfully.',
            [
                'section' =>
                    'religion',

                'completion_percentage' =>
                    60,

                'registration_completed' =>
                    false
            ]
        );

    } catch (Throwable $e) {

        error_response(
            'Unable to save religion details.',
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| 4. SAVE EDUCATION / CAREER
|--------------------------------------------------------------------------
*/

function save_education(): never
{
    $userId = registration_user_id();

    $data = request_json();

    require_fields(
        $data,
        [
            'highestEducation',
            'jobTitle',
            'jobSector'
        ],
        'Education and career details are incomplete.'
    );

    validate_choice($data['highestEducation'], EDUCATION_OPTIONS, 'education');
    validate_choice($data['jobSector'], JOB_SECTOR_OPTIONS, 'job sector');
    $data['jobTitle'] = validate_text_length($data['jobTitle'], 1, 100, 'Job title');

    if (!empty($data['specialization'])) {

        $validSpecializations = SPECIALIZATION_OPTIONS_BY_EDUCATION[
            $data['highestEducation']
        ] ?? [];

        if (
            empty($validSpecializations) ||
            !in_array($data['specialization'], $validSpecializations, true)
        ) {
            error_response('Invalid specialization selected.', [], 422);
        }
    }

    $pdo = db();

    try {

        $existing = get_existing_profile(
            $pdo,
            $userId
        );

        if (!$existing) {
            error_response(
                'Please save basic details first.',
                [],
                422
            );
        }

        $sql = "
            UPDATE profiles SET

                highest_education =
                    :highest_education,

                specialization =
                    :specialization,

                job_title =
                    :job_title,

                job_sector =
                    :job_sector,

                completion_percentage =
                    GREATEST(
                        completion_percentage,
                        80
                    )

            WHERE user_id = :user_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ':highest_education' =>
                $data['highestEducation'],

            ':specialization' =>
                $data['specialization'] ?? null,

            ':job_title' =>
                $data['jobTitle'],

            ':job_sector' =>
                $data['jobSector'],

            ':user_id' =>
                $userId
        ]);

        success_response(
            'Education and career details saved successfully.',
            [
                'section' =>
                    'education',

                'completion_percentage' =>
                    80,

                'registration_completed' =>
                    false
            ]
        );

    } catch (Throwable $e) {

        error_response(
            'Unable to save education details.',
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| 5. SAVE PREFERENCES
|--------------------------------------------------------------------------
*/

function save_preferences(): never
{
    $userId = registration_user_id();

    $data = request_json();

    $pdo = db();

    $pdo->beginTransaction();

    try {

        $existing = get_existing_profile(
            $pdo,
            $userId
        );

        if (!$existing) {

            $pdo->rollBack();

            error_response(
                'Please save basic details first.',
                [],
                422
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validation (ranges + whitelists)
        |--------------------------------------------------------------------------
        */

        if (!empty($data['ageMin'])) {
            validate_numeric_range($data['ageMin'], MIN_AGE_FEMALE, MAX_AGE, 'Minimum preferred age');
        }

        if (!empty($data['ageMax'])) {
            validate_numeric_range($data['ageMax'], MIN_AGE_FEMALE, MAX_AGE, 'Maximum preferred age');
        }

        if (!empty($data['ageMin']) && !empty($data['ageMax']) && (int)$data['ageMin'] > (int)$data['ageMax']) {
            error_response('Minimum preferred age cannot be greater than maximum.', [], 422);
        }

        if (!empty($data['heightMin'])) {
            validate_numeric_range($data['heightMin'], MIN_HEIGHT_INCHES, MAX_HEIGHT_INCHES, 'Minimum preferred height');
        }

        if (!empty($data['heightMax'])) {
            validate_numeric_range($data['heightMax'], MIN_HEIGHT_INCHES, MAX_HEIGHT_INCHES, 'Maximum preferred height');
        }

        if (
            !empty($data['heightMin']) && !empty($data['heightMax']) &&
            (float)$data['heightMin'] > (float)$data['heightMax']
        ) {
            error_response('Minimum preferred height cannot be greater than maximum.', [], 422);
        }

        if (!empty($data['religion'])) {
            validate_choice($data['religion'], RELIGION_OPTIONS, 'preferred religion');
        }

        if (!empty($data['acceptanceOfKids']) && !empty(ACCEPTANCE_OF_KIDS_OPTIONS)) {
            validate_choice($data['acceptanceOfKids'], ACCEPTANCE_OF_KIDS_OPTIONS, 'kids acceptance');
        }

       if (!empty($data['maritalStatuses'])) {
    validate_choice_array(
        $data['maritalStatuses'],
        PREFERRED_MARITAL_STATUS_OPTIONS,
        'preferred marital status'
    );

    if (
        in_array('Any', $data['maritalStatuses'], true) &&
        count($data['maritalStatuses']) > 1
    ) {
        error_response(
            '"Any" cannot be combined with other preferred marital status values.',
            [],
            422
        );
    }
}

        if (!empty($data['preferredSects'])) {
            // Covers Muslim sects and, per the frontend's reuse of this field,
            // Christian denominations too.
            $validSects = array_merge(
                PREFERRED_MUSLIM_SECT_OPTIONS,
                PREFERRED_CHRISTIAN_DENOMINATION_OPTIONS
            );
            validate_choice_array($data['preferredSects'], $validSects, 'preferred sect');

            if (
                in_array('Any', $data['preferredSects'], true) &&
                count($data['preferredSects']) > 1
            ) {
                error_response(
                    '"Any" cannot be combined with other preferred sect/denomination values.',
                    [],
                    422
                );
            }
        }

        if (!empty($data['preferredSunniGroups'])) {
            validate_choice_array(
                $data['preferredSunniGroups'],
                PREFERRED_SUNNI_GROUP_OPTIONS,
                'preferred Sunni group'
            );

            if (
                in_array('Any', $data['preferredSunniGroups'], true) &&
                count($data['preferredSunniGroups']) > 1
            ) {
                error_response(
                    '"Any" cannot be combined with other preferred Sunni groups.',
                    [],
                    422
                );
            }
        }

        if (!empty($data['preferredSalafiGroups'])) {
            validate_choice_array(
                $data['preferredSalafiGroups'],
                PREFERRED_SALAFI_GROUP_OPTIONS,
                'preferred Salafi group'
            );

            if (
                in_array('Any', $data['preferredSalafiGroups'], true) &&
                count($data['preferredSalafiGroups']) > 1
            ) {
                error_response(
                    '"Any" cannot be combined with other preferred Salafi groups.',
                    [],
                    422
                );
            }
        }

        if (!empty($data['preferredCastes'])) {
            validate_choice_array(
                $data['preferredCastes'],
                PREFERRED_HINDU_CASTE_OPTIONS,
                'preferred caste'
            );

            if (
                in_array('Any', $data['preferredCastes'], true) &&
                count($data['preferredCastes']) > 1
            ) {
                error_response(
                    '"Any" cannot be combined with other preferred caste values.',
                    [],
                    422
                );
            }
        }

        if (!empty($data['preferredSubCastes'])) {
            // Reused across Hindu sub-castes and Christian sub-denominations
            // (see build ProfileFor logic on the frontend), so validate
            // against the union of both.
            $allSubCastes = array_merge(
                ['Any'],
                ...array_values(HINDU_SUB_CASTE_OPTIONS_BY_CASTE),
                ...array_values(CHRISTIAN_SUB_GROUP_OPTIONS_BY_DENOMINATION)
            );
            validate_choice_array($data['preferredSubCastes'], $allSubCastes, 'preferred sub-caste');

            if (
                in_array('Any', $data['preferredSubCastes'], true) &&
                count($data['preferredSubCastes']) > 1
            ) {
                error_response(
                    '"Any" cannot be combined with other preferred sub-caste/sub-denomination values.',
                    [],
                    422
                );
            }
        }

if (!empty($data['preferredEducation'])) {
    validate_choice_array(
        $data['preferredEducation'],
        PREFERRED_EDUCATION_OPTIONS,
        'preferred education'
    );

    if (
        in_array('Any', $data['preferredEducation'], true) &&
        count($data['preferredEducation']) > 1
    ) {
        error_response(
            '"Any" cannot be combined with other preferred education values.',
            [],
            422
        );
    }
}
        if (!empty($data['preferredEducationSpecific'])) {
            validate_choice_array(
                $data['preferredEducationSpecific'],
                PREFERRED_EDUCATION_SPECIFIC_OPTIONS,
                'preferred specific education'
            );
        }

        if (!empty($data['preferredCareerSectors'])) {
            validate_choice_array($data['preferredCareerSectors'], PREFERRED_CAREER_SECTOR_OPTIONS, 'preferred career sector');
        }

        if (empty($data['preferredLocations'])) {
            error_response('Please select at least one preferred location.', [], 422);
        }

        $preferredLocationOptions = array_merge(
            ['All Kerala'],
            DISTRICT_OPTIONS
        );

        validate_choice_array(
            $data['preferredLocations'],
            $preferredLocationOptions,
            'preferred location'
        );


        /*
        |--------------------------------------------------------------------------
        | Main preference fields
        |--------------------------------------------------------------------------
        */

        $prefSql = "
            INSERT INTO profile_preferences (
                user_id,
                age_min,
                age_max,
                height_min,
                height_max,
                preferred_religion,
                acceptance_of_kids
            )
            VALUES (
                :user_id,
                :age_min,
                :age_max,
                :height_min,
                :height_max,
                :preferred_religion,
                :acceptance_of_kids
            )
            ON DUPLICATE KEY UPDATE

                age_min =
                    VALUES(age_min),

                age_max =
                    VALUES(age_max),

                height_min =
                    VALUES(height_min),

                height_max =
                    VALUES(height_max),

                preferred_religion =
                    VALUES(preferred_religion),

                acceptance_of_kids =
                    VALUES(acceptance_of_kids)
        ";

        $stmt = $pdo->prepare($prefSql);

        $stmt->execute([

            ':user_id' =>
                $userId,

            ':age_min' =>
                !empty($data['ageMin'])
                    ? (int)$data['ageMin']
                    : null,

            ':age_max' =>
                !empty($data['ageMax'])
                    ? (int)$data['ageMax']
                    : null,

            ':height_min' =>
                !empty($data['heightMin'])
                    ? (float)$data['heightMin']
                    : null,

            ':height_max' =>
                !empty($data['heightMax'])
                    ? (float)$data['heightMax']
                    : null,

            ':preferred_religion' =>
                $data['religion'] ?? null,

            ':acceptance_of_kids' =>
                $data['acceptanceOfKids'] ?? null
        ]);


        /*
        |--------------------------------------------------------------------------
        | Preference values
        |--------------------------------------------------------------------------
        */

        $pdo->prepare(
            'DELETE FROM preference_values WHERE user_id = ?'
        )->execute([
            $userId
        ]);

            /*
                    |--------------------------------------------------------------------------
                | Multiple parent preferences → child preference not applicable
                |--------------------------------------------------------------------------
                */

                // Muslim: multiple sects OR Any parent -> child preference is not applicable.
                if (
                    ($data['religion'] ?? '') === 'Muslim' &&
                    isset($data['preferredSects']) &&
                    is_array($data['preferredSects']) &&
                    (
                        count($data['preferredSects']) > 1 ||
                        in_array('Any', $data['preferredSects'], true)
                    )
                ) {
                    $data['preferredSunniGroups'] = [];
                    $data['preferredSalafiGroups'] = [];
                }

                // Hindu: multiple castes OR Any parent -> child preference is not applicable.
                if (
                    ($data['religion'] ?? '') === 'Hindu' &&
                    isset($data['preferredCastes']) &&
                    is_array($data['preferredCastes']) &&
                    (
                        count($data['preferredCastes']) > 1 ||
                        in_array('Any', $data['preferredCastes'], true)
                    )
                ) {
                    $data['preferredSubCastes'] = [];
                }

                // Christian: multiple denominations OR Any parent -> child preference is not applicable.
                if (
                    ($data['religion'] ?? '') === 'Christian' &&
                    isset($data['preferredSects']) &&
                    is_array($data['preferredSects']) &&
                    (
                        count($data['preferredSects']) > 1 ||
                        in_array('Any', $data['preferredSects'], true)
                    )
                ) {
                    $data['preferredSubCastes'] = [];
                }

        $preferenceGroups = [

            'marital_status' =>
                $data['maritalStatuses'] ?? [],

            'sect' =>
                $data['preferredSects'] ?? [],

            'sunni_group' =>
                $data['preferredSunniGroups'] ?? [],

            'salafi_group' =>
                $data['preferredSalafiGroups'] ?? [],

            'caste' =>
                $data['preferredCastes'] ?? [],

            'sub_caste' =>
                $data['preferredSubCastes'] ?? [],

            'education' =>
                $data['preferredEducation'] ?? [],

            'education_specific' =>
                $data['preferredEducationSpecific'] ?? [],

            'career_sector' =>
                $data['preferredCareerSectors'] ?? [],

            'location' =>
                $data['preferredLocations'] ?? []
        ];

        $valueStmt = $pdo->prepare(
            'INSERT INTO preference_values
                (user_id, preference_type, value)
             VALUES
                (?, ?, ?)'
        );

        foreach (
            $preferenceGroups
            as $type => $values
        ) {

            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $value) {

                $value = trim((string)$value);

                if ($value === '') {
                    continue;
                }

                $valueStmt->execute([
                    $userId,
                    $type,
                    $value
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Preference section saved
        |--------------------------------------------------------------------------
        */

        $pdo->prepare(
            "UPDATE profiles
             SET completion_percentage =
                 GREATEST(
                     completion_percentage,
                     95
                 )
             WHERE user_id = ?"
        )->execute([
            $userId
        ]);

        $pdo->commit();

        success_response(
            'Preferences saved successfully.',
            [
                'section' =>
                    'preferences',

                'completion_percentage' =>
                    95,

                'registration_completed' =>
                    false
            ]
        );

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_response(
            'Unable to save preferences.',
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| 6. FINALIZE REGISTRATION
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This endpoint does NOT save profile data.
| All profile sections are already saved.
|
*/

// // delte after checking db
function get_profile(): never
{
    $user = current_user(true);

    $userId = (int)$user['id'];

    $pdo = db();

    /*
     |--------------------------------------------------------------------------
     | USERS
     |--------------------------------------------------------------------------
     */

    $stmt = $pdo->prepare(
        'SELECT *
         FROM users
         WHERE id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);


    /*
     |--------------------------------------------------------------------------
     | PROFILES
     |--------------------------------------------------------------------------
     */

    $stmt = $pdo->prepare(
        'SELECT *
         FROM profiles
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);


    /*
     |--------------------------------------------------------------------------
     | PROFILE PREFERENCES
     |--------------------------------------------------------------------------
     */

    $stmt = $pdo->prepare(
        'SELECT *
         FROM profile_preferences
         WHERE user_id = ?
         LIMIT 1'
    );

    $stmt->execute([$userId]);

    $profilePreferences =
        $stmt->fetch(PDO::FETCH_ASSOC);


    /*
     |--------------------------------------------------------------------------
     | PREFERENCE VALUES
     |--------------------------------------------------------------------------
     */

    $stmt = $pdo->prepare(
        'SELECT *
         FROM preference_values
         WHERE user_id = ?
         ORDER BY id ASC'
    );

    $stmt->execute([$userId]);

    $preferenceValues =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     |--------------------------------------------------------------------------
     | RESPONSE
     |--------------------------------------------------------------------------
     */

    success_response(
        'Profile data fetched successfully.',
        [
            'users' =>
                $userData ?: [],

            'profiles' =>
                $profileData ?: [],

            'profile_preferences' =>
                $profilePreferences ?: [],

            'preference_values' =>
                $preferenceValues
        ]
    );
}
// // delte after checking db






/*
|--------------------------------------------------------------------------
| PROFILE VIEW
|--------------------------------------------------------------------------
| Fetch one registered profile by member_id for an authenticated user.
| Contact data is returned only after an accepted interest exists.
*/
function get_profile_view(string $memberId): never
{
    $viewer = current_user(true);
    $viewerId = (int)$viewer['id'];
    $memberId = trim($memberId);

    if ($memberId === '' || !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $memberId)) {
        error_response('Invalid member ID.', [], 422);
    }

    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT
            p.*,
            u.id AS target_user_id,
            u.member_id,
            u.phone AS account_phone
         FROM profiles p
         INNER JOIN users u ON u.id = p.user_id
         WHERE u.member_id = ?
           AND u.role = "user"
           AND u.account_status = "active"
           AND p.registration_completed = 1
           AND p.profile_status NOT IN ("rejected", "blocked")
         LIMIT 1'
    );
    $stmt->execute([$memberId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        error_response('Profile not found.', [], 404);
    }

    $targetUserId = (int)$row['target_user_id'];

    if ($targetUserId === $viewerId) {
        error_response('You cannot open your own profile here.', [], 422);
    }

    $interestStmt = $pdo->prepare(
        'SELECT 1
         FROM interests
         WHERE status = "accepted"
           AND (
                (sender_user_id = ? AND receiver_user_id = ?)
                OR
                (sender_user_id = ? AND receiver_user_id = ?)
           )
         LIMIT 1'
    );
    $interestStmt->execute([
        $viewerId, $targetUserId,
        $targetUserId, $viewerId
    ]);

    $isInterestAccepted = (bool)$interestStmt->fetchColumn();

    $interestStatusStmt = $pdo->prepare(
        'SELECT status FROM interests
         WHERE (sender_user_id = ? AND receiver_user_id = ?)
            OR (sender_user_id = ? AND receiver_user_id = ?)
         ORDER BY id DESC LIMIT 1'
    );
    $interestStatusStmt->execute([$viewerId, $targetUserId, $targetUserId, $viewerId]);
    $interestStatus = $interestStatusStmt->fetchColumn() ?: null;

    $shortlistStmt = $pdo->prepare(
        'SELECT 1 FROM shortlists WHERE user_id = ? AND shortlisted_user_id = ? LIMIT 1'
    );
    $shortlistStmt->execute([$viewerId, $targetUserId]);
    $isShortlisted = (bool)$shortlistStmt->fetchColumn();

    $prefStmt = $pdo->prepare(
        'SELECT age_min, age_max, height_min, height_max,
                preferred_religion, acceptance_of_kids, horoscope_required
         FROM profile_preferences
         WHERE user_id = ?
         LIMIT 1'
    );
    $prefStmt->execute([$targetUserId]);
    $preferences = $prefStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $valuesStmt = $pdo->prepare(
        'SELECT preference_type, value
         FROM preference_values
         WHERE user_id = ?
         ORDER BY id ASC'
    );
    $valuesStmt->execute([$targetUserId]);

    $preferenceValues = [];
    foreach ($valuesStmt->fetchAll(PDO::FETCH_ASSOC) as $valueRow) {
        $type = (string)$valueRow['preference_type'];
        $preferenceValues[$type][] = $valueRow['value'];
    }

        $photoStmt = $pdo->prepare(
        'SELECT id, file_path
        FROM profile_photos
        WHERE user_id = ?
        AND status = "active"
        ORDER BY is_primary DESC, display_order ASC, id ASC'
    );

    $photoStmt->execute([$targetUserId]);

    /*
    * Photo privacy:
    * Viewer Home Verified ആണെങ്കിൽ original photo.
    * അല്ലെങ്കിൽ blurred photo മാത്രം.
    */
    $viewerProfileStmt = $pdo->prepare(
        'SELECT home_verified
        FROM profiles
        WHERE user_id = ?
        LIMIT 1'
    );

    $viewerProfileStmt->execute([$viewerId]);

    $viewerHomeVerified =
        (int)$viewerProfileStmt->fetchColumn() === 1;

    $photos = [];

    foreach ($photoStmt->fetchAll(PDO::FETCH_ASSOC) as $photoRow) {

    $path = trim((string)$photoRow['file_path']);

    if ($path === '') {
        continue;
    }

    // Only internal stored photo paths are handled here.
    if (preg_match('/^https?:\/\//i', $path)) {
        continue;
    }

    $photoUrl = photo_url_for_viewer(
        (int)$photoRow['id']
    );

    if ($photoUrl !== null) {
        $photos[] = $photoUrl;
    }
}

    $age = null;
    if (!empty($row['date_of_birth'])) {
        try {
            $age = (new DateTime((string)$row['date_of_birth']))
                ->diff(new DateTime())
                ->y;
        } catch (Throwable $e) {
            $age = null;
        }
    }

    $profile = [
        'memberId' => $row['member_id'] ?? $memberId,
        'phone' => $isInterestAccepted ? ($row['account_phone'] ?? '') : '',
        'profileFor' => $row['profile_for'] ?? '',
        'gender' => $row['gender'] ?? '',
        'fullName' => $row['full_name'] ?? '',
        'maritalStatus' => $row['marital_status'] ?? '',
        'hasKids' => $row['has_kids'] ?? '',
        'numberOfKids' => $row['number_of_kids'] !== null ? (string)$row['number_of_kids'] : '',
        'kidsLivingStatus' => $row['kids_living_status'] ?? '',
        'dobDay' => !empty($row['date_of_birth']) ? date('d', strtotime((string)$row['date_of_birth'])) : '',
        'dobMonth' => !empty($row['date_of_birth']) ? date('m', strtotime((string)$row['date_of_birth'])) : '',
        'dobYear' => !empty($row['date_of_birth']) ? date('Y', strtotime((string)$row['date_of_birth'])) : '',
        'age' => $age,
        'height' => $row['height'] !== null ? (float)$row['height'] : 0,

        'district' => $row['district'] ?? '',
        'state' => $row['state'] ?? '',
        'pincode' => '',
        'houseName' => '',
        'place' => $row['place'] ?? '',

        'religion' => $row['religion'] ?? '',
        'sect' => $row['sect'] ?? '',
        'muslimGroup' => $row['muslim_group'] ?? '',
        'salafiGroup' => $row['salafi_group'] ?? '',
        'caste' => $row['caste'] ?? '',
        'subCaste' => $row['sub_caste'] ?? '',
        'nakshatra' => $row['nakshatra'] ?? '',
        'rashi' => $row['rashi'] ?? '',
        'dosham' => $row['dosham'] ?? '',
        'denomination' => $row['denomination'] ?? '',
        'christianSubGroup' => $row['christian_sub_group'] ?? '',
        'parishName' => $row['parish_name'] ?? '',

        'highestEducation' => $row['highest_education'] ?? '',
        'specialization' => $row['specialization'] ?? '',
        'jobTitle' => $row['job_title'] ?? '',
        'jobSector' => $row['job_sector'] ?? '',

        'weight' => $row['weight'] !== null ? (float)$row['weight'] : 0,
        'bodyType' => $row['body_type'] ?? '',
        'complexion' => $row['complexion'] ?? '',
        'physicalStatus' => $row['physical_status'] ?? '',

        'secondaryMobile' => $isInterestAccepted ? ($row['secondary_mobile'] ?? '') : '',
        'whatsappNumber' => $isInterestAccepted ? ($row['whatsapp_number'] ?? '') : '',
        'email' => $isInterestAccepted ? ($row['email'] ?? '') : '',

        'collegeUniversity' => $row['college_university'] ?? '',
        'annualIncome' => $row['annual_income'] ?? '',
        'workLocation' => $row['work_location'] ?? '',
        'workLocationType' => $row['work_location_type'] ?? '',
        'workState' => $row['work_state'] ?? '',
        'workDistrict' => $row['work_district'] ?? '',
        'workCountry' => $row['work_country'] ?? '',
        'workCity' => $row['work_city'] ?? '',
        'companyName' => $row['company_name'] ?? '',

        'fatherName' => $row['father_name'] ?? '',
        'fatherOccupation' => $row['father_occupation'] ?? '',
        'fatherStatus' => $row['father_status'] ?? '',
        'motherName' => $row['mother_name'] ?? '',
        'motherOccupation' => $row['mother_occupation'] ?? '',
        'motherStatus' => $row['mother_status'] ?? '',
        'brothers' => $row['brothers'] !== null ? (string)$row['brothers'] : '',
        'sisters' => $row['sisters'] !== null ? (string)$row['sisters'] : '',
        'marriedBrothers' => $row['married_brothers'] !== null ? (string)$row['married_brothers'] : '',
        'marriedSisters' => $row['married_sisters'] !== null ? (string)$row['married_sisters'] : '',
        'familyStatus' => $row['family_status'] ?? '',
        'homeType' => $row['home_type'] ?? '',

        'preferredAgeMin' => isset($preferences['age_min']) ? (int)$preferences['age_min'] : 0,
        'preferredAgeMax' => isset($preferences['age_max']) ? (int)$preferences['age_max'] : 0,
        'preferredHeightMin' => isset($preferences['height_min']) ? (float)$preferences['height_min'] : 0,
        'preferredHeightMax' => isset($preferences['height_max']) ? (float)$preferences['height_max'] : 0,
        'preferredReligion' => $preferences['preferred_religion'] ?? '',
        'acceptanceOfKids' => $preferences['acceptance_of_kids'] ?? '',

        'preferredMaritalStatus' => $preferenceValues['marital_status'] ?? [],
        'preferredSects' => $preferenceValues['sect'] ?? [],
        'preferredSunniGroups' => $preferenceValues['sunni_group'] ?? [],
        'preferredSalafiGroups' => $preferenceValues['salafi_group'] ?? [],
        'preferredCaste' => $preferenceValues['caste'] ?? [],
        'preferredSubCaste' => $preferenceValues['sub_caste'] ?? [],
        'preferredEducation' => $preferenceValues['education'] ?? [],
        'preferredEducationSpecific' => $preferenceValues['education_specific'] ?? [],
        'preferredCareerSector' => $preferenceValues['career_sector'] ?? [],
        'preferredLocations' => $preferenceValues['location'] ?? [],

        'preferredFamilyStatus' => $preferenceValues['family_status'] ?? [],
        'preferredPhysicalStatus' => $preferenceValues['physical_status'] ?? [],
        'preferredLocationRadius' => $preferenceValues['location_radius'] ?? [],
        'preferredIncome' => $preferenceValues['income'] ?? [],
        'preferredComplexion' => $preferenceValues['complexion'] ?? [],
        'preferredStar' => $preferenceValues['star'] ?? [],
        'horoscopeRequired' => $preferences['horoscope_required'] ?? '',

        'expectations' => $row['expectations'] ?? '',
        'photos' => $photos,
        'primaryPhoto' => $photos[0] ?? '',

        'registrationCompleted' => (bool)$row['registration_completed'],
        'completionPercentage' => (int)($row['completion_percentage'] ?? 0),
        'homeVerified' => (bool)$row['home_verified'],
        'profileCreatedAt' => $row['created_at'] ?? ''
    ];

    success_response('Profile fetched successfully.', [
        'profile' => $profile,
        'is_interest_accepted' => $isInterestAccepted,
        'interest_status' => $interestStatus,
        'is_shortlisted' => $isShortlisted
    ]);
}


/*
|--------------------------------------------------------------------------
| PROFILE EDITING API
|--------------------------------------------------------------------------
| Authenticated users may edit only their own profile and only before
| home verification. Fields are explicitly whitelisted per section.
*/

function update_profile_section(): never
{
    $user = registration_user();
    $userId = (int)$user['id'];
    $data = request_json();

    if (!isset($data['section']) || !is_string($data['section'])) {
        error_response('Profile section is required.', [], 422);
    }

    $section = trim($data['section']);
    $allowedSections = [
        'physical',
        'contact',
        'work',
        'family',
        'expectations',
        'additional_preferences'
    ];

    if (!in_array($section, $allowedSections, true)) {
        error_response('Invalid profile section.', [], 422);
    }

    $pdo = db();
    $profile = get_existing_profile($pdo, $userId);

    if (!$profile) {
        error_response('Profile not found.', [], 404);
    }

    if ((int)($profile['home_verified'] ?? 0) === 1) {
        error_response('Profile editing is locked after Home Verification.', [], 403);
    }

    unset($data['section'], $data['user_id'], $data['profile_id']);

    $allowedBySection = [
        'physical' => [
            'weight', 'bodyType', 'complexion', 'physicalStatus'
        ],
        'contact' => [
            'secondaryMobile', 'whatsappCountryCode', 'whatsappNumber', 'email'
        ],
        'work' => [
            'collegeUniversity', 'companyName', 'annualIncome',
            'workLocationType', 'workState', 'workDistrict',
            'workCountry', 'workCity'
        ],
        'family' => [
            'fatherName', 'fatherOccupation', 'fatherStatus',
            'motherName', 'motherOccupation', 'motherStatus',
            'brothers', 'sisters', 'marriedBrothers', 'marriedSisters',
            'familyStatus', 'homeType'
        ],
        'expectations' => [
            'expectations'
        ],
        'additional_preferences' => [
            'preferredFamilyStatus', 'preferredPhysicalStatus',
            'preferredLocationRadius', 'preferredIncome',
            'preferredComplexion', 'horoscopeRequired', 'preferredStar'
        ]
    ];

    foreach (array_keys($data) as $field) {
        if (!in_array($field, $allowedBySection[$section], true)) {
            error_response('Invalid field supplied for this profile section.', [
                'field' => $field
            ], 422);
        }
    }

    if ($section === 'additional_preferences') {
        update_additional_preferences_section($pdo, $userId, $data);
    }

    $maps = [
        'physical' => [
            'weight' => 'weight',
            'bodyType' => 'body_type',
            'complexion' => 'complexion',
            'physicalStatus' => 'physical_status'
        ],
        'contact' => [
            'secondaryMobile' => 'secondary_mobile',
            'whatsappCountryCode' => 'whatsapp_country_code',
            'whatsappNumber' => 'whatsapp_number',
            'email' => 'email'
        ],
        'work' => [
            'collegeUniversity' => 'college_university',
            'companyName' => 'company_name',
            'annualIncome' => 'annual_income',
            'workLocationType' => 'work_location_type',
            'workState' => 'work_state',
            'workDistrict' => 'work_district',
            'workCountry' => 'work_country',
            'workCity' => 'work_city'
        ],
        'family' => [
            'fatherName' => 'father_name',
            'fatherOccupation' => 'father_occupation',
            'fatherStatus' => 'father_status',
            'motherName' => 'mother_name',
            'motherOccupation' => 'mother_occupation',
            'motherStatus' => 'mother_status',
            'brothers' => 'brothers',
            'sisters' => 'sisters',
            'marriedBrothers' => 'married_brothers',
            'marriedSisters' => 'married_sisters',
            'familyStatus' => 'family_status',
            'homeType' => 'home_type'
        ],
        'expectations' => [
            'expectations' => 'expectations'
        ]
    ];

    if (!isset($maps[$section])) {
        success_response('Profile section updated successfully.', [
            'section' => $section
        ]);
    }

    $set = [];
    $params = [];

    foreach ($maps[$section] as $input => $column) {
        if (!array_key_exists($input, $data)) {
            continue;
        }

        $value = $data[$input];

        if ($input === 'weight') {
            if ($value === null || $value === '') {
                $value = null;
            } elseif (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
                error_response('Weight must be a valid number.', [], 422);
            } else {
                $value = (float)$value;
                if ($value < 20 || $value > 250) {
                    error_response('Weight must be between 20 and 250 kg.', [], 422);
                }
            }
        } elseif (in_array($input, ['brothers','sisters','marriedBrothers','marriedSisters'], true)) {
            if ($value === null || $value === '') {
                $value = null;
            } elseif (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
                error_response('Sibling count must be a whole number.', [], 422);
            } else {
                $value = (int)$value;
                if ($value < 0 || $value > 20) {
                    error_response('Sibling count must be between 0 and 20.', [], 422);
                }
            }
        } else {
            if ($value !== null && !is_string($value)) {
                error_response('Invalid value type supplied.', ['field' => $input], 422);
            }
            if (is_string($value)) {
                $value = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '');
            }
        }

        $set[] = "{$column} = :{$input}";
        $params[":{$input}"] = $value;
    }

    if (!$set) {
        error_response('No editable fields were supplied.', [], 422);
    }

    validate_profile_edit_section_values($section, $data, $profile);

    $params[':user_id'] = $userId;
    $sql = 'UPDATE profiles SET ' . implode(', ', $set) . ' WHERE user_id = :user_id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    success_response('Profile section updated successfully.', [
        'section' => $section,
        'updated_fields' => array_keys(array_intersect_key($maps[$section], $data))
    ]);
}

function validate_profile_edit_section_values(string $section, array $data, array $profile): void
{
    if ($section === 'physical') {
        if (isset($data['bodyType']) && $data['bodyType'] !== '' && !in_array($data['bodyType'], ['Slim','Average','Athletic','Heavy'], true)) {
            error_response('Invalid body type.', [], 422);
        }
        if (isset($data['complexion']) && $data['complexion'] !== '' && !in_array($data['complexion'], ['Very Fair','Fair','Wheatish','Medium','Dusky','Dark'], true)) {
            error_response('Invalid complexion.', [], 422);
        }
        if (isset($data['physicalStatus']) && $data['physicalStatus'] !== '' && !in_array($data['physicalStatus'], ['Normal','Physically Challenged','Other'], true)) {
            error_response('Invalid physical status.', [], 422);
        }
    }

    if ($section === 'contact') {
        if (isset($data['secondaryMobile']) && $data['secondaryMobile'] !== '' && !preg_match('/^\+?[0-9]{7,15}$/', $data['secondaryMobile'])) {
            error_response('Invalid secondary mobile number.', [], 422);
        }
        if (isset($data['whatsappCountryCode']) && !preg_match('/^\+[1-9][0-9]{0,3}$/', $data['whatsappCountryCode'])) {
            error_response('Invalid WhatsApp country code.', [], 422);
        }
        if (isset($data['whatsappNumber']) && !preg_match('/^\+?[0-9]{7,15}$/', $data['whatsappNumber'])) {
            error_response('Invalid WhatsApp number.', [], 422);
        }
        if (isset($data['email']) && $data['email'] !== '' && (strlen($data['email']) > 255 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))) {
            error_response('Invalid email address.', [], 422);
        }
    }

    if ($section === 'work') {
        foreach (['collegeUniversity','companyName'] as $field) {
            if (isset($data[$field]) && strlen($data[$field]) > 200) {
                error_response('Work field is too long.', ['field' => $field], 422);
            }
        }
        if (isset($data['annualIncome']) && !in_array($data['annualIncome'], ['', 'Below ₹2 Lakh','₹2 - ₹5 Lakh','₹5 - ₹10 Lakh','₹10 - ₹15 Lakh','₹15 - ₹25 Lakh','Above ₹25 Lakh'], true)) {
            error_response('Invalid annual income option.', [], 422);
        }
        if (isset($data['workLocationType']) && !in_array($data['workLocationType'], ['india_same_state','india_other_state','outside_india'], true)) {
            error_response('Invalid work location type.', [], 422);
        }
        foreach (['workState','workDistrict','workCountry','workCity'] as $field) {
            if (isset($data[$field]) && strlen($data[$field]) > 100) {
                error_response('Work location field is too long.', ['field' => $field], 422);
            }
        }
    }

    if ($section === 'family') {
        foreach (['fatherName','motherName'] as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && !preg_match("/^[A-Za-zÀ-ÿ.'-]+(?:\s+[A-Za-zÀ-ÿ.'-]+)*$/u", $data[$field])) {
                error_response('Invalid parent name.', ['field' => $field], 422);
            }
            if (isset($data[$field]) && strlen($data[$field]) > 100) {
                error_response('Parent name is too long.', ['field' => $field], 422);
            }
        }
        foreach (['fatherOccupation','motherOccupation'] as $field) {
            if (isset($data[$field]) && strlen($data[$field]) > 100) {
                error_response('Occupation is too long.', ['field' => $field], 422);
            }
        }
        foreach (['fatherStatus','motherStatus'] as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && !in_array($data[$field], ['living','passed_away'], true)) {
                error_response('Invalid parent status.', ['field' => $field], 422);
            }
        }
        if (isset($data['marriedBrothers'], $data['brothers']) && $data['marriedBrothers'] !== null && $data['brothers'] !== null && (int)$data['marriedBrothers'] > (int)$data['brothers']) {
            error_response('Married brothers cannot exceed total brothers.', [], 422);
        }
        if (isset($data['marriedSisters'], $data['sisters']) && $data['marriedSisters'] !== null && $data['marriedSisters'] !== null && (int)$data['marriedSisters'] > (int)$data['sisters']) {
            error_response('Married sisters cannot exceed total sisters.', [], 422);
        }
        if (isset($data['familyStatus']) && !in_array($data['familyStatus'], ['Lower Middle Class','Middle Class','Upper Middle Class','Affluent'], true)) {
            error_response('Invalid family status.', [], 422);
        }
        if (isset($data['homeType']) && $data['homeType'] !== '' && !in_array($data['homeType'], ['Own House','Rented House','Family House','Other'], true)) {
            error_response('Invalid home type.', [], 422);
        }
    }

    if ($section === 'expectations') {
        if (isset($data['expectations'])) {
            if (!is_string($data['expectations']) || strlen($data['expectations']) > 1000) {
                error_response('Expectations must be 1000 characters or less.', [], 422);
            }
            if (preg_match('/(?:https?:\/\/|www\.|@|\+?[0-9][0-9\s().-]{6,})/i', $data['expectations'])) {
                error_response('Contact information is not allowed in expectations.', [], 422);
            }
        }
    }
}

function update_additional_preferences_section(PDO $pdo, int $userId, array $data): never
{
    $allowed = [
        'preferredFamilyStatus' => ['any','Lower Middle Class','Middle Class','Upper Middle Class','Affluent'],
        'preferredPhysicalStatus' => ['any','Normal','Physically Challenged','Other'],
        'preferredLocationRadius' => ['any','Within 10 km','Within 25 km','Within 50 km','Within 100 km','Anywhere in Kerala'],
        'preferredIncome' => ['any','Below ₹2 Lakh','₹2 - ₹5 Lakh','₹5 - ₹10 Lakh','₹10 - ₹15 Lakh','₹15 - ₹25 Lakh','Above ₹25 Lakh'],
        'preferredComplexion' => ['any','Very Fair','Fair','Wheatish','Medium','Dusky','Dark'],
        'preferredStar' => NAKSHATRA_OPTIONS
    ];

    $types = [
        'preferredFamilyStatus' => 'family_status',
        'preferredPhysicalStatus' => 'physical_status',
        'preferredLocationRadius' => 'location_radius',
        'preferredIncome' => 'income',
        'preferredComplexion' => 'complexion',
        'preferredStar' => 'star'
    ];

    $pdo->beginTransaction();

    try {
        foreach ($types as $field => $type) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            if (!is_array($data[$field])) {
                error_response('Preference values must be arrays.', ['field' => $field], 422);
            }
            $values = array_values(array_unique($data[$field]));
            if (count($values) > 30) {
                error_response('Too many preference values.', ['field' => $field], 422);
            }
            foreach ($values as $value) {
                if (!is_string($value) || !in_array($value, $allowed[$field], true)) {
                    error_response('Invalid preference value.', ['field' => $field], 422);
                }
            }
            if (in_array('any', $values, true) && count($values) > 1) {
                error_response('"any" cannot be combined with other preference values.', ['field' => $field], 422);
            }

            $stmt = $pdo->prepare('DELETE FROM preference_values WHERE user_id = ? AND preference_type = ?');
            $stmt->execute([$userId, $type]);

            if ($values) {
                $insert = $pdo->prepare('INSERT INTO preference_values (user_id, preference_type, value) VALUES (?, ?, ?)');
                foreach ($values as $value) {
                    $insert->execute([$userId, $type, $value]);
                }
            }
        }

        if (array_key_exists('horoscopeRequired', $data)) {
            if (!is_string($data['horoscopeRequired']) || !in_array($data['horoscopeRequired'], ['', 'yes', 'no'], true)) {
                error_response('Invalid horoscope preference.', [], 422);
            }
            $stmt = $pdo->prepare('UPDATE profile_preferences SET horoscope_required = ? WHERE user_id = ?');
            $stmt->execute([$data['horoscopeRequired'] ?: null, $userId]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    success_response('Profile section updated successfully.', [
        'section' => 'additional_preferences'
    ]);
}


function complete_registration(): never
{
    $user = registration_user();

    $userId = (int)$user['id'];

    $pdo = db();

    try {

        $profile = get_existing_profile(
            $pdo,
            $userId
        );

        if (!$profile) {

            error_response(
                'Profile details are not saved yet.',
                [],
                422
            );
        }

        if (
            (int)($profile['completion_percentage'] ?? 0)
            < 95
        ) {

            error_response(
                'Please complete all registration sections first.',
                [
                    'completion_percentage' =>
                        (int)$profile['completion_percentage']
                ],
                422
            );
        }

        $pdo->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Mark profile completed
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare(
            "UPDATE profiles
             SET
                registration_completed = 1,
                profile_status = 'new',
                completion_percentage = 100,
                home_verified = 0
             WHERE user_id = ?"
        );

        $stmt->execute([
            $userId
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activate user
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare(
            "UPDATE users
             SET account_status = 'active'
             WHERE id = ?"
        );

        $stmt->execute([
            $userId
        ]);

        $pdo->commit();

        success_response(
            'Registration completed successfully.',
            [
                'member_id' =>
                    $user['member_id'],

                'account_status' =>
                    'active',

                'registration_completed' =>
                    true
            ]
        );

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_response(
            'Unable to complete registration.',
            [],
            500
        );
    }

   

}
