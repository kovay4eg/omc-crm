<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class Weather extends Widget
{
    protected string $view = 'filament.widgets.weather';

    protected int|string|array $columnSpan = 1;

    protected function getExtraAttributes(): array
    {
        return ['class' => 'self-start'];
    }

    protected function getViewData(): array
    {
        return ['weather' => $this->getWeather()];
    }

    /**
     * @return array{available: bool, temperature?: int, condition?: string, icon?: string, updated_at?: string}
     */
    private function getWeather(): array
    {
        $cacheKey = 'dashboard-weather-poltava';
        $cachedWeather = Cache::get($cacheKey);

        if (is_array($cachedWeather)) {
            return $cachedWeather;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(5)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => 49.5883,
                    'longitude' => 34.5514,
                    'current' => 'temperature_2m,weather_code',
                    'timezone' => 'Europe/Kyiv',
                ]);

            $current = $response->successful() ? $response->json('current') : null;
            $temperature = data_get($current, 'temperature_2m');
            $weatherCode = data_get($current, 'weather_code');

            if (! is_numeric($temperature) || ! is_numeric($weatherCode)) {
                throw new \RuntimeException('Weather service returned incomplete data.');
            }

            [$condition, $icon] = $this->weatherDetails((int) $weatherCode);

            $weather = [
                'available' => true,
                'temperature' => (int) round((float) $temperature),
                'condition' => $condition,
                'icon' => $icon,
                'updated_at' => Carbon::parse((string) data_get($current, 'time'), 'Europe/Kyiv')->format('H:i'),
            ];

            Cache::put($cacheKey, $weather, now()->addMinutes(15));

            return $weather;
        } catch (Throwable) {
            return ['available' => false];
        }
    }

    /** @return array{string, string} */
    private function weatherDetails(int $code): array
    {
        return match ($code) {
            0 => ['Ясно', '☀️'],
            1 => ['Переважно ясно', '🌤️'],
            2 => ['Мінлива хмарність', '⛅'],
            3 => ['Хмарно', '☁️'],
            45, 48 => ['Туман', '🌫️'],
            51, 53, 55, 56, 57 => ['Мряка', '🌦️'],
            61, 63, 65, 66, 67, 80, 81, 82 => ['Дощ', '🌧️'],
            71, 73, 75, 77, 85, 86 => ['Сніг', '🌨️'],
            95, 96, 99 => ['Гроза', '⛈️'],
            default => ['Невідомі умови', '🌡️'],
        };
    }
}
