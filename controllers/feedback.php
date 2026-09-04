<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';


function submit_feedback(): never
{
    // Logged-in user required
    $user = current_user(true);

    $userId = (int)$user['id'];

    $data = request_json();

    $feedbackType =
        trim((string)($data['feedback_type'] ?? ''));

    $message =
        trim((string)($data['message'] ?? ''));


    // =========================
    // VALIDATE TYPE
    // =========================

    $allowedTypes = [
        'suggestion',
        'problem',
        'confusing',
        'feature',
        'other'
    ];

    if (
        !in_array(
            $feedbackType,
            $allowedTypes,
            true
        )
    ) {
        error_response(
            'Please select a valid feedback type.',
            [],
            422
        );
    }


    // =========================
    // VALIDATE MESSAGE
    // =========================

    if ($message === '') {
        error_response(
            'Please enter your feedback message.',
            [],
            422
        );
    }


    if (mb_strlen($message) > 5000) {
        error_response(
            'Feedback message is too long.',
            [],
            422
        );
    }


    // =========================
    // INSERT
    // =========================

    try {

        $stmt = db()->prepare(
            'INSERT INTO feedback
            (
                user_id,
                feedback_type,
                message
            )
            VALUES
            (
                ?,
                ?,
                ?
            )'
        );


        $stmt->execute([
            $userId,
            $feedbackType,
            $message
        ]);


        success_response(
            'Your feedback has been submitted successfully.'
        );


    } catch (Throwable $e) {

        error_response(
            'Unable to submit feedback. Please try again.',
            [],
            500
        );
    }
}