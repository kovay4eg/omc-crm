<x-filament::widget wire:poll.15m="$refresh">
    <x-filament::card>
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-gray-400">
                    Погода в Полтаві
                </div>

                @if ($weather['available'])
                    <div class="mt-1 text-3xl font-bold text-white">
                        {{ $weather['temperature'] > 0 ? '+' : '' }}{{ $weather['temperature'] }}°C
                    </div>
                    <div class="mt-1 text-sm text-gray-500">
                        Оновлено о {{ $weather['updated_at'] }}
                    </div>
                @else
                    <div class="mt-1 text-lg font-semibold text-gray-200">
                        Дані тимчасово недоступні
                    </div>
                    <div class="mt-1 text-sm text-gray-500">
                        Спробуємо оновити автоматично
                    </div>
                @endif
            </div>

            @if ($weather['available'])
                <div class="text-lg text-gray-300">
                    {{ $weather['icon'] }} {{ $weather['condition'] }}
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>
