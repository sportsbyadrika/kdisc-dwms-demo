<?php
namespace App\Core;

/**
 * One-time passwords for e-mail verification, password reset and Aadhaar e-KYC.
 *
 * Codes are stored hashed. While config('mail.demo_otp') is true the plain code
 * is handed back to the caller so the MVP can be demonstrated without a working
 * mail or SMS gateway.
 */
class Otp
{
    public const TTL       = 600; // 10 minutes
    public const COOLDOWN  = 60;  // seconds between sends
    public const MAX_TRIES = 5;

    public static function issue(string $channel, string $purpose, string $identifier, ?array $payload = null): string
    {
        // Retire any code still outstanding for this identifier and purpose.
        Database::run(
            'UPDATE verification_codes SET consumed_at = NOW()
             WHERE identifier = ? AND purpose = ? AND consumed_at IS NULL',
            [$identifier, $purpose]
        );

        $code = otp(6);
        Database::insert('verification_codes', [
            'channel'    => $channel,
            'purpose'    => $purpose,
            'identifier' => $identifier,
            'code_hash'  => password_hash($code, PASSWORD_BCRYPT),
            'payload'    => $payload ? json_encode($payload) : null,
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL),
        ]);

        return $code;
    }

    /** Seconds the caller must wait before another code may be sent (0 = go ahead). */
    public static function cooldownRemaining(string $purpose, string $identifier): int
    {
        $last = Database::value(
            'SELECT created_at FROM verification_codes
             WHERE identifier = ? AND purpose = ? ORDER BY id DESC LIMIT 1',
            [$identifier, $purpose]
        );
        if (!$last) {
            return 0;
        }
        $elapsed = time() - strtotime($last);
        return $elapsed >= self::COOLDOWN ? 0 : self::COOLDOWN - $elapsed;
    }

    /**
     * @return array{ok:bool,message:string,payload:?array}
     */
    public static function verify(string $purpose, string $identifier, string $code): array
    {
        $row = Database::first(
            'SELECT * FROM verification_codes
             WHERE identifier = ? AND purpose = ? AND consumed_at IS NULL
             ORDER BY id DESC LIMIT 1',
            [$identifier, $purpose]
        );

        if (!$row) {
            return ['ok' => false, 'message' => 'No active one-time password. Please request a new one.', 'payload' => null];
        }
        if (strtotime($row['expires_at']) < time()) {
            return ['ok' => false, 'message' => 'That one-time password has expired. Please request a new one.', 'payload' => null];
        }
        if ((int) $row['attempts'] >= self::MAX_TRIES) {
            Database::update('verification_codes', ['consumed_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $row['id']]);
            return ['ok' => false, 'message' => 'Too many incorrect attempts. Please request a new one-time password.', 'payload' => null];
        }
        if (!password_verify(trim($code), $row['code_hash'])) {
            Database::run('UPDATE verification_codes SET attempts = attempts + 1 WHERE id = ?', [$row['id']]);
            $left = self::MAX_TRIES - ((int) $row['attempts'] + 1);
            return [
                'ok'      => false,
                'message' => 'That one-time password is not correct.' . ($left > 0 ? ' ' . $left . ' attempt(s) left.' : ''),
                'payload' => null,
            ];
        }

        Database::update('verification_codes', ['consumed_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $row['id']]);
        return [
            'ok'      => true,
            'message' => 'Verified.',
            'payload' => $row['payload'] ? json_decode($row['payload'], true) : null,
        ];
    }

    /** True when the plain code may be revealed on screen (demo mode). */
    public static function demoMode(): bool
    {
        return (bool) config('mail.demo_otp', false);
    }
}
