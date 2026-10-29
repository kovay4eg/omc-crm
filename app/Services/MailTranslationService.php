<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MailTranslationService
{
    public function configured(): bool
    {
        return filled(config('admin_pro_mail.translation.deepl_key'));
    }

    public function translateToUkrainian(string $subject, string $body): array
    {
        throw_unless($this->configured(), RuntimeException::class, 'Сервіс перекладу ще не налаштовано.');
        throw_if(
            mb_strlen($subject) + mb_strlen($body) > 60000,
            RuntimeException::class,
            'Цей лист завеликий для автоматичного перекладу.',
        );

        $cacheKey = 'admin-pro-mail:translation:uk:v1:'.hash('sha256', $subject."\0".$body);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($subject, $body): array {
            $response = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => 'DeepL-Auth-Key '.config('admin_pro_mail.translation.deepl_key'),
                ])
                ->timeout(20)
                ->retry(2, 250)
                ->post((string) config('admin_pro_mail.translation.deepl_url'), [
                    'text' => [$subject, $body],
                    'target_lang' => 'UK',
                    'preserve_formatting' => true,
                ]);

            throw_unless($response->successful(), RuntimeException::class, 'Сервіс перекладу тимчасово недоступний.');

            $translations = $response->json('translations');
            throw_unless(is_array($translations) && count($translations) === 2, RuntimeException::class, 'Не вдалося отримати переклад листа.');

            return [
                'subject' => trim((string) ($translations[0]['text'] ?? $subject)),
                'body' => trim((string) ($translations[1]['text'] ?? $body)),
                'source_language' => strtoupper((string) ($translations[1]['detected_source_language'] ?? $translations[0]['detected_source_language'] ?? '')),
            ];
        });
    }
}
