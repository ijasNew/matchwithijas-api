<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


/*
|--------------------------------------------------------------------------
| SHARED HELPER: RESOLVE user_id FROM member_id
|--------------------------------------------------------------------------
*/

function resolve_user_id_by_member_id(PDO $pdo, string $memberId): ?int
{
    $stmt = $pdo->prepare(
        'SELECT id
         FROM users
         WHERE member_id = ?
         LIMIT 1'
    );

    $stmt->execute([$memberId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    return (int) $row['id'];
}


/*
|--------------------------------------------------------------------------
| SHARED HELPER: FORMAT AN INTEREST ROW FOR THE FRONTEND
|--------------------------------------------------------------------------
*/

function format_interest_row(array $row): array
{
    $age = null;

    if (!empty($row['date_of_birth'])) {

        $birthDate = new DateTime($row['date_of_birth']);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;
    }

    return [
        'interestId' => (string) $row['id'],
        'memberId' => (string) $row['member_id'],
        'name' => (string) ($row['full_name'] ?? ''),
        'age' => $age,
        'maritalStatus' => (string) ($row['marital_status'] ?? ''),
        'district' => (string) ($row['district'] ?? ''),
        'religion' => (string) ($row['religion'] ?? ''),
        'education' => (string) ($row['highest_education'] ?? ''),
        'photoUrl' => $row['photo_path'] ?? null,
        'verified' => ((int) ($row['home_verified'] ?? 0)) === 1,
        'status' => (string) $row['status'],
        'createdAt' => (string) $row['created_at']
    ];
}


/*
|--------------------------------------------------------------------------
| PROFILE JOIN FRAGMENT (reused by received / sent queries)
|--------------------------------------------------------------------------
*/

const INTEREST_PROFILE_JOIN_SQL = '
    INNER JOIN users u ON u.id = %s
    LEFT JOIN profiles p ON p.user_id = %s
    LEFT JOIN (
        SELECT pp.user_id, pp.file_path
        FROM profile_photos pp
        WHERE pp.status = "active" AND pp.is_primary = 1
    ) photo ON photo.user_id = %s
';


/*
|--------------------------------------------------------------------------
| SEND INTEREST
|--------------------------------------------------------------------------
|
| POST /interests/send
| Body: { "member_id": "F1024" }
|
*/

function send_interest(): never
{
    $user = current_user(true);
    $senderId = (int) $user['id'];

    $pdo = db();
    $data = request_json();

    $receiverMemberId = trim((string) ($data['member_id'] ?? ''));

    if ($receiverMemberId === '') {
        error_response('Please select a profile to send interest to.', [], 422);
    }

    $receiverId = resolve_user_id_by_member_id($pdo, $receiverMemberId);

    if ($receiverId === null) {
        error_response('Profile not found.', [], 404);
    }

    if ($receiverId === $senderId) {
        error_response('You cannot send interest to your own profile.', [], 422);
    }

    /*
     * FINAL BUSINESS RULES
     * ---------------------
     * 1. pending   -> cannot send another interest in either direction.
     * 2. accepted  -> terminal; no new interest.
     * 3. declined  -> terminal; NO SEND AGAIN.
     * 4. cancelled -> inactive; a new interest may be sent.
     *
     * Direction matters:
     * A -> B cancelled does NOT block B -> A.
     */

    try {
        $pdo->beginTransaction();

        // Lock both possible directional records for this user pair.
        $stmt = $pdo->prepare(
            'SELECT id, sender_user_id, receiver_user_id, status
             FROM interests
             WHERE (sender_user_id = ? AND receiver_user_id = ?)
                OR (sender_user_id = ? AND receiver_user_id = ?)
             FOR UPDATE'
        );

        $stmt->execute([
            $senderId,
            $receiverId,
            $receiverId,
            $senderId
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $outgoing = null;
        $incoming = null;

        foreach ($rows as $row) {
            if ((int) $row['sender_user_id'] === $senderId) {
                $outgoing = $row;
            } else {
                $incoming = $row;
            }
        }

        // ---------------------------------------------------------
        // EXISTING OUTGOING INTEREST: A -> B
        // ---------------------------------------------------------
        if ($outgoing !== null) {
            $status = (string) $outgoing['status'];

            if ($status === 'pending') {
                $pdo->rollBack();
                error_response(
                    'You have already sent an interest to this profile.',
                    [],
                    409
                );
            }

            if ($status === 'accepted') {
                $pdo->rollBack();
                error_response(
                    'This interest has already been accepted.',
                    [],
                    409
                );
            }

            if ($status === 'declined') {
                $pdo->rollBack();
                error_response(
                    'This interest was declined. You cannot send interest again.',
                    [],
                    409
                );
            }

            // cancelled = inactive. Reuse the same directional row.
            if ($status === 'cancelled') {
                $update = $pdo->prepare(
                    "UPDATE interests
                     SET status = 'pending',
                         responded_at = NULL,
                         updated_at = NOW()
                     WHERE id = ?
                       AND sender_user_id = ?
                       AND receiver_user_id = ?
                       AND status = 'cancelled'"
                );

                $update->execute([
                    $outgoing['id'],
                    $senderId,
                    $receiverId
                ]);

                if ($update->rowCount() !== 1) {
                    $pdo->rollBack();
                    error_response('Unable to send interest. Please try again.', [], 409);
                }

                $pdo->commit();
                success_response('Interest sent successfully.');
            }
        }

        // ---------------------------------------------------------
        // EXISTING INCOMING INTEREST: B -> A
        // ---------------------------------------------------------
        if ($incoming !== null) {
            $status = (string) $incoming['status'];

            if ($status === 'pending') {
                $pdo->rollBack();
                error_response(
                    'This profile has already sent you an interest. Please respond to that interest first.',
                    [],
                    409
                );
            }

            if ($status === 'accepted') {
                $pdo->rollBack();
                error_response(
                    'This interest has already been accepted.',
                    [],
                    409
                );
            }

            if ($status === 'declined') {
                $pdo->rollBack();
                error_response(
                    'This interest was declined. A new interest cannot be sent in this relationship.',
                    [],
                    409
                );
            }

            // cancelled = inactive, so it does not block a new A -> B request.
        }

        // ---------------------------------------------------------
        // NEW INTEREST
        // ---------------------------------------------------------
        $insert = $pdo->prepare(
            'INSERT INTO interests
                (sender_user_id, receiver_user_id, status)
             VALUES
                (?, ?, "pending")'
        );

        $insert->execute([
            $senderId,
            $receiverId
        ]);

        $pdo->commit();

        success_response('Interest sent successfully.');

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Unique directional pair is protected by the DB schema.
        if ($e instanceof PDOException && (string) $e->getCode() === '23000') {
            error_response(
                'Unable to send interest because the relationship changed. Please refresh and try again.',
                [],
                409
            );
        }

        error_response('Unable to send interest. Please try again.', [], 500);
    }
}

/*
|--------------------------------------------------------------------------
| GET RECEIVED INTERESTS
|--------------------------------------------------------------------------
|
| GET /interests/received
|
*/

function get_received_interests(): never
{
    $user = current_user(true);
    $userId = (int) $user['id'];

    $pdo = db();

    $sql = '
        SELECT
            i.id,
            i.status,
            i.created_at,
            u.member_id,
            p.full_name,
            p.marital_status,
            p.date_of_birth,
            p.district,
            p.religion,
            p.highest_education,
            p.home_verified,
            photo.file_path AS photo_path
        FROM interests i
        INNER JOIN users u ON u.id = i.sender_user_id
        LEFT JOIN profiles p ON p.user_id = i.sender_user_id
        LEFT JOIN (
            SELECT pp.user_id, pp.file_path
            FROM profile_photos pp
            WHERE pp.status = "active" AND pp.is_primary = 1
        ) photo ON photo.user_id = i.sender_user_id
        WHERE i.receiver_user_id = ?
          AND i.status <> "cancelled"
        ORDER BY i.created_at DESC
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $interests = array_map('format_interest_row', $rows);

    success_response(
        'Received interests fetched successfully.',
        [
            'interests' => $interests,
            'count' => count($interests)
        ]
    );
}


/*
|--------------------------------------------------------------------------
| GET SENT INTERESTS
|--------------------------------------------------------------------------
|
| GET /interests/sent
|
*/

function get_sent_interests(): never
{
    $user = current_user(true);
    $userId = (int) $user['id'];

    $pdo = db();

    $sql = '
        SELECT
            i.id,
            i.status,
            i.created_at,
            u.member_id,
            p.full_name,
            p.marital_status,
            p.date_of_birth,
            p.district,
            p.religion,
            p.highest_education,
            p.home_verified,
            photo.file_path AS photo_path
        FROM interests i
        INNER JOIN users u ON u.id = i.receiver_user_id
        LEFT JOIN profiles p ON p.user_id = i.receiver_user_id
        LEFT JOIN (
            SELECT pp.user_id, pp.file_path
            FROM profile_photos pp
            WHERE pp.status = "active" AND pp.is_primary = 1
        ) photo ON photo.user_id = i.receiver_user_id
        WHERE i.sender_user_id = ?
          AND i.status <> "cancelled"
        ORDER BY i.created_at DESC
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $interests = array_map('format_interest_row', $rows);

    success_response(
        'Sent interests fetched successfully.',
        [
            'interests' => $interests,
            'count' => count($interests)
        ]
    );
}


/*
|--------------------------------------------------------------------------
| RESPOND TO INTEREST (ACCEPT / DECLINE)
|--------------------------------------------------------------------------
|
| POST /interests/respond
| Body: { "interest_id": "12", "action": "accept" | "decline" }
|
*/

function respond_interest(): never
{
    $user = current_user(true);
    $userId = (int) $user['id'];

    $pdo = db();

    $data = request_json();

    $interestId = (int) ($data['interest_id'] ?? 0);
    $action = trim((string) ($data['action'] ?? ''));

    if ($interestId <= 0) {
        error_response('Invalid interest.', [], 422);
    }

    if (!in_array($action, ['accept', 'decline'], true)) {
        error_response('Invalid action.', [], 422);
    }

    $stmt = $pdo->prepare(
        'SELECT id, receiver_user_id, status
         FROM interests
         WHERE id = ?
         LIMIT 1'
    );

    $stmt->execute([$interestId]);

    $interest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$interest) {
        error_response('Interest not found.', [], 404);
    }

    if ((int) $interest['receiver_user_id'] !== $userId) {
        error_response('You are not allowed to respond to this interest.', [], 403);
    }

    if ($interest['status'] !== 'pending') {
        error_response('This interest has already been responded to.', [], 409);
    }

    $newStatus = $action === 'accept' ? 'accepted' : 'declined';

    $update = $pdo->prepare(
        'UPDATE interests
         SET status = ?, responded_at = NOW(), updated_at = NOW()
         WHERE id = ?'
    );

    $update->execute([$newStatus, $interestId]);

    success_response(
        $action === 'accept'
            ? 'Interest accepted successfully.'
            : 'Interest declined.'
    );
}


/*
|--------------------------------------------------------------------------
| CANCEL A SENT INTEREST
|--------------------------------------------------------------------------
|
| POST /interests/cancel
| Body: { "interest_id": "12" }
|
*/

function cancel_interest(): never
{
    $user = current_user(true);
    $userId = (int) $user['id'];

    $pdo = db();

    $data = request_json();

    $interestId = (int) ($data['interest_id'] ?? 0);

    if ($interestId <= 0) {
        error_response('Invalid interest.', [], 422);
    }

    $stmt = $pdo->prepare(
        'SELECT id, sender_user_id, status
         FROM interests
         WHERE id = ?
         LIMIT 1'
    );

    $stmt->execute([$interestId]);

    $interest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$interest) {
        error_response('Interest not found.', [], 404);
    }

    if ((int) $interest['sender_user_id'] !== $userId) {
        error_response('You are not allowed to cancel this interest.', [], 403);
    }

    if ($interest['status'] !== 'pending') {
        error_response('Only pending interests can be cancelled.', [], 409);
    }

    $update = $pdo->prepare(
        "UPDATE interests
         SET status = 'cancelled', updated_at = NOW()
         WHERE id = ?"
    );

    $update->execute([$interestId]);

    success_response('Interest cancelled.');
}
