<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class HolidayTheme
{
    /**
     * @return array{key: string, greeting: string}|null
     */
    public static function current(?CarbonInterface $moment = null): ?array
    {
        $timezone = config('app.timezone', 'Europe/Kyiv');
        $date = $moment
            ? CarbonImmutable::instance($moment)->setTimezone($timezone)->startOfDay()
            : CarbonImmutable::now($timezone)->startOfDay();

        $monthDay = $date->format('m-d');

        if ($monthDay >= '12-28' || $monthDay <= '01-02') {
            return self::theme('new-year', 'Полтавський обласний молодіжний центр вітає вас з Новим роком!');
        }

        if ($monthDay >= '12-23' && $monthDay <= '12-27') {
            return self::theme('christmas', 'Полтавський обласний молодіжний центр вітає вас з Різдвом Христовим!');
        }

        $easter = self::orthodoxEaster($date->year);
        if ($date->betweenIncluded($easter->subDay(), $easter->addDay())) {
            return self::theme('easter', 'Полтавський обласний молодіжний центр вітає вас зі світлим святом Великодня!');
        }

        if ($date->isSameDay(self::vyshyvankaDay($date->year))) {
            return self::theme('vyshyvanka', 'Полтавський обласний молодіжний центр вітає вас із Днем вишиванки!');
        }

        if ($monthDay >= '08-23' && $monthDay <= '08-24') {
            return self::theme('independence', 'Полтавський обласний молодіжний центр вітає вас із Днем Державного Прапора та Днем Незалежності України!');
        }

        if ($monthDay >= '08-11' && $monthDay <= '08-12') {
            return self::theme('youth', 'Полтавський обласний молодіжний центр вітає вас із Днем молоді!');
        }

        return null;
    }

    private static function theme(string $key, string $greeting): array
    {
        return compact('key', 'greeting');
    }

    private static function vyshyvankaDay(int $year): CarbonImmutable
    {
        $date = CarbonImmutable::create($year, 5, 1, 0, 0, 0, config('app.timezone'));
        $firstThursday = $date->dayOfWeek === CarbonInterface::THURSDAY
            ? $date
            : $date->next(CarbonInterface::THURSDAY);

        return $firstThursday->addWeeks(2);
    }

    private static function orthodoxEaster(int $year): CarbonImmutable
    {
        $a = $year % 4;
        $b = $year % 7;
        $c = $year % 19;
        $d = (19 * $c + 15) % 30;
        $e = (2 * $a + 4 * $b - $d + 34) % 7;
        $month = intdiv($d + $e + 114, 31);
        $day = (($d + $e + 114) % 31) + 1;

        // Julian calendar differs from Gregorian by 13 days in 1900–2099.
        return CarbonImmutable::create($year, $month, $day, 0, 0, 0, config('app.timezone'))
            ->addDays(13);
    }
}
