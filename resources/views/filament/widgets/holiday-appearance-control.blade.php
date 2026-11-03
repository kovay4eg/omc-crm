<x-filament::widget>
    <style>
        .holiday-control { padding: 24px; border: 1px solid rgba(96, 165, 250, .32); border-radius: 18px; background: linear-gradient(135deg, #171b2b, #17171f 58%, #20213a); box-shadow: 0 10px 28px rgba(0, 0, 0, .16); }
        .holiday-control__heading { display: flex; gap: 12px; align-items: center; margin-bottom: 18px; }
        .holiday-control__heading span { font-size: 28px; }
        .holiday-control__heading h2 { margin: 0; color: #fff; font-size: 18px; font-weight: 800; }
        .holiday-control__heading p { margin: 3px 0 0; color: #a5b4fc; font-size: 13px; }
        .holiday-control__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .holiday-control__item { display: flex; min-width: 0; align-items: center; justify-content: space-between; gap: 18px; padding: 18px; border: 1px solid #313442; border-radius: 14px; background: rgba(17, 19, 29, .82); }
        .holiday-control__state { color: #fff; font-size: 15px; font-weight: 800; }
        .holiday-control__state small { display: block; margin-top: 5px; color: #9ca3af; font-size: 12px; font-weight: 500; line-height: 1.45; }
        .holiday-control__button { flex: 0 0 auto; min-width: 150px; min-height: 44px; padding: 10px 16px; border: 0; border-radius: 10px; color: #fff; cursor: pointer; font-weight: 800; transition: transform .18s ease, filter .18s ease; }
        .holiday-control__button:hover { transform: translateY(-1px); filter: brightness(1.08); }
        .holiday-control__button:focus-visible { outline: 3px solid #fbbf24; outline-offset: 3px; }
        .holiday-control__button:disabled { cursor: wait; opacity: .6; }
        .holiday-control__button--on { background: #dc2626; }
        .holiday-control__button--off { background: #16a34a; }
        @media (max-width: 900px) { .holiday-control__grid { grid-template-columns: 1fr; } }
        @media (max-width: 560px) { .holiday-control__item { align-items: stretch; flex-direction: column; } .holiday-control__button { width: 100%; } }
    </style>

    <section class="holiday-control">
        <div class="holiday-control__heading">
            <span aria-hidden="true">✨</span>
            <div>
                <h2>Святкове оформлення сайту</h2>
                <p>Керування доступне лише AdminPro.</p>
            </div>
        </div>

        <div class="holiday-control__grid">
            <div class="holiday-control__item">
                <div class="holiday-control__state">
                    Святкові логотипи: {{ $holidayLogosEnabled ? 'увімкнено' : 'вимкнено' }}
                    <small>Після увімкнення тема логотипа змінюється автоматично за датою свята.</small>
                </div>
                <button type="button" wire:click="toggleHolidayLogos" wire:loading.attr="disabled" wire:target="toggleHolidayLogos" class="holiday-control__button {{ $holidayLogosEnabled ? 'holiday-control__button--on' : 'holiday-control__button--off' }}">
                    {{ $holidayLogosEnabled ? 'Вимкнути' : 'Увімкнути' }}
                </button>
            </div>

            <div class="holiday-control__item">
                <div class="holiday-control__state">
                    Гірлянда і сніг: {{ $winterDecorEnabled ? 'увімкнено' : 'вимкнено' }}
                    <small>Легка гірлянда під шапкою та декоративний сніг на всіх публічних сторінках.</small>
                </div>
                <button type="button" wire:click="toggleWinterDecor" wire:loading.attr="disabled" wire:target="toggleWinterDecor" class="holiday-control__button {{ $winterDecorEnabled ? 'holiday-control__button--on' : 'holiday-control__button--off' }}">
                    {{ $winterDecorEnabled ? 'Вимкнути' : 'Увімкнути' }}
                </button>
            </div>
        </div>
    </section>
</x-filament::widget>
