<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use RuntimeException;

class AdminProMailboxService
{
    public function status(): array
    {
        return ['address' => (string) config('admin_pro_mail.address'), 'configured' => $this->configured()];
    }

    public function configured(): bool
    {
        return function_exists('imap_open')
            && filled(config('admin_pro_mail.username'))
            && filled(config('admin_pro_mail.password'));
    }

    public function messages(int $page = 1, int $perPage = 30, ?string $search = null): array
    {
        $connection = $this->open();

        try {
            $uids = imap_search($connection, 'ALL', SE_UID) ?: [];
            rsort($uids, SORT_NUMERIC);
            $items = [];
            $needle = mb_strtolower(trim((string) $search));

            foreach ($uids as $uid) {
                $header = $this->header($connection, (int) $uid);
                $haystack = mb_strtolower($header['subject'].' '.$header['from_name'].' '.$header['from_address']);
                if ($needle === '' || str_contains($haystack, $needle)) {
                    $items[] = $header;
                }
            }

            $total = count($items);

            return [
                'data' => array_slice($items, ($page - 1) * $perPage, $perPage),
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                ],
            ];
        } finally {
            imap_close($connection);
        }
    }

    public function message(int $uid): array
    {
        $connection = $this->open();

        try {
            $messageNumber = imap_msgno($connection, $uid);
            throw_if($messageNumber < 1, RuntimeException::class, 'Лист не знайдено.');
            imap_setflag_full($connection, (string) $uid, '\\Seen', ST_UID);
            $structure = imap_fetchstructure($connection, $messageNumber);

            return [
                ...$this->header($connection, $uid),
                'body' => trim($this->extractBody($connection, $messageNumber, $structure)),
            ];
        } finally {
            imap_close($connection);
        }
    }

    public function mark(int $uid, bool $read): void
    {
        $connection = $this->open();

        try {
            $success = $read
                ? imap_setflag_full($connection, (string) $uid, '\\Seen', ST_UID)
                : imap_clearflag_full($connection, (string) $uid, '\\Seen', ST_UID);
            throw_unless($success, RuntimeException::class, 'Не вдалося змінити стан листа.');
        } finally {
            imap_close($connection);
        }
    }

    public function delete(int $uid): void
    {
        $connection = $this->open();

        try {
            throw_unless(imap_delete($connection, (string) $uid, FT_UID), RuntimeException::class, 'Не вдалося видалити лист.');
            imap_expunge($connection);
        } finally {
            imap_close($connection);
        }
    }

    public function send(array $recipients, string $subject, string $body): void
    {
        throw_unless($this->configured(), RuntimeException::class, 'Поштова скринька ще не налаштована.');

        Mail::mailer('admin_pro')->raw($body, function ($message) use ($recipients, $subject): void {
            $message
                ->from((string) config('admin_pro_mail.address'), (string) config('admin_pro_mail.name'))
                ->to($recipients)
                ->subject($subject);
        });
    }

    private function open()
    {
        throw_unless(function_exists('imap_open'), RuntimeException::class, 'PHP IMAP недоступний на сервері.');
        throw_unless(filled(config('admin_pro_mail.password')), RuntimeException::class, 'Поштова скринька ще не налаштована.');

        $flags = '/imap/'.config('admin_pro_mail.imap.encryption', 'ssl');
        if (! config('admin_pro_mail.imap.validate_certificate', true)) {
            $flags .= '/novalidate-cert';
        }
        $mailbox = sprintf('{%s:%d%s}INBOX', config('admin_pro_mail.imap.host'), config('admin_pro_mail.imap.port'), $flags);
        $connection = @imap_open(
            $mailbox,
            (string) config('admin_pro_mail.username'),
            (string) config('admin_pro_mail.password'),
            0,
            1,
        );

        throw_if($connection === false, RuntimeException::class, 'Не вдалося підключитися до поштової скриньки.');

        return $connection;
    }

    private function header($connection, int $uid): array
    {
        $messageNumber = imap_msgno($connection, $uid);
        throw_if($messageNumber < 1, RuntimeException::class, 'Лист не знайдено.');
        $header = imap_headerinfo($connection, $messageNumber);
        throw_if($header === false, RuntimeException::class, 'Не вдалося прочитати заголовок листа.');
        $from = $header->from[0] ?? null;
        $fromAddress = $from ? trim(($from->mailbox ?? '').'@'.($from->host ?? ''), '@') : '';
        $flags = imap_fetch_overview($connection, (string) $uid, FT_UID)[0] ?? null;

        return [
            'uid' => $uid,
            'subject' => $this->decodeHeader((string) ($header->subject ?? '(без теми)')),
            'from_name' => $this->decodeHeader((string) ($from->personal ?? $fromAddress)),
            'from_address' => $fromAddress,
            'date' => isset($header->udate) ? date(DATE_ATOM, (int) $header->udate) : null,
            'read' => (bool) ($flags?->seen ?? false),
        ];
    }

    private function decodeHeader(string $value): string
    {
        $result = '';
        foreach (imap_mime_header_decode($value) as $part) {
            $charset = strtoupper((string) ($part->charset ?? 'UTF-8'));
            $text = (string) ($part->text ?? '');
            $result .= in_array($charset, ['DEFAULT', 'UTF-8', 'US-ASCII'], true)
                ? $text
                : (mb_convert_encoding($text, 'UTF-8', $charset) ?: $text);
        }

        return trim($result);
    }

    private function extractBody($connection, int $messageNumber, object $structure): string
    {
        if (empty($structure->parts)) {
            return $this->decodeBody((string) imap_body($connection, $messageNumber, FT_PEEK), (int) ($structure->encoding ?? 0), $structure);
        }

        $plain = $this->findPart($connection, $messageNumber, $structure, 'PLAIN');
        if ($plain !== null) {
            return $plain;
        }
        $html = $this->findPart($connection, $messageNumber, $structure, 'HTML');

        return $html === null ? '' : trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function findPart($connection, int $messageNumber, object $structure, string $subtype, string $prefix = ''): ?string
    {
        foreach ($structure->parts ?? [] as $index => $part) {
            $number = $prefix === '' ? (string) ($index + 1) : $prefix.'.'.($index + 1);
            if ((int) ($part->type ?? -1) === 0 && strtoupper((string) ($part->subtype ?? '')) === $subtype) {
                return $this->decodeBody((string) imap_fetchbody($connection, $messageNumber, $number, FT_PEEK), (int) ($part->encoding ?? 0), $part);
            }
            if (! empty($part->parts)) {
                $found = $this->findPart($connection, $messageNumber, $part, $subtype, $number);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function decodeBody(string $body, int $encoding, object $part): string
    {
        $decoded = match ($encoding) {
            3 => base64_decode($body, true) ?: '',
            4 => quoted_printable_decode($body),
            default => $body,
        };
        $charset = 'UTF-8';
        foreach ([...($part->parameters ?? []), ...($part->dparameters ?? [])] as $parameter) {
            if (strtolower((string) ($parameter->attribute ?? '')) === 'charset') {
                $charset = (string) $parameter->value;
                break;
            }
        }

        return strtoupper($charset) === 'UTF-8' ? $decoded : (mb_convert_encoding($decoded, 'UTF-8', $charset) ?: $decoded);
    }
}
