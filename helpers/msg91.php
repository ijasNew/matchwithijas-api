<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function verify_msg91_access_token(string $accessToken): array
{
    $accessToken = trim($accessToken);

    if ($accessToken === '') {
        throw new RuntimeException('MSG91 access token is required.');
    }

    $authKey = trim((string)(getenv('MSG91_AUTH_KEY') ?: ''));

    $secretFile = __DIR__ . '/../config/msg91-secret.php';
    if ($authKey === '' && is_file($secretFile)) {
        $fileKey = require $secretFile;
        if (is_string($fileKey)) {
            $authKey = trim($fileKey);
        }
    }

    if ($authKey === '' || str_contains($authKey, 'PASTE_MSG91')) {
        throw new RuntimeException('MSG91 server AuthKey is not configured.');
    }

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://control.msg91.com/api/v5/widget/verifyAccessToken',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'authkey' => $authKey,
            'access-token' => $accessToken,
        ], JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $raw = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($raw === false || $curlError !== '') {
        throw new RuntimeException('Unable to contact MSG91 for OTP verification.');
    }

    $body = json_decode($raw, true);

    if (!is_array($body)) {
        throw new RuntimeException('Invalid response received from MSG91.');
    }

    $type = strtolower((string)($body['type'] ?? ''));
    $status = strtolower((string)($body['status'] ?? ''));
    $success = ($body['success'] ?? false) === true;

    if ($httpCode < 200 || $httpCode >= 300 || (!$success && $type !== 'success' && $status !== 'success')) {
        throw new RuntimeException(
            (string)($body['message'] ?? $body['error'] ?? 'MSG91 OTP verification failed.')
        );
    }

    return $body;
}

function normalize_msg91_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';

    if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
        return substr($digits, -10);
    }

    if (strlen($digits) === 10) {
        return $digits;
    }

    return '';
}

function collect_msg91_phone_candidates(mixed $value, array &$candidates = []): array
{
    if (!is_array($value)) {
        return $candidates;
    }

    foreach ($value as $key => $item) {
        $normalizedKey = strtolower(str_replace(['-', ' '], '_', (string)$key));

        if (in_array($normalizedKey, [
            'identifier',
            'mobile',
            'mobile_number',
            'phone',
            'phone_number',
            'contact_number',
        ], true) && is_scalar($item)) {
            $candidate = normalize_msg91_phone((string)$item);
            if ($candidate !== '') {
                $candidates[] = $candidate;
            }
        }

        if (is_array($item)) {
            collect_msg91_phone_candidates($item, $candidates);
        }
    }

    return $candidates;
}

function jwt_payload(string $jwt): array
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return [];

    $payload = strtr($parts[1], '-_', '+/');
    $padding = strlen($payload) % 4;
    if ($padding > 0) {
        $payload .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode($payload, true);
    if ($decoded === false) return [];

    $data = json_decode($decoded, true);
    return is_array($data) ? $data : [];
}

function assert_msg91_token_matches_phone(array $verificationResponse, string $accessToken, string $expectedPhone): void
{
    $expected = normalize_msg91_phone($expectedPhone);
    if ($expected === '') {
        throw new RuntimeException('Invalid mobile number.');
    }

    $candidates = collect_msg91_phone_candidates($verificationResponse);
    $candidates = collect_msg91_phone_candidates(jwt_payload($accessToken), $candidates);

    foreach (array_unique($candidates) as $candidate) {
        if ($candidate === $expected) {
            return;
        }
    }

    throw new RuntimeException('The OTP verification does not match the mobile number entered on MWI.');
}
