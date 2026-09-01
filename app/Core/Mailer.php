<?php
namespace App\Core;

/**
 * Thin wrapper over PHP's mail(). Shared hosts normally have a local MTA;
 * swap the body of send() for SMTP/PHPMailer when one is available.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $from     = config('mail.from', 'no-reply@localhost');
        $fromName = config('mail.from_name', config('app.name'));
        $headers  = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . sprintf('%s <%s>', $fromName, $from),
            'Reply-To: ' . $from,
            'X-Mailer: DWMS 2.0',
        ];

        if (!function_exists('mail')) {
            error_log('[DWMS mail disabled] ' . $to . ' :: ' . $subject);
            return false;
        }
        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
        if (!$sent) {
            error_log('[DWMS mail failed] ' . $to . ' :: ' . $subject);
        }
        return (bool) $sent;
    }

    public static function otp(string $to, string $code, string $reason): bool
    {
        $app  = config('app.name', 'DWMS 2.0');
        $body = "Your $app one-time password is: $code\n\n"
              . "It is valid for 10 minutes and is needed to $reason.\n"
              . "If you did not request this, you can ignore this e-mail.\n\n"
              . "-- $app";
        return self::send($to, $app . ' one-time password: ' . $code, $body);
    }
}
