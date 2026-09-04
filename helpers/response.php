<?php
declare(strict_types=1);

function json_response(array $body, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function success_response(string $message, mixed $data = null, int $status = 200): never
{
    json_response(['success' => true, 'message' => $message, 'data' => $data], $status);
}

function error_response(string $message, array $errors = [], int $status = 400): never
{
    json_response(['success' => false, 'message' => $message, 'errors' => $errors], $status);
}

function request_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) error_response('Invalid JSON request.', [], 400);
    return $data;
}
