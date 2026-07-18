<x-filament::widget>
<style>
    .maintenance-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 24px;
        border: 1px solid #34363d;
        border-radius: 16px;
        background: #1b1b1f;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .16);
    }

    .maintenance-control--active {
        border-color: rgba(239, 68, 68, .72);
        background: rgba(127, 29, 29, .18);
    }

    .maintenance-control-state {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-size: 17px;
        font-weight: 800;
    }

    .maintenance-control-state--active {
        color: #fca5a5;
    }

    .maintenance-control-indicator {
        width: 11px;
        height: 11px;
        flex: 0 0 11px;
        border-radius: 50%;
        background: #22c55e;
    }

    .maintenance-control-indicator--active {
        background: #ef4444;
        box-shadow: 0 0 0 7px rgba(239, 68, 68, .12);
        animation: maintenance-control-pulse 1.5s ease-in-out infinite;
    }

    .maintenance-control-description {
        max-width: 700px;
        margin: 9px 0 0;
        color: #a1a1aa;
        font-size: 14px;
        line-height: 1.5;
    }

    .maintenance-control-button {
        min-width: 260px;
        min-height: 58px;
        padding: 14px 24px;
        border: 0;
        border-radius: 11px;
        color: #fff;
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: .02em;
        line-height: 1.2;
        text-transform: uppercase;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .maintenance-control-button:hover {
        transform: translateY(-2px);
    }

    .maintenance-control-button:focus-visible {
        outline: 3px solid #fbbf24;
        outline-offset: 3px;
    }

    .maintenance-control-button:disabled {
        cursor: wait;
        opacity: .65;
    }

    .maintenance-control-button--enable {
        background: #dc2626;
        box-shadow: 0 8px 20px rgba(220, 38, 38, .28);
    }

    .maintenance-control-button--enable:hover {
        background: #ef4444;
    }

    .maintenance-control-button--disable {
        background: #16a34a;
        box-shadow: 0 8px 20px rgba(22, 163, 74, .26);
    }

    .maintenance-control-button--disable:hover {
        background: #22c55e;
    }

    @keyframes maintenance-control-pulse {
        50% { opacity: .55; transform: scale(.78); }
    }

    @media (max-width: 768px) {
        .maintenance-control {
            align-items: stretch;
            flex-direction: column;
        }

        .maintenance-control-button {
            width: 100%;
        }
    }
</style>
    <section class="maintenance-control {{ $maintenanceMode ? 'maintenance-control--active' : '' }}">
        <div>
            <div class="maintenance-control-state {{ $maintenanceMode ? 'maintenance-control-state--active' : '' }}">
                <span class="maintenance-control-indicator {{ $maintenanceMode ? 'maintenance-control-indicator--active' : '' }}"></span>
                {{ $maintenanceMode ? 'Технічні роботи увімкнено' : 'Сайт доступний для відвідувачів' }}
            </div>

            <p class="maintenance-control-description">
                @if ($maintenanceMode)
                    Публічна частина сайту тимчасово закрита. Адмін-панель залишається доступною лише для працівників.
                @else
                    Увімкніть режим лише тоді, коли потрібно тимчасово закрити фронтенд для оновлення сайту.
                @endif
            </p>
        </div>

        <button
            type="button"
            wire:click="toggleMaintenanceMode"
            wire:loading.attr="disabled"
            wire:confirm="{{ $maintenanceMode ? 'Відкрити сайт для відвідувачів?' : 'Увімкнути технічні роботи? Відвідувачі тимчасово не матимуть доступу до сайту.' }}"
            class="maintenance-control-button {{ $maintenanceMode ? 'maintenance-control-button--disable' : 'maintenance-control-button--enable' }}"
            style="min-width:260px;min-height:58px;padding:14px 24px;border:0;border-radius:11px;color:#fff;cursor:pointer;font-size:14px;font-weight:800;letter-spacing:.02em;line-height:1.2;text-transform:uppercase;background:{{ $maintenanceMode ? '#16a34a' : '#dc2626' }};box-shadow:0 8px 20px {{ $maintenanceMode ? 'rgba(22,163,74,.26)' : 'rgba(220,38,38,.28)' }};"
        >
            {{ $maintenanceMode ? 'Вимкнути технічні роботи' : 'Увімкнути технічні роботи' }}
        </button>
    </section>
</x-filament::widget>
