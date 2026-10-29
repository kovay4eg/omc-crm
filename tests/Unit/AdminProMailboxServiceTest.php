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

    public function test_it_turns_html_email_into_readable_text_and_safe_links(): void
    {
        $html = <<<'HTML'
        <html>
            <head><style>@media only screen { body { color: red; } }</style></head>
            <body>
                <p>Hello Nataliia,</p>
                <p>Your confirmation is required.</p>
                <a href="https://developer.apple.com/enroll/verify?token=abc&amp;value=1">Complete verification form</a>
                <a href="javascript:alert('unsafe')">Unsafe link</a>
                <script>alert('hidden')</script>
            </body>
        </html>
        HTML;

        $content = $this->htmlContent($html);

        $this->assertStringContainsString('Hello Nataliia,', $content['body']);
        $this->assertStringContainsString('Your confirmation is required.', $content['body']);
        $this->assertStringNotContainsString('@media', $content['body']);
        $this->assertStringNotContainsString("alert('hidden')", $content['body']);
        $this->assertSame([
            [
                'label' => 'Complete verification form',
                'url' => 'https://developer.apple.com/enroll/verify?token=abc&value=1',
            ],
        ], $content['links']);
    }

    private function creationCandidates(array $mailboxes, string $leaf): array
    {
        $method = new ReflectionMethod(AdminProMailboxService::class, 'folderCreationCandidates');
        $method->setAccessible(true);

        return $method->invoke(new AdminProMailboxService, $mailboxes, $leaf);
    }

    private function htmlContent(string $html): array
    {
        $method = new ReflectionMethod(AdminProMailboxService::class, 'htmlContent');
        $method->setAccessible(true);

        return $method->invoke(new AdminProMailboxService, $html);
    }
}
