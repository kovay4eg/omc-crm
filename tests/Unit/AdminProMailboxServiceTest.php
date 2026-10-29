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
                <table style="width: 100%;"><tr><td><img src="cid:apple-logo" alt="Apple"></td></tr></table>
                <p onclick="alert('unsafe')">Hello Nataliia,</p>
                <p>Your confirmation is required.</p>
                <img src="https://example.com/banner.png" srcset="https://tracker.example/banner-2x.png 2x" alt="Banner">
                <img src="http://unsafe.example/tracker.png" alt="Unsafe tracker">
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
        $this->assertStringContainsString('<style>', $content['html']);
        $this->assertStringContainsString('@media only screen', $content['html']);
        $this->assertStringNotContainsString('<script', $content['html']);
        $this->assertStringNotContainsString('javascript:', $content['html']);
        $this->assertStringNotContainsString('onclick=', $content['html']);
        $this->assertStringContainsString('src="cid:apple-logo"', $content['html']);
        $this->assertStringContainsString('src="https://example.com/banner.png"', $content['html']);
        $this->assertStringNotContainsString('srcset=', $content['html']);
        $this->assertStringNotContainsString('src="http://unsafe.example', $content['html']);
        $this->assertStringContainsString('target="_blank"', $content['html']);
        $this->assertSame([
            [
                'label' => 'Complete verification form',
                'url' => 'https://developer.apple.com/enroll/verify?token=abc&value=1',
            ],
        ], $content['links']);
    }

    public function test_it_uses_html_alternative_when_plain_part_contains_stylesheet_noise(): void
    {
        $plain = "96\n\n@media only screen { body { color: red; } }\nHello Nataliia,";
        $html = '<html><body><p>Hello Nataliia,</p><a href="https://developer.apple.com/verify">Verification form</a></body></html>';

        $content = $this->plainContent($plain, $html);

        $this->assertSame("Hello Nataliia,\nVerification form", $content['body']);
        $this->assertSame([
            [
                'label' => 'Verification form',
                'url' => 'https://developer.apple.com/verify',
            ],
        ], $content['links']);
    }

    public function test_it_describes_regular_and_nested_inline_attachments(): void
    {
        $structure = (object) [
            'parts' => [
                (object) [
                    'type' => 1,
                    'subtype' => 'ALTERNATIVE',
                    'parts' => [
                        (object) ['type' => 0, 'subtype' => 'PLAIN'],
                        (object) ['type' => 0, 'subtype' => 'HTML'],
                    ],
                ],
                (object) [
                    'type' => 3,
                    'subtype' => 'PDF',
                    'disposition' => 'attachment',
                    'encoding' => 3,
                    'bytes' => 2048,
                    'dparameters' => [(object) [
                        'attribute' => 'filename',
                        'value' => '=?UTF-8?B?0J/Rg9C10L0uUERGPw==?=',
                    ]],
                ],
                (object) [
                    'type' => 1,
                    'subtype' => 'RELATED',
                    'parts' => [
                        (object) [
                            'type' => 5,
                            'subtype' => 'PNG',
                            'disposition' => 'inline',
                            'id' => '<apple-logo>',
                            'bytes' => 512,
                            'parameters' => [(object) ['attribute' => 'name', 'value' => 'logo.png']],
                        ],
                    ],
                ],
            ],
        ];

        $attachments = $this->attachmentDescriptors($structure);

        $this->assertCount(2, $attachments);
        $this->assertSame('2', $attachments[0]['part']);
        $this->assertSame('application/pdf', $attachments[0]['mime_type']);
        $this->assertSame('3.1', $attachments[1]['part']);
        $this->assertSame('apple-logo', $attachments[1]['content_id']);
        $this->assertTrue($attachments[1]['inline']);
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

    private function plainContent(string $plain, ?string $html): array
    {
        $method = new ReflectionMethod(AdminProMailboxService::class, 'plainContent');
        $method->setAccessible(true);

        return $method->invoke(new AdminProMailboxService, $plain, $html);
    }

    private function attachmentDescriptors(object $structure): array
    {
        $method = new ReflectionMethod(AdminProMailboxService::class, 'attachmentDescriptors');
        $method->setAccessible(true);

        return $method->invoke(new AdminProMailboxService, $structure);
    }
}
