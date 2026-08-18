<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use RuntimeException;

class AdminProMailboxService
{
    public const FOLDERS = ['inbox', 'archive', 'spam', 'trash'];

    public const FILTERS = ['all', 'unread', 'starred'];

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

    public function folders(): array
    {
        $connection = $this->open('inbox');

        try {
            return collect(self::FOLDERS)->map(function (string $folder) use ($connection): array {
                $name = $this->resolveFolderName($connection, $folder, true);
                $status = imap_status($connection, $this->serverPrefix().$this->encodeMailboxName($name), SA_MESSAGES | SA_UNSEEN);

                return [
                    'key' => $folder,
                    'label' => $this->folderLabel($folder),
                    'total' => (int) ($status->messages ?? 0),
                    'unread' => (int) ($status->unseen ?? 0),
                ];
            })->all();
        } finally {
            imap_close($connection);
        }
    }

    public function messages(
        int $page = 1,
        int $perPage = 30,
        ?string $search = null,
        string $folder = 'inbox',
        string $filter = 'all',
    ): array {
        $folder = $this->validateFolder($folder);
        $filter = $this->validateFilter($filter);
        $connection = $this->open($folder);

        try {
            $criteria = match ($filter) {
                'unread' => 'UNSEEN',
                'starred' => 'FLAGGED',
                default => 'ALL',
            };
            $uids = imap_search($connection, $criteria, SE_UID) ?: [];
            rsort($uids, SORT_NUMERIC);
            $items = [];
            $needle = mb_strtolower(trim((string) $search));

            foreach ($uids as $uid) {
                $header = $this->header($connection, (int) $uid, $folder);
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

    public function message(int $uid, string $folder = 'inbox'): array
    {
        $folder = $this->validateFolder($folder);
        $connection = $this->open($folder);

        try {
            $messageNumber = imap_msgno($connection, $uid);
            throw_if($messageNumber < 1, RuntimeException::class, 'Лист не знайдено.');
            imap_setflag_full($connection, (string) $uid, '\\Seen', ST_UID);
            $structure = imap_fetchstructure($connection, $messageNumber);

            return [
                ...$this->header($connection, $uid, $folder),
                'body' => trim($this->extractBody($connection, $messageNumber, $structure)),
            ];
        } finally {
            imap_close($connection);
        }
    }

    public function mark(int $uid, bool $read, string $folder = 'inbox'): void
    {
        $connection = $this->open($this->validateFolder($folder));

        try {
            $success = $read
                ? imap_setflag_full($connection, (string) $uid, '\\Seen', ST_UID)
                : imap_clearflag_full($connection, (string) $uid, '\\Seen', ST_UID);
            throw_unless($success, RuntimeException::class, 'Не вдалося змінити стан листа.');
        } finally {
            imap_close($connection);
        }
    }

    public function flag(int $uid, bool $flagged, string $folder = 'inbox'): void
    {
        $connection = $this->open($this->validateFolder($folder));

        try {
            $success = $flagged
                ? imap_setflag_full($connection, (string) $uid, '\\Flagged', ST_UID)
                : imap_clearflag_full($connection, (string) $uid, '\\Flagged', ST_UID);
            throw_unless($success, RuntimeException::class, 'Не вдалося змінити позначку листа.');
        } finally {
            imap_close($connection);
        }
    }

    public function move(int $uid, string $targetFolder, string $sourceFolder = 'inbox'): void
    {
        $sourceFolder = $this->validateFolder($sourceFolder);
        $targetFolder = $this->validateFolder($targetFolder);
        throw_if($sourceFolder === $targetFolder, RuntimeException::class, 'Лист уже знаходиться в цій папці.');
        $connection = $this->open($sourceFolder);

        try {
            $target = $this->resolveFolderName($connection, $targetFolder, true);
            throw_unless(
                imap_mail_move($connection, (string) $uid, $this->encodeMailboxName($target), CP_UID),
                RuntimeException::class,
                'Не вдалося перемістити лист.',
            );
            imap_expunge($connection);
        } finally {
            imap_close($connection);
        }
    }

    public function delete(int $uid, string $folder = 'inbox', bool $permanently = false): void
    {
        $folder = $this->validateFolder($folder);

        if ($folder !== 'trash' && ! $permanently) {
            $this->move($uid, 'trash', $folder);

            return;
        }

        $connection = $this->open($folder);

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

    public function newMessageBatch(?int $lastUid, ?int $uidValidity, int $limit = 20): array
    {
        $connection = $this->open('inbox');

        try {
            $status = imap_status($connection, $this->serverPrefix().'INBOX', SA_UIDVALIDITY | SA_UIDNEXT);
            throw_if($status === false, RuntimeException::class, 'Не вдалося перевірити стан вхідної пошти.');
            $currentValidity = (int) ($status->uidvalidity ?? 0);
            $uids = imap_search($connection, 'ALL', SE_UID) ?: [];
            rsort($uids, SORT_NUMERIC);
            $latestUid = $uids === [] ? 0 : (int) $uids[0];
            $reset = $uidValidity !== null && $uidValidity !== $currentValidity;
            $messages = [];

            if ($lastUid !== null && ! $reset) {
                foreach (array_slice(array_values(array_filter($uids, fn (int $uid): bool => $uid > $lastUid)), 0, $limit) as $uid) {
                    $messages[] = $this->header($connection, (int) $uid, 'inbox');
                }
            }

            return [
                'uid_validity' => $currentValidity,
                'last_uid' => $latestUid,
                'reset' => $reset,
                'messages' => $messages,
            ];
        } finally {
            imap_close($connection);
        }
    }

    private function open(string $folder = 'inbox')
    {
        throw_unless(function_exists('imap_open'), RuntimeException::class, 'PHP IMAP недоступний на сервері.');
        throw_unless(filled(config('admin_pro_mail.password')), RuntimeException::class, 'Поштова скринька ще не налаштована.');

        $mailbox = $this->serverPrefix().'INBOX';
        $connection = @imap_open(
            $mailbox,
            (string) config('admin_pro_mail.username'),
            (string) config('admin_pro_mail.password'),
            0,
            1,
        );

        throw_if($connection === false, RuntimeException::class, 'Не вдалося підключитися до поштової скриньки.');

        if ($folder !== 'inbox') {
            $name = $this->resolveFolderName($connection, $folder, true);
            throw_unless(
                @imap_reopen($connection, $this->serverPrefix().$this->encodeMailboxName($name)),
                RuntimeException::class,
                'Не вдалося відкрити папку «'.$this->folderLabel($folder).'».',
            );
        }

        return $connection;
    }

    private function header($connection, int $uid, string $folder): array
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
            'flagged' => (bool) ($flags?->flagged ?? false),
            'folder' => $folder,
        ];
    }

    private function validateFolder(string $folder): string
    {
        $folder = strtolower(trim($folder));
        throw_unless(in_array($folder, self::FOLDERS, true), RuntimeException::class, 'Невідома поштова папка.');

        return $folder;
    }

    private function validateFilter(string $filter): string
    {
        $filter = strtolower(trim($filter));
        throw_unless(in_array($filter, self::FILTERS, true), RuntimeException::class, 'Невідомий фільтр листів.');

        return $filter;
    }

    private function serverPrefix(): string
    {
        $flags = '/imap/'.config('admin_pro_mail.imap.encryption', 'ssl');
        if (! config('admin_pro_mail.imap.validate_certificate', true)) {
            $flags .= '/novalidate-cert';
        }

        return sprintf('{%s:%d%s}', config('admin_pro_mail.imap.host'), config('admin_pro_mail.imap.port'), $flags);
    }

    private function resolveFolderName($connection, string $folder, bool $create): string
    {
        if ($folder === 'inbox') {
            return 'INBOX';
        }

        $candidates = match ($folder) {
            'archive' => ['archive', 'archives', 'inbox.archive', 'inbox.archives'],
            'spam' => ['spam', 'junk', 'junk e-mail', 'inbox.spam', 'inbox.junk'],
            'trash' => ['trash', 'deleted', 'deleted messages', 'inbox.trash', 'inbox.deleted'],
            default => [],
        };
        $mailboxes = imap_getmailboxes($connection, $this->serverPrefix(), '*') ?: [];

        foreach ($mailboxes as $mailbox) {
            $name = $this->decodeMailboxName($this->stripServerPrefix((string) $mailbox->name));
            if (in_array(mb_strtolower($name), $candidates, true)) {
                return $name;
            }
        }

        $name = match ($folder) {
            'archive' => 'Archive',
            'spam' => 'Spam',
            'trash' => 'Trash',
            default => throw new RuntimeException('Невідома поштова папка.'),
        };

        if ($create) {
            throw_unless(
                @imap_createmailbox($connection, $this->serverPrefix().$this->encodeMailboxName($name)),
                RuntimeException::class,
                'Не вдалося створити папку «'.$this->folderLabel($folder).'».',
            );
        }

        return $name;
    }

    private function stripServerPrefix(string $name): string
    {
        $separator = strpos($name, '}');

        return $separator === false ? $name : substr($name, $separator + 1);
    }

    private function encodeMailboxName(string $name): string
    {
        return function_exists('imap_utf7_encode') ? imap_utf7_encode($name) : $name;
    }

    private function decodeMailboxName(string $name): string
    {
        return function_exists('imap_utf7_decode') ? imap_utf7_decode($name) : $name;
    }

    private function folderLabel(string $folder): string
    {
        return match ($folder) {
            'inbox' => 'Вхідні',
            'archive' => 'Архів',
            'spam' => 'Спам',
            'trash' => 'Видалені',
            default => $folder,
        };
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
