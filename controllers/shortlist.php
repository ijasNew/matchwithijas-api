<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/interests.php'; // reuses resolve_user_id_by_member_id()
require_once __DIR__ . '/photo-security.php';

/*
|--------------------------------------------------------------------------
| ADD TO SHORTLIST
|--------------------------------------------------------------------------
|
| POST /shortlist/add
| Body: { "member_id": "F1024" }
|
*/

function add_shortlist(): never
{
    $user = current_user(true);
    $userId = (int) $user['id'];

    $pdo = db();

    $data = request_json();

    $targetMemberId = trim((string) ($data['member_id'] ?? ''));

    if ($targetMemberId === '') {
        error_response('Please select a profile to shortlist.', [], 422);
    }

    $targetUserId = resolve_user_id_by_member_id($pdo, $targetMemberId);

    if ($targetUserId === null) {
        error_response('Profile not found.', [], 404);
    }

    if ($targetUserId === $userId) {
        error_response('You cannot shortlist your own profile.', [], 422);
    }

    try {

        $stmt = $pdo->prepare(
            'INSERT INTO shortlists (user_id, shortlisted_user_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE user_id = user_id'
        );

        $stmt->execute([$userId, $targetUserId]);

        success_response('Profile added to shortlist.');

    } catch (Throwable $e) {

        error_response('Unable to shortlist this profile. Please try again.', [], 500);
    }
}


/*
|--------------------------------------------------------------------------
| REMOVE FROM SHORTLIST
|--------------------------------------------------------------------------
|
| POST /shortlist/remove
| Body: { "member_id": "F1024" }
|
*/

function remove_shortlist(): never
{
    $user = current_user(true);
    $userId = (int) $user['id'];

    $pdo = db();

    $data = request_json();

    $targetMemberId = trim((string) ($data['member_id'] ?? ''));

    if ($targetMemberId === '') {
        error_response('Please select a profile to remove.', [], 422);
    }

    $targetUserId = resolve_user_id_by_member_id($pdo, $targetMemberId);

    if ($targetUserId === null) {
        error_response('Profile not found.', [], 404);
    }

    $stmt = $pdo->prepare(
        'DELETE FROM shortlists
         WHERE user_id = ? AND shortlisted_user_id = ?'
    );

    $stmt->execute([$userId, $targetUserId]);

    success_response('Profile removed from shortlist.');
}


/*
|--------------------------------------------------------------------------
| GET SHORTLIST
|--------------------------------------------------------------------------
|
| GET /shortlist
|
| Returns full shortlisted profile cards AND a plain list of
| shortlisted member IDs (handy for the frontend to mark
| "already shortlisted" stars on the matching-profiles grid).
|
*/
function get_shortlist(): never
{
    $user = current_user(true);
    $userId = (int) $user['id'];

    $pdo = db();

    $sql = <<<'SQL'
        SELECT
            s.shortlisted_user_id,
            s.created_at,
            u.member_id,
            p.full_name,
            p.marital_status,
            p.date_of_birth,
            p.district,
            p.religion,
            p.highest_education,
            p.home_verified,
            photo.photo_id,
            photo.file_path AS photo_path
        FROM shortlists s
        INNER JOIN users u
            ON u.id = s.shortlisted_user_id
        LEFT JOIN profiles p
            ON p.user_id = s.shortlisted_user_id
        LEFT JOIN (
            SELECT
                user_id,
                id AS photo_id,
                file_path
            FROM (
                SELECT
                    pp.*,
                    ROW_NUMBER() OVER (
                        PARTITION BY user_id
                        ORDER BY
                            is_primary DESC,
                            display_order ASC,
                            id ASC
                    ) AS rn
                FROM profile_photos pp
                WHERE pp.status = 'active'
            ) ranked
            WHERE rn = 1
        ) photo
            ON photo.user_id = s.shortlisted_user_id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $viewerHomeVerified = false;

    $verificationStmt = $pdo->prepare(
        'SELECT home_verified
         FROM profiles
         WHERE user_id = ?
         LIMIT 1'
    );

    $verificationStmt->execute([$userId]);

    $viewerHomeVerified =
        ((int) ($verificationStmt->fetchColumn() ?: 0)) === 1;

    $profiles = [];
    $memberIds = [];

    foreach ($rows as $row) {

        $age = null;

        if (!empty($row['date_of_birth'])) {
            $birthDate = new DateTime($row['date_of_birth']);
            $today = new DateTime();
            $age = $birthDate->diff($today)->y;
        }

        $profiles[] = [
            'memberId' => (string) $row['member_id'],
            'name' => (string) ($row['full_name'] ?? ''),
            'age' => $age,
            'maritalStatus' => (string) ($row['marital_status'] ?? ''),
            'district' => (string) ($row['district'] ?? ''),
            'religion' => (string) ($row['religion'] ?? ''),
            'education' => (string) ($row['highest_education'] ?? ''),

            // SECURE PHOTO URL
            'photoUrl' => !empty($row['photo_id'])
                ? photo_url_for_viewer((int) $row['photo_id'])
                : null,

            'verified' =>
                ((int) ($row['home_verified'] ?? 0)) === 1,

            'shortlistedAt' =>
                (string) $row['created_at']
        ];

        $memberIds[] = (string) $row['member_id'];
    }

    success_response(
        'Shortlisted profiles fetched successfully.',
        [
            'profiles' => $profiles,
            'memberIds' => $memberIds,
            'count' => count($profiles)
        ]
    );
}
