<x-filament::widget>
    <x-filament::card class="h-full">
        <div
            x-data="{
                time: '',
                currentDate: '',

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                },

                updateTime() {
                    const now = new Date();

                    this.time = now.toLocaleTimeString('uk-UA', {
                        timeZone: 'Europe/Kyiv',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    this.currentDate = now.toLocaleDateString('uk-UA', {
                        timeZone: 'Europe/Kyiv',
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long'
                    });
                }
            }"
            x-init="init()"
            class="flex h-full flex-col justify-center"
        >
            <div class="text-5xl font-bold text-white">
                <span x-text="time"></span>
            </div>

            <div class="mt-2 text-lg capitalize text-gray-400">
                <span x-text="currentDate"></span>
            </div>

            <div class="mt-3 text-sm text-gray-500">
                Час за Києвом
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
