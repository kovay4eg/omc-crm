<?php

namespace Tests\Unit;

use App\Services\MailTranslationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MailTranslationServiceTest extends TestCase
{
    public function test_it_translates_subject_and_body_to_ukrainian(): void
    {
        config()->set('cache.default', 'array');
        config()->set('admin_pro_mail.translation.deepl_key', 'test-key');
        config()->set('admin_pro_mail.translation.deepl_url', 'https://api-free.deepl.com/v2/translate');

        Http::fake([
            'api-free.deepl.com/*' => Http::response([
                'translations' => [
                    ['detected_source_language' => 'EN', 'text' => 'Тема листа'],
                    ['detected_source_language' => 'EN', 'text' => 'Перекладений текст'],
                ],
            ]),
        ]);

        $result = app(MailTranslationService::class)->translateToUkrainian('Email subject', 'Email body');

        $this->assertSame('Тема листа', $result['subject']);
        $this->assertSame('Перекладений текст', $result['body']);
        $this->assertSame('EN', $result['source_language']);
        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'DeepL-Auth-Key test-key')
            && $request['target_lang'] === 'UK'
            && $request['text'] === ['Email subject', 'Email body']
        );
    }
}
