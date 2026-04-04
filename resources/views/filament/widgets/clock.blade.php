<x-filament::widget>
    <x-filament::card class="h-full">
        <div
            x-data="{
                time: '',
                currentDate: '',
                holiday: '',
                prediction: '',

                holidays: {
                    '01-01': 'Новий рік',
                    '01-07': 'Різдво Христове',
                    '03-08': 'Міжнародний жіночий день',
                    '04-20': 'Великдень',
                    '05-01': 'День праці',
                    '05-09': 'День перемоги',
                    '06-28': 'День Конституції України',
                    '08-24': 'День незалежності України',
                    '10-14': 'День захисників України',
                    '12-25': 'Різдво (григоріанське)'
                },

                predictions: [
                    'Сьогодні вдалий день для нових ідей 💡',
                    'Можливі приємні новини 📩',
                    'Час завершити старі справи ✅',
                    'День підходить для відпочинку 😌',
                    'Очікуй невелику несподіванку 🎁',
                    'Сконцентруйся на головному 🎯',
                    'Сьогодні все піде за планом 🚀'
                ],

                init() {
                    this.updateTime();

                    const now = new Date();

                    // DATE KEY
                    const dateKey =
                        now.getFullYear() + '-' +
                        String(now.getMonth() + 1).padStart(2, '0') + '-' +
                        String(now.getDate()).padStart(2, '0');

                    // HOLIDAY
                    const holidayKey =
                        String(now.getMonth() + 1).padStart(2, '0') + '-' +
                        String(now.getDate()).padStart(2, '0');

                    this.holiday = this.holidays[holidayKey] ?? 'Звичайний день';

                    // USER ID (передаємо з Blade)
                    const userId = {{ auth()->id() ?? 0 }};

                    // 🔥 deterministic random
                    const seed = this.hashCode(userId + '-' + dateKey);
                    const index = Math.abs(seed) % this.predictions.length;

                    this.prediction = this.predictions[index];

                    setInterval(() => this.updateTime(), 1000);
                },

                updateTime() {
                    const now = new Date();

                    this.time = now.toLocaleTimeString('uk-UA', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    this.currentDate = now.toLocaleDateString('uk-UA', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long'
                    });
                },

                // 🔥 simple hash
                hashCode(str) {
                    let hash = 0;
                    for (let i = 0; i < str.length; i++) {
                        hash = ((hash << 5) - hash) + str.charCodeAt(i);
                        hash |= 0;
                    }
                    return hash;
                }
            }"
            x-init="init()"
            class="h-full flex flex-col justify-center"
        >

            <!-- TIME -->
            <div class="text-5xl font-bold text-white">
                <span x-text="time"></span>
            </div>

            <!-- DATE -->
            <div class="text-lg text-gray-400 mt-2 capitalize">
                <span x-text="currentDate"></span>
            </div>

            <!-- HOLIDAY -->
            <div class="text-sm text-gray-500 mt-3">
                🎉 <span x-text="holiday"></span>
            </div>

            <!-- PREDICTION -->
            <div class="text-sm text-gray-400 mt-1 italic">
                🔮 <span x-text="prediction"></span>
            </div>

        </div>
    </x-filament::card>
</x-filament::widget>