<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/response.php';

function bearer_token(): ?string
{
    $header = '';

    // Apache / PHP-FPM
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['HTTP_AUTHORIZATION'];
    }

    // Apache rewrite fallback
    if (!$header && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    // getallheaders() fallback
    if (!$header && function_exists('getallheaders')) {
        $headers = getallheaders();

        $header =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? '';
    }

    if (
        preg_match(
            '/^Bearer\s+(.+)$/i',
            trim($header),
            $matches
        )
    ) {
        return trim($matches[1]);
    }

    return null;
}

function issue_token(int $userId): string
{
    $plain = bin2hex(random_bytes(32));
    $hash = hash('sha256', $plain);
    $expires = date('Y-m-d H:i:s', time() + TOKEN_TTL_SECONDS);

    $stmt = db()->prepare('INSERT INTO auth_sessions (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $hash, $expires]);
    return $plain;
}

function current_user(bool $required = true): ?array
{
    $token = bearer_token();
    if (!$token) {
        if ($required) error_response('Authentication required.', [], 401);
        return null;
    }

    $hash = hash('sha256', $token);
    $stmt = db()->prepare(
        'SELECT u.* FROM auth_sessions s JOIN users u ON u.id = s.user_id
         WHERE s.token_hash = ? AND s.revoked_at IS NULL AND s.expires_at > NOW() LIMIT 1'
    );
    $stmt->execute([$hash]);
    $user = $stmt->fetch();

    if (!$user) {
        if ($required) error_response('Invalid or expired authentication token.', [], 401);
        return null;
    }

    db()->prepare('UPDATE auth_sessions SET last_used_at = NOW() WHERE token_hash = ?')->execute([$hash]);
    return $user;
}

function revoke_current_token(): void
{
    $token = bearer_token();
    if (!$token) return;
    db()->prepare('UPDATE auth_sessions SET revoked_at = NOW() WHERE token_hash = ?')->execute([hash('sha256', $token)]);
}
