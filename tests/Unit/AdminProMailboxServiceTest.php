<?php

namespace Tests\Unit;

use App\Services\AdminProMailboxService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AdminProMailboxServiceTest extends TestCase
{
    public function test_it_prefers_detected_inbox_namespace_for_new_folders(): void
    {
        $mailboxes = [
            (object) ['name' => '{mail.example:993/imap/ssl}INBOX', 'delimiter' => '.'],
            (object) ['name' => '{mail.example:993/imap/ssl}INBOX.Sent', 'delimiter' => '.'],
        ];

        $this->assertSame(
            ['INBOX.Spam', 'Spam'],
            $this->creationCandidates($mailboxes, 'Spam'),
        );
    }

    public function test_it_keeps_bare_folder_first_when_server_has_no_inbox_namespace(): void
    {
        $mailboxes = [
            (object) ['name' => '{mail.example:993/imap/ssl}INBOX', 'delimiter' => '/'],
            (object) ['name' => '{mail.example:993/imap/ssl}Sent', 'delimiter' => '/'],
        ];

        $this->assertSame(
            ['Spam', 'INBOX/Spam'],
            $this->creationCandidates($mailboxes, 'Spam'),
        );
    }

    private function creationCandidates(array $mailboxes, string $leaf): array
    {
        $method = new ReflectionMethod(AdminProMailboxService::class, 'folderCreationCandidates');
        $method->setAccessible(true);

        return $method->invoke(new AdminProMailboxService, $mailboxes, $leaf);
    }
}
