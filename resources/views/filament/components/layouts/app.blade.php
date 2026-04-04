<x-filament::widget>
    <x-filament::card>

        <div class="mb-4">
            <h2 class="text-xl font-bold text-white">
                📅 Календар івентів
            </h2>
        </div>

        @php
            $events = \App\Models\Event::get()->map(function ($event) {
                $start = \Carbon\Carbon::parse($event->event_date);

                return [
                    'title' => $event->title,
                    'start' => $start->toIso8601String(),
                    'end' => $start->copy()->addHour()->toIso8601String(),
                ];
            })->values();
        @endphp

        <div 
            x-data="{}"
            x-init="
                const calendar = new FullCalendar.Calendar($el, {
                    initialView: 'timeGridWeek',
                    events: {{ json_encode($events) }},
                });
                calendar.render();
            "
            style="min-height:500px"
        ></div>

    </x-filament::card>
</x-filament::widget>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @filamentStyles
    @livewireStyles
</head>
<body class="antialiased">

    {{ $slot }}

    @livewireScripts
    @filamentScripts

    <script>
    document.addEventListener('livewire:init', () => {

        let changed = false;

        function markChanged() {
            changed = true;
        }

        function attachInputs() {
            document.querySelectorAll('input, textarea, select').forEach(el => {
                el.removeEventListener('input', markChanged);
                el.addEventListener('input', markChanged);
            });
        }

        function applyPulse() {
            const btn = document.querySelector('button[type="submit"]');
            if (!btn) return;

            if (changed) {
                btn.style.animation = 'pulseFix 1s infinite';
            } else {
                btn.style.animation = '';
            }
        }

        const observer = new MutationObserver(() => {
            attachInputs();
            applyPulse();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        attachInputs();

    });
    </script>

    <style>
    @keyframes pulseFix {
        0% { transform: scale(1); }
        50% { transform: scale(1.07); }
        100% { transform: scale(1); }
    }
    </style>

</body>
</html>