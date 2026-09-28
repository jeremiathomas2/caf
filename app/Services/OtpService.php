<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Issues and verifies the six-digit codes used as the management system's
 * second factor, and the four-digit codes used to verify a phone number.
 *
 * Codes are hashed with the app key, so a stored secret is useless on its own.
 */
class OtpService
{
    public const EXPIRY_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    /**
     * Generate a numeric code of the given length.
     */
    public function generate(int $length = 6): string
    {
        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Build the hashed payload stored in the session.
     *
     * @return array{code: string, hash: string, attempts: int, expires_at: int}
     */
    public function issue(int $length = 6): array
    {
        $code = $this->generate($length);

        return [
            'code' => $code,
            'hash' => $this->hash($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES)->getTimestamp(),
        ];
    }

    /**
     * Compare a submitted code against a payload, tracking failed attempts.
     *
     * @param  array{code?: string, hash?: string, attempts?: int, expires_at?: int}  $payload
     * @return array{valid: bool, reason: null|'expired'|'mismatch'|'attempts', attempts: int}
     */
    public function verify(string $code, array $payload): array
    {
        $attempts = (int) ($payload['attempts'] ?? 0);

        if (($payload['expires_at'] ?? 0) < now()->getTimestamp()) {
            return ['valid' => false, 'reason' => 'expired', 'attempts' => $attempts];
        }

        if ($attempts >= self::MAX_ATTEMPTS) {
            return ['valid' => false, 'reason' => 'attempts', 'attempts' => $attempts];
        }

        if (! isset($payload['hash']) || ! hash_equals((string) $payload['hash'], $this->hash($code))) {
            return ['valid' => false, 'reason' => 'mismatch', 'attempts' => $attempts + 1];
        }

        return ['valid' => true, 'reason' => null, 'attempts' => $attempts];
    }

    /**
     * Store a code, returning the payload for the session.
     *
     * @return array{code: string, hash: string, attempts: int, expires_at: int}
     */
    public function store(string $key, int $length = 6): array
    {
        $payload = $this->issue($length);
        session()->put($key, $payload);

        return $payload;
    }

    public function forget(string $key): void
    {
        session()->forget($key);
    }

    public function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /**
     * A fresh, human-typeable recovery code shown once at enrolment.
     */
    public function recoveryCode(): string
    {
        return strtoupper(Str::random(4).'-'.Str::random(4));
    }

    /**
     * Encrypt a long-lived shared secret (e.g. a TOTP seed) for storage.
     */
    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(string $payload): string
    {
        return Crypt::decryptString($payload);
    }
}
