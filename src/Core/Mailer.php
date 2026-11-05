<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\EmailLog;

final class Mailer
{
    public static function send(string $to, string $subject, string $html): bool
    {
        $config = config('mail');
        $fromName = (string) $config['from_name'];
        $from = (string) $config['from'];
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'From: ' . sprintf('=?UTF-8?B?%s?=', base64_encode($fromName)) . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: TRIVEX',
        ];

        $status = 'logged';
        $driver = (string) $config['driver'];

        try {
            if ($driver === 'smtp') {
                self::smtp($to, $subject, $html, $config, $headers);
                $status = 'sent';
            } elseif ($driver === 'mail') {
                $body = chunk_split(base64_encode($html), 76, "\r\n");
                $ok = @mail(
                    $to,
                    '=?UTF-8?B?' . base64_encode($subject) . '?=',
                    $body,
                    implode("\r\n", $headers)
                );
                $status = $ok ? 'sent' : 'failed';
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            self::log('[MAIL ERROR] ' . $e->getMessage());
        }

        $dir = BASE_PATH . '/storage/mail';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $slug = date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($to)) . '_' . substr(md5($subject . microtime()), 0, 6);
        @file_put_contents($dir . '/' . $slug . '.html', $html);

        try {
            EmailLog::create([
                'to_email' => $to,
                'subject'  => $subject,
                'body'     => $html,
                'driver'   => $driver,
                'status'   => $status,
            ]);
        } catch (\Throwable) {
            // Mail logging should never break a request.
        }

        return $status !== 'failed';
    }

    public static function render(string $view, array $data = []): string
    {
        $view = preg_replace('#^emails/#', '', $view) ?: $view;
        $data['subject'] = $data['subject'] ?? '';
        $data['preheader'] = $data['preheader'] ?? '';
        $body = View::raw('emails/' . $view, $data);
        $data['emailBody'] = $body;
        return View::raw('emails/layout', $data);
    }

    private static function smtp(string $to, string $subject, string $html, array $config, array $headers): void
    {
        $host = (string) $config['host'];
        $port = (int) $config['port'];
        $encryption = strtolower((string) ($config['encryption'] ?? ''));
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');

        if ($username === '' || $password === '') {
            throw new \RuntimeException('SMTP username and password are not configured.');
        }

        $timeout = 15;
        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ]);
        $socket = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!is_resource($socket)) {
            throw new \RuntimeException("SMTP connection failed: $errstr ($errno)");
        }
        stream_set_timeout($socket, $timeout);

        $read = static function () use ($socket): string {
            $data = '';
            while (($line = fgets($socket, 515)) !== false) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $write = static function (string $command) use ($socket): void {
            if (fwrite($socket, $command . "\r\n") === false) {
                throw new \RuntimeException('Could not write to the SMTP socket.');
            }
        };
        $expect = static function (string $response, array $codes): void {
            $line = trim($response);
            $code = (int) substr($line, 0, 3);
            if (!in_array($code, $codes, true)) {
                throw new \RuntimeException('SMTP error: ' . ($line !== '' ? $line : 'empty response'));
            }
        };

        try {
            $expect($read(), [220]);

            $hostname = gethostname() ?: 'localhost';
            $write("EHLO $hostname");
            $response = $read();
            $expect($response, [250]);

            if ($encryption === 'tls') {
                $write('STARTTLS');
                $expect($read(), [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('Could not start TLS on the SMTP connection.');
                }
                $write("EHLO $hostname");
                $expect($read(), [250]);
            }

            $write('AUTH LOGIN');
            $expect($read(), [334]);
            $write(base64_encode($username));
            $expect($read(), [334]);
            $write(base64_encode($password));
            $expect($read(), [235]);

            $from = (string) $config['from'];
            $write("MAIL FROM:<$from>");
            $expect($read(), [250]);
            $write("RCPT TO:<$to>");
            $expect($read(), [250, 251]);
            $write('DATA');
            $expect($read(), [354]);

            $message = self::smtpMessage($to, $subject, $html, $from, (string) $config['from_name'], $headers);
            $write($message);
            $expect($read(), [250]);

            $write('QUIT');
            $read();
        } finally {
            fclose($socket);
        }
    }

    /**
     * Build a CRLF-safe SMTP DATA payload. The body is base64 encoded so long
     * lines, accents and HTML never trigger SMTP line-length problems.
     */
    private static function smtpMessage(
        string $to,
        string $subject,
        string $html,
        string $from,
        string $fromName,
        array $headers
    ): string {
        $messageHeaders = [
            'Date: ' . date('r'),
            'From: ' . sprintf('=?UTF-8?B?%s?=', base64_encode($fromName)) . ' <' . $from . '>',
            'To: <' . $to . '>',
            'Reply-To: ' . $from,
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (gethostname() ?: 'trivex.local') . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'X-Mailer: TRIVEX',
        ];

        $body = chunk_split(base64_encode($html), 76, "\r\n");
        $message = implode("\r\n", $messageHeaders) . "\r\n\r\n" . $body;
        $message = str_replace(["\r\n", "\r"], "\n", $message);
        $message = str_replace("\n", "\r\n", $message);
        $message = preg_replace('/^\./m', '..', $message) ?? $message;

        return rtrim($message, "\r\n") . "\r\n.";
    }

    private static function log(string $message): void
    {
        $dir = BASE_PATH . '/storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($dir . '/mail.log', '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}
