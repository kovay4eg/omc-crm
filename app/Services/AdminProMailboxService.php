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
                ...$this->extractContent($connection, $messageNumber, $structure),
                'attachments' => $this->attachmentDescriptors($structure),
            ];
        } finally {
            imap_close($connection);
        }
    }

    public function attachment(int $uid, string $partNumber, string $folder = 'inbox'): array
    {
        throw_unless(preg_match('/^\d+(?:\.\d+)*$/', $partNumber) === 1, RuntimeException::class, 'Некоректний ідентифікатор вкладення.');

        $folder = $this->validateFolder($folder);
        $connection = $this->open($folder);

        try {
            $messageNumber = imap_msgno($connection, $uid);
            throw_if($messageNumber < 1, RuntimeException::class, 'Лист не знайдено.');
            $structure = imap_fetchstructure($connection, $messageNumber);
            $part = $this->partByNumber($structure, $partNumber);
            throw_if($part === null, RuntimeException::class, 'Вкладення не знайдено.');

            $descriptor = $this->attachmentDescriptor($part, $partNumber);
            throw_if($descriptor === null, RuntimeException::class, 'Ця частина листа не є вкладенням.');

            $raw = (string) imap_fetchbody($connection, $messageNumber, $partNumber, FT_PEEK);
            $content = $this->decodeTransferEncoding($raw, (int) ($part->encoding ?? 0));

            return [
                ...$descriptor,
                'content' => $content,
                'size' => strlen($content),
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

        $aliases = match ($folder) {
            'archive' => ['archive', 'archives'],
            'spam' => ['spam', 'junk', 'junk e-mail'],
            'trash' => ['trash', 'deleted', 'deleted messages'],
            default => [],
        };
        $mailboxes = imap_getmailboxes($connection, $this->serverPrefix(), '*') ?: [];

        foreach ($mailboxes as $mailbox) {
            $name = $this->decodeMailboxName($this->stripServerPrefix((string) $mailbox->name));
            $normalized = mb_strtolower($name);
            $basename = mb_strtolower((string) preg_replace('/^.*[.\/]/u', '', $name));
            if (in_array($normalized, $aliases, true) || in_array($basename, $aliases, true)) {
                return $name;
            }
        }

        $leaf = match ($folder) {
            'archive' => 'Archive',
            'spam' => 'Spam',
            'trash' => 'Trash',
            default => throw new RuntimeException('Невідома поштова папка.'),
        };

        if ($create) {
            foreach ($this->folderCreationCandidates($mailboxes, $leaf) as $name) {
                if (@imap_createmailbox($connection, $this->serverPrefix().$this->encodeMailboxName($name))) {
                    return $name;
                }

                imap_errors();
            }

            throw new RuntimeException('Не вдалося створити папку «'.$this->folderLabel($folder).'».');
        }

        return $leaf;
    }

    private function folderCreationCandidates(array $mailboxes, string $leaf): array
    {
        $delimiter = '.';
        $namespace = null;

        foreach ($mailboxes as $mailbox) {
            $name = $this->decodeMailboxName($this->stripServerPrefix((string) ($mailbox->name ?? '')));
            $mailboxDelimiter = trim((string) ($mailbox->delimiter ?? ''));
            if ($mailboxDelimiter !== '') {
                $delimiter = $mailboxDelimiter;
            }

            if (preg_match('/^INBOX([.\/]).+/i', $name, $matches) === 1) {
                $namespace = 'INBOX'.$matches[1];
                break;
            }

            if ($mailboxDelimiter !== '' && str_starts_with(mb_strtolower($name), 'inbox'.mb_strtolower($mailboxDelimiter))) {
                $namespace = 'INBOX'.$mailboxDelimiter;
                break;
            }
        }

        $preferred = $namespace === null ? $leaf : $namespace.$leaf;
        $fallback = $namespace === null ? 'INBOX'.$delimiter.$leaf : $leaf;

        return array_values(array_unique([$preferred, $fallback]));
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

    private function extractContent($connection, int $messageNumber, object $structure): array
    {
        if (empty($structure->parts)) {
            $body = $this->decodeBody((string) imap_body($connection, $messageNumber, FT_PEEK), (int) ($structure->encoding ?? 0), $structure);

            return strtoupper((string) ($structure->subtype ?? 'PLAIN')) === 'HTML'
                ? $this->htmlContent($body)
                : ['body' => $this->normalizeText($body), 'html' => null, 'links' => []];
        }

        $plain = $this->findPart($connection, $messageNumber, $structure, 'PLAIN');
        $html = $this->findPart($connection, $messageNumber, $structure, 'HTML');

        if ($plain !== null) {
            return $this->plainContent($plain, $html);
        }

        return $html === null
            ? ['body' => '', 'html' => null, 'links' => []]
            : $this->htmlContent($html);
    }

    private function plainContent(string $plain, ?string $html): array
    {
        $body = $this->normalizeText($plain);
        $containsStylesheet = preg_match('/@media\b|(?:^|\n)\s*\.[a-z0-9_-]+\s*\{/iu', $body) === 1;

        if ($containsStylesheet && $html !== null) {
            return $this->htmlContent($html);
        }

        return [
            'body' => $body,
            'html' => $html === null ? null : $this->sanitizeHtml($html),
            'links' => $html === null ? [] : $this->safeLinks($html),
        ];
    }

    private function htmlContent(string $html): array
    {
        $withoutHiddenContent = preg_replace(
            '~<(style|script|head|noscript|template|svg)\b[^>]*>.*?</\1\s*>~is',
            '',
            $html,
        ) ?? $html;
        $withLineBreaks = preg_replace(
            '~<(?:br\s*/?|/(?:p|div|li|tr|h[1-6]))\s*>~i',
            "\n",
            $withoutHiddenContent,
        ) ?? $withoutHiddenContent;

        return [
            'body' => $this->normalizeText(html_entity_decode(strip_tags($withLineBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'html' => $this->sanitizeHtml($html),
            'links' => $this->safeLinks($html),
        ];
    }

    private function sanitizeHtml(string $html): ?string
    {
        if (! class_exists(\DOMDocument::class) || trim($html) === '') {
            return null;
        }

        $previousErrors = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8">'.$html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        if (! $loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);

            return null;
        }

        $xpath = new \DOMXPath($document);
        $blocked = $xpath->query('//script|//iframe|//frame|//frameset|//object|//embed|//applet|//form|//input|//button|//textarea|//select|//option|//meta|//base|//link|//noscript|//template|//svg|//video|//audio|//source');
        if ($blocked !== false) {
            foreach (iterator_to_array($blocked) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $elements = $xpath->query('//*');
        if ($elements !== false) {
            foreach (iterator_to_array($elements) as $element) {
                foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
                    $name = strtolower($attribute->name);
                    $value = trim($attribute->value);

                    if (str_starts_with($name, 'on') || in_array($name, ['srcdoc', 'srcset', 'background', 'dynsrc', 'lowsrc', 'action', 'formaction', 'poster'], true)) {
                        $element->removeAttributeNode($attribute);

                        continue;
                    }

                    if ($name === 'href' && ! $this->isSafeMailUrl($value, ['http', 'https', 'mailto'])) {
                        $element->removeAttributeNode($attribute);

                        continue;
                    }

                    if ($name === 'src' && ! $this->isSafeImageSource($value)) {
                        $element->removeAttributeNode($attribute);

                        continue;
                    }

                    if ($name === 'style' && preg_match('/(?:expression\s*\(|url\s*\(|behavior\s*:|-moz-binding)/iu', $value) === 1) {
                        $element->removeAttributeNode($attribute);
                    }
                }

                if (strtolower($element->tagName) === 'a') {
                    $element->setAttribute('target', '_blank');
                    $element->setAttribute('rel', 'noopener noreferrer nofollow');
                }
            }
        }

        $messageStyles = '';
        $styles = $xpath->query('//style');
        if ($styles !== false) {
            foreach (iterator_to_array($styles) as $style) {
                $safeCss = preg_replace('/@import\s+[^;]+;|url\s*\([^)]*\)|expression\s*\([^)]*\)|behavior\s*:[^;]+;?/iu', '', $style->textContent) ?? '';
                $messageStyles .= '<style>'.$safeCss.'</style>';
                $style->parentNode?->removeChild($style);
            }
        }

        $body = $document->getElementsByTagName('body')->item(0);
        $content = '';
        $container = $body ?? $document->documentElement;
        if ($container !== null) {
            foreach (iterator_to_array($container->childNodes) as $child) {
                $content .= $document->saveHTML($child);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $baseStyles = <<<'CSS'
        html { color-scheme: light; }
        body { margin: 0; padding: 0; color: #172033; background: #fff; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; line-height: 1.5; overflow-wrap: anywhere; }
        img { max-width: 100%; height: auto; }
        table { max-width: 100%; }
        pre { max-width: 100%; overflow: auto; white-space: pre-wrap; }
        a { color: #3047e8; }
        CSS;

        return '<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><base target="_blank"><style>'.$baseStyles.'</style>'.$messageStyles.'</head><body>'.$content.'</body></html>';
    }

    private function isSafeMailUrl(string $url, array $schemes): bool
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }

        return in_array(strtolower((string) parse_url(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'), PHP_URL_SCHEME)), $schemes, true);
    }

    private function isSafeImageSource(string $source): bool
    {
        if (preg_match('/^cid:[^\s]+$/iu', $source) === 1) {
            return true;
        }

        if (preg_match('/^data:image\/(?:png|jpe?g|gif|webp);base64,[a-z0-9+\/=\s]+$/iu', $source) === 1) {
            return true;
        }

        return $this->isSafeMailUrl($source, ['https']);
    }

    private function attachmentDescriptors(object $structure): array
    {
        $attachments = [];
        $this->collectAttachmentDescriptors($structure, '', $attachments);

        return $attachments;
    }

    private function collectAttachmentDescriptors(object $structure, string $prefix, array &$attachments): void
    {
        foreach ($structure->parts ?? [] as $index => $part) {
            $partNumber = $prefix === '' ? (string) ($index + 1) : $prefix.'.'.($index + 1);
            $descriptor = $this->attachmentDescriptor($part, $partNumber);

            if ($descriptor !== null) {
                $attachments[] = $descriptor;

                continue;
            }

            if (! empty($part->parts)) {
                $this->collectAttachmentDescriptors($part, $partNumber, $attachments);
            }
        }
    }

    private function attachmentDescriptor(object $part, string $partNumber): ?array
    {
        $disposition = strtolower((string) ($part->disposition ?? ''));
        $filename = $this->partFilename($part);
        $contentId = trim((string) ($part->id ?? ''), "<> \t\r\n");
        $isAttachment = $disposition === 'attachment'
            || $filename !== ''
            || ($disposition === 'inline' && (int) ($part->type ?? 0) !== 0);

        if (! $isAttachment) {
            return null;
        }

        $mimeType = $this->partMimeType($part);

        return [
            'part' => $partNumber,
            'name' => $filename !== '' ? $filename : 'attachment-'.$partNumber.'.'.$this->extensionForMimeType($mimeType),
            'mime_type' => $mimeType,
            'size' => max(0, (int) ($part->bytes ?? 0)),
            'inline' => $disposition === 'inline' || $contentId !== '',
            'content_id' => $contentId,
        ];
    }

    private function partFilename(object $part): string
    {
        foreach ([...($part->dparameters ?? []), ...($part->parameters ?? [])] as $parameter) {
            $attribute = strtolower((string) ($parameter->attribute ?? ''));
            if (! in_array($attribute, ['filename', 'filename*', 'name', 'name*'], true)) {
                continue;
            }

            $value = trim((string) ($parameter->value ?? ''));
            if (str_contains($attribute, '*') && str_contains($value, "''")) {
                [, $value] = explode("''", $value, 2);
                $value = rawurldecode($value);
            } else {
                $value = $this->decodeHeader($value);
            }

            $value = basename(str_replace('\\', '/', $value));
            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '';

            return mb_substr(trim($value), 0, 180);
        }

        return '';
    }

    private function partMimeType(object $part): string
    {
        $primary = match ((int) ($part->type ?? 7)) {
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            default => 'application',
        };
        $subtype = strtolower((string) ($part->subtype ?? 'octet-stream'));
        $subtype = preg_replace('/[^a-z0-9.+-]/', '', $subtype) ?: 'octet-stream';

        return $primary.'/'.$subtype;
    }

    private function extensionForMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'message/rfc822' => 'eml',
            default => 'bin',
        };
    }

    private function partByNumber(object $structure, string $partNumber): ?object
    {
        $current = $structure;
        foreach (explode('.', $partNumber) as $segment) {
            $index = (int) $segment - 1;
            if ($index < 0 || ! isset($current->parts[$index])) {
                return null;
            }
            $current = $current->parts[$index];
        }

        return $current;
    }

    private function safeLinks(string $html): array
    {
        preg_match_all(
            '~<a\b[^>]*\bhref\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)</a\s*>~is',
            $html,
            $matches,
            PREG_SET_ORDER,
        );

        $links = [];
        foreach ($matches as $match) {
            $url = trim(html_entity_decode((string) $match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (! in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                continue;
            }

            $label = $this->normalizeText(html_entity_decode(strip_tags((string) $match[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $links[$url] = [
                'label' => $label === '' ? $url : $label,
                'url' => $url,
            ];
        }

        return array_values($links);
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\t ]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */u', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
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
        $decoded = $this->decodeTransferEncoding($body, $encoding);
        $charset = 'UTF-8';
        foreach ([...($part->parameters ?? []), ...($part->dparameters ?? [])] as $parameter) {
            if (strtolower((string) ($parameter->attribute ?? '')) === 'charset') {
                $charset = (string) $parameter->value;
                break;
            }
        }

        return strtoupper($charset) === 'UTF-8' ? $decoded : (mb_convert_encoding($decoded, 'UTF-8', $charset) ?: $decoded);
    }

    private function decodeTransferEncoding(string $body, int $encoding): string
    {
        return match ($encoding) {
            3 => base64_decode($body, true) ?: '',
            4 => quoted_printable_decode($body),
            default => $body,
        };
    }
}
