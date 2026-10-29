<?php

namespace Tests\Unit;

use App\Services\MailLanguageDetector;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MailLanguageDetectorTest extends TestCase
{
    #[DataProvider('languageSamples')]
    public function test_it_detects_whether_translation_should_be_offered(string $text, string $expected): void
    {
        $this->assertSame($expected, app(MailLanguageDetector::class)->detect($text));
    }

    public static function languageSamples(): array
    {
        return [
            'Ukrainian' => ['Доброго дня! Дякуємо за ваше звернення.', 'uk'],
            'English' => ['Hello, we received your documents and will reply soon.', 'non-uk'],
            'Russian' => ['Здравствуйте, мы получили ваше обращение.', 'non-uk'],
            'Too short to decide' => ['Hello', 'und'],
        ];
    }
}
