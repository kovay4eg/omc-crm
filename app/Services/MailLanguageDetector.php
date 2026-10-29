<?php

namespace App\Services;

class MailLanguageDetector
{
    public function detect(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('~https?://\S+|\b[\w.+-]+@[\w.-]+\.[a-z]{2,}\b~iu', ' ', $text) ?? $text;
        preg_match_all('/\p{L}/u', $text, $letterMatches);

        $letters = $letterMatches[0] ?? [];
        $total = count($letters);

        if ($total < 8) {
            return 'und';
        }

        $joined = implode('', $letters);
        $latin = preg_match_all('/\p{Latin}/u', $joined);
        $cyrillic = preg_match_all('/\p{Cyrillic}/u', $joined);

        if ($latin / $total >= 0.25) {
            return 'non-uk';
        }

        if (($total - $latin - $cyrillic) / $total >= 0.25) {
            return 'non-uk';
        }

        if ($cyrillic === 0) {
            return 'und';
        }

        if (preg_match('/[ыэъё]/u', $text) === 1) {
            return 'non-uk';
        }

        if (preg_match('/[іїєґ]/u', $text) === 1) {
            return 'uk';
        }

        $words = array_values(array_filter(preg_split('/[^\p{L}]+/u', $text) ?: []));
        $ukrainianWords = [
            'це', 'що', 'для', 'та', 'і', 'й', 'до', 'від', 'ви', 'ми', 'ваш', 'ваша', 'ваше', 'ваші',
            'лист', 'дякую', 'будь', 'ласка', 'доброго', 'добрий', 'отримали', 'підтвердження', 'звернення',
        ];
        $russianWords = [
            'это', 'что', 'для', 'и', 'до', 'от', 'вы', 'мы', 'ваш', 'ваша', 'ваше', 'ваши',
            'письмо', 'спасибо', 'пожалуйста', 'здравствуйте', 'получили', 'подтверждение', 'обращение',
        ];
        $ukrainianScore = count(array_intersect($words, $ukrainianWords));
        $russianScore = count(array_intersect($words, $russianWords));

        if ($ukrainianScore > $russianScore) {
            return 'uk';
        }

        if ($russianScore > 0) {
            return 'non-uk';
        }

        return 'und';
    }
}
