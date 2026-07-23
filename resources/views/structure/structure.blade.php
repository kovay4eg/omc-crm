@php
    $bookingSettings = $settings ?? \App\Models\HomepageSetting::query()->first();
    $bookingAddress = $bookingSettings?->contact_address ?: 'м. Полтава, просп. Віталія Грицаєнка, 25';
    $bookingPhone = $bookingSettings?->contact_phone ?: '+380 (095) 580-90-62';
    $bookingEmail = $bookingSettings?->contact_email ?: 'poltomc@gmail.com';
    $bookingPhoneLink = preg_replace('/[^0-9+]/', '', $bookingPhone);
    $bookingSocialNetworks = [
        ['enabled' => $bookingSettings?->facebook_enabled, 'url' => $bookingSettings?->facebook_url, 'name' => 'Facebook', 'icon' => 'facebook.svg'],
        ['enabled' => $bookingSettings?->instagram_enabled, 'url' => $bookingSettings?->instagram_url, 'name' => 'Instagram', 'icon' => 'instagram.svg'],
        ['enabled' => $bookingSettings?->telegram_enabled, 'url' => $bookingSettings?->telegram_url, 'name' => 'Telegram', 'icon' => 'telegram.svg'],
        ['enabled' => $bookingSettings?->youtube_enabled, 'url' => $bookingSettings?->youtube_url, 'name' => 'YouTube', 'icon' => 'youtube.svg'],
        ['enabled' => $bookingSettings?->tiktok_enabled, 'url' => $bookingSettings?->tiktok_url, 'name' => 'TikTok', 'icon' => 'tiktok.svg'],
    ];
@endphp

{{-- Головний контейнер секції "Де ми можемо зустрітися?" --}}
<section
    id="structure-section"
    class="meeting-section position-relative py-5 overflow-hidden"
>
    <div class="container position-relative z-3">
        {{-- Заголовок --}}
        <div class="title-container position-relative mb-4 py-2 d-flex justify-content-between align-items-center">
            {{-- Фоновий рухомий текст --}}
            <div class="bg-watermark-wrapper d-block" aria-hidden="true">
                <div id="bgWatermark" class="bg-watermark-track text-uppercase">
                    <span class="bg-watermark-text">ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ</span>
                    <span class="bg-watermark-text">ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ</span>
                    <span class="bg-watermark-text">ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ</span>
                </div>
            </div>

            <h2 class="main-title text-uppercase fw-extrabold m-0 position-relative z-2">
                Де ми можемо <span class="text-blue">зустрітися?</span>
            </h2>

            <div id="mainFlower" class="interactive-flower">
                <img src="/images/icons/flower.svg" alt="Декоративна квітка" width="75" height="75">
            </div>
        </div>

        {{-- Сітка карток --}}
        <div id="mainGridView" class="row g-4 mb-5">
            {{-- Картка 01 --}}
            <div class="col-12 col-md-4">
                <div class="meeting-card h-100" role="button" tabindex="0" aria-controls="hub-view" aria-expanded="false" onclick="switchView('hub-view', 'МОЛОДІЖНИЙ ХАБ', this)" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); switchView('hub-view', 'МОЛОДІЖНИЙ ХАБ', this); }">
                    <div class="card-img-box">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Молодіжний хаб">
                    </div>

                    <div class="card-content d-flex justify-content-between align-items-end">
                        <div>
                            <div class="card-num">01</div>
                            <h4 class="card-title">МОЛОДІЖНИЙ ХАБ</h4>
                        </div>

                        <div class="arrow-container">
                            <div class="arrow-mask-icon"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Картка 02 --}}
            <div class="col-12 col-md-4">
                <div class="meeting-card h-100" role="button" tabindex="0" aria-controls="studio-view" aria-expanded="false" onclick="switchView('studio-view', 'КОНТЕНТА', this)" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); switchView('studio-view', 'КОНТЕНТА', this); }">
                    <div class="card-img-box">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Аудіовізуальна студія">
                    </div>

                    <div class="card-content d-flex justify-content-between align-items-end">
                        <div>
                            <div class="card-num">02</div>
                            <h4 class="card-title">АУДІОВІЗУАЛЬНА СТУДІЯ «КОНТЕНТА»</h4>
                        </div>

                        <div class="arrow-container">
                            <div class="arrow-mask-icon"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Картка 03 --}}
            <div class="col-12 col-md-4">
                <div class="meeting-card h-100" role="button" tabindex="0" aria-controls="mobile-view" aria-expanded="false" onclick="switchView('mobile-view', 'МОБІЛЬНА РОБОТА', this)" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); switchView('mobile-view', 'МОБІЛЬНА РОБОТА', this); }">
                    <div class="card-img-box">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Мобільна молодіжна робота">
                    </div>

                    <div class="card-content d-flex justify-content-between align-items-end">
                        <div>
                            <div class="card-num">03</div>
                            <h4 class="card-title">МОБІЛЬНА МОЛОДІЖНА РОБОТА</h4>
                        </div>

                        <div class="arrow-container">
                            <div class="arrow-mask-icon"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Контент 01: Хаб --}}
        <div id="hub-view" class="view-panel d-none">
            <div class="panel-inner-content pt-2 pb-5">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="main-title text-uppercase fw-extrabold text-blue">Молодіжний хаб</h2>
                    <button class="btn-close-view" type="button" onclick="resetView(true)" aria-label="Закрити інформацію про простір"></button>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-lg-10">
                        <p class="lead-text mb-3">
                            <strong>Молодіжний хаб</strong> – це сучасний мультифункціональний простір площею 549,2 кв.м, створений для всебічного розвитку, змістовного дозвілля та соціалізації молоді.
                        </p>

                        <p class="sub-text text-muted">
                            Приміщення Хабу відремонтовано та облаштовано в рамках проєкту ГО «NOVA UNITED» за фінансової підтримки Дитячого фонду ООН (ЮНІСЕФ) та уряду Японії.
                        </p>
                    </div>
                </div>

                <div class="photo-grid mb-5">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-8">
                            <div class="red-block" style="height: 420px;"></div>
                        </div>

                        <div class="col-12 col-md-4 d-flex flex-column gap-3">
                            <div class="red-block flex-grow-1" style="min-height: 200px;"></div>
                            <div class="red-block flex-grow-1" style="min-height: 200px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Контент 02: Контента --}}
        <div id="studio-view" class="view-panel d-none">
            <div class="panel-inner-content pt-2 pb-5">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="main-title text-uppercase fw-extrabold text-blue">Аудіовізуальна студія «КОНТЕНТА»</h2>
                    <button class="btn-close-view" type="button" onclick="resetView(true)" aria-label="Закрити інформацію про простір"></button>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-lg-10">
                        <p class="lead-text mb-3">
                            <strong>Аудіовізуальна студія «Контента»</strong> – це сучасна медіа-лабораторія, розташована в самому серці нашого центру. Простір створений для креаторів, подкастерів та відеоконтенту.
                        </p>
                    </div>
                </div>

                <div class="photo-grid mb-5">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 300px;"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Контент 03: Мобільна робота --}}
        <div id="mobile-view" class="view-panel d-none">
            <div class="panel-inner-content pt-2 pb-5">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="main-title text-uppercase fw-extrabold text-blue">Мобільна молодіжна робота</h2>
                    <button class="btn-close-view" type="button" onclick="resetView(true)" aria-label="Закрити інформацію про простір"></button>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-lg-10">
                        <p class="lead-text mb-3">
                            Команда Полтавського обласного молодіжного центру активно впроваджує напрямок мобільної молодіжної роботи – формат для роботи ОТГ та виїздів.
                        </p>
                    </div>
                </div>

                <div class="photo-grid mb-5">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 240px;"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 240px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Нижня лінія бронювання --}}
        <button
            type="button"
            class="booking-line booking-line-trigger d-flex w-100 justify-content-between align-items-center py-4 border-top border-bottom mt-4"
            data-bs-toggle="modal"
            data-bs-target="#bookingContactModal"
            aria-label="Відкрити контакти для бронювання простору"
        >
            <div class="d-flex align-items-center gap-3">
                <div class="star-icon">✦</div>
                <span class="booking-text text-uppercase fw-bold">Можливість забронювати простір</span>
            </div>

            <div class="arrow-mask-icon static-arrow" aria-hidden="true"></div>
        </button>
    </div>
</section>

<div class="modal fade booking-modal" id="bookingContactModal" tabindex="-1" aria-labelledby="bookingContactModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="booking-modal__eyebrow mb-1">БРОНЮВАННЯ ПРОСТОРУ</p>
                    <h2 class="booking-modal__title mb-0" id="bookingContactModalTitle">Зв’яжіться з нами</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
            </div>

            <div class="modal-body pt-3">
                <p class="booking-modal__intro">Напишіть або зателефонуйте адміністратору, щоб узгодити дату, формат і доступність простору.</p>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="booking-modal__contact">
                            <span class="booking-modal__icon" aria-hidden="true">⌖</span>
                            <div>
                                <span class="booking-modal__label">Адреса</span>
                                <span class="booking-modal__value">{{ $bookingAddress }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <a class="booking-modal__contact booking-modal__contact--link h-100" href="tel:{{ $bookingPhoneLink }}">
                            <span class="booking-modal__icon" aria-hidden="true">☎</span>
                            <span>
                                <span class="booking-modal__label">Телефон</span>
                                <span class="booking-modal__value">{{ $bookingPhone }}</span>
                            </span>
                        </a>
                    </div>

                    <div class="col-12 col-sm-6">
                        <a class="booking-modal__contact booking-modal__contact--link h-100" href="mailto:{{ $bookingEmail }}">
                            <span class="booking-modal__icon" aria-hidden="true">✉</span>
                            <span>
                                <span class="booking-modal__label">Електронна пошта</span>
                                <span class="booking-modal__value booking-modal__email">{{ $bookingEmail }}</span>
                            </span>
                        </a>
                    </div>
                </div>

                @if (collect($bookingSocialNetworks)->contains(fn ($network) => $network['enabled'] && $network['url']))
                    <div class="booking-modal__socials mt-4" aria-label="Соціальні мережі">
                        <span class="booking-modal__label d-block w-100">Соціальні мережі</span>

                        @foreach ($bookingSocialNetworks as $network)
                            @if ($network['enabled'] && $network['url'])
                                <a href="{{ $network['url'] }}" target="_blank" rel="noopener noreferrer" class="booking-modal__social-link" aria-label="{{ $network['name'] }}">
                                    <img src="{{ asset('icons/' . $network['icon']) }}" alt="">
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&display=swap');

    .meeting-section {
        font-family: 'Montserrat', sans-serif;
    }

    .text-blue {
        color: #2B24C1 !important;
    }

    .fw-extrabold {
        font-weight: 900;
    }

    .title-container {
        width: 100%;
        min-height: 58px;
        margin-bottom: 3.5rem !important;
    }

    .bg-watermark-wrapper {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100vw;
        overflow: hidden;
        pointer-events: none;
        transform: translate(-50%, -50%);
        z-index: 1;
    }

    .bg-watermark-track {
        display: flex;
        width: max-content;
        will-change: transform;
    }

    .bg-watermark-text {
        display: block;
        flex: 0 0 auto;
        padding-right: 2rem;
        font-size: clamp(64px, 11vw, 160px);
        font-weight: 800;
        line-height: 1;
        color: #eef0ff;
        opacity: 1;
        white-space: nowrap;
        letter-spacing: -.05em;
    }

    .title-container .main-title {
        color: #2B24C1;
    }

    .interactive-flower {
        display: none;
    }

    .interactive-flower img {
        will-change: transform;
        transition: transform .1s linear;
    }

    .arrow-mask-icon {
        width: 28px;
        height: 28px;
        background: url('/images/icons/arrow-inactive.svg') center / contain no-repeat;
        transition: transform .3s cubic-bezier(.25, 1, .5, 1), background-image .25s ease, filter .25s ease;
        will-change: transform;
    }

    .arrow-mask-icon.static-arrow {
        width: 42px;
        height: 42px;
    }

    .meeting-card {
        overflow: hidden;
        background-color: #EAE5F3;
        border-radius: 18px;
        cursor: pointer;
        transition: transform .3s ease, background-color .3s ease, box-shadow .3s ease;
    }

    .meeting-card:hover:not(.card-active) {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(43, 36, 193, .08);
    }

    .meeting-card:focus-visible {
        outline: 3px solid #2B24C1;
        outline-offset: 4px;
    }

    .meeting-card.card-active {
        background-color: #2B24C1 !important;
    }

    .meeting-card.card-active .card-title {
        color: #fff !important;
    }

    .meeting-card.card-active .card-num {
        -webkit-text-stroke: 2px #fff !important;
    }

    .meeting-card.card-active .arrow-mask-icon {
        background-image: url('/images/icons/arrow-active.svg') !important;
        filter: brightness(0) invert(1);
    }

    .meeting-card:hover:not(.card-active) .arrow-mask-icon {
        transform: translateX(6px);
    }

    .meeting-card .arrow-container {
        display: none;
    }

    .card-img-box img {
        display: block;
        width: 100%;
        height: 170px;
        object-fit: cover;
    }

    .card-content {
        min-height: 205px;
        padding: 1.5rem;
    }

    .card-num {
        margin-bottom: .55rem;
        font-family: 'Commissioner', sans-serif;
        font-size: 96px;
        font-weight: 800;
        line-height: .9;
        color: transparent;
        -webkit-text-stroke: 2px #131DA4;
        letter-spacing: 0;
    }

    .card-title {
        margin: 0;
        font-size: .82rem;
        font-weight: 800;
        color: #000;
    }

    .view-panel {
        opacity: 0;
        scroll-margin-top: 118px;
        transform: translateY(15px);
        transition: opacity .4s ease, transform .4s ease;
    }

    .view-panel.active {
        margin-top: 40px;
        opacity: 1;
        transform: translateY(0);
    }

    .view-panel.d-none {
        display: none !important;
    }

    .red-block {
        width: 100%;
        background-color: #EAE5F3;
        border: 2px dashed rgba(43, 36, 193, .15);
        border-radius: 24px;
    }

    .btn-close-view {
        width: 28px;
        height: 28px;
        border: none;
        background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232B24C1'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 1 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center / contain no-repeat;
        cursor: pointer;
        transition: transform .3s ease;
    }

    .btn-close-view:hover {
        transform: rotate(90deg);
    }

    .booking-line {
        margin-top: 30px !important;
        border-top: 1px solid rgba(43, 36, 193, .12) !important;
        border-bottom: 1px solid rgba(43, 36, 193, .12) !important;
    }

    .booking-line:hover .static-arrow {
        transform: translateX(12px);
    }

    .booking-line-trigger {
        border-right: 0;
        border-left: 0;
        background: transparent;
        text-align: left;
        cursor: pointer;
    }

    .booking-line-trigger:focus-visible {
        outline: 3px solid #2B24C1;
        outline-offset: 5px;
    }

    .star-icon {
        color: #2B24C1;
        font-size: 1.5rem;
    }

    .booking-text {
        color: #2B24C1;
    }

    .booking-modal .modal-content {
        border-radius: 24px;
        background: #f7f7ff;
        box-shadow: 0 24px 70px rgba(24, 23, 96, .22);
    }

    .booking-modal .modal-header,
    .booking-modal .modal-body {
        padding-right: clamp(1.25rem, 4vw, 2rem);
        padding-left: clamp(1.25rem, 4vw, 2rem);
    }

    .booking-modal__eyebrow,
    .booking-modal__label {
        color: #666a9c;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .booking-modal__title {
        color: #2B24C1;
        font-size: clamp(1.45rem, 4vw, 2rem);
        font-weight: 900;
    }

    .booking-modal__intro {
        margin-bottom: 1.25rem;
        color: #27283f;
        font-family: 'Commissioner', sans-serif;
        font-size: .98rem;
        line-height: 1.55;
    }

    .booking-modal__contact {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        min-width: 0;
        padding: 15px;
        border: 1px solid #dfdffc;
        border-radius: 16px;
        background: #fff;
        color: #20213d;
        text-decoration: none;
    }

    .booking-modal__contact--link {
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .booking-modal__contact--link:hover {
        border-color: #2B24C1;
        box-shadow: 0 8px 20px rgba(43, 36, 193, .1);
        color: #20213d;
        transform: translateY(-2px);
    }

    .booking-modal__icon {
        display: grid;
        width: 32px;
        height: 32px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        background: #ecebff;
        color: #2B24C1;
        font-family: Arial, sans-serif;
        font-weight: 900;
    }

    .booking-modal__value {
        display: block;
        margin-top: 3px;
        overflow-wrap: anywhere;
        color: #20213d;
        font-family: 'Commissioner', sans-serif;
        font-size: .94rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .booking-modal__socials {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .booking-modal__social-link {
        display: grid;
        width: 44px;
        height: 44px;
        place-items: center;
        border: 1px solid #d8d7fa;
        border-radius: 50%;
        background: #fff;
        transition: transform .2s ease, background .2s ease;
    }

    .booking-modal__social-link:hover {
        background: #ecebff;
        transform: translateY(-3px);
    }

    .booking-modal__social-link img {
        width: 19px;
        height: 19px;
        object-fit: contain;
    }

    body.modal-open .omc-a11y-widget {
        z-index: 1040;
    }

    .main-title {
        font-size: 34px;
        line-height: 1.05;
    }

    @media (max-width: 991.98px) {
        .title-container {
            margin-bottom: 2.5rem !important;
        }

        .main-title {
            font-size: 28px;
        }

        .card-content {
            min-height: auto;
        }

        .card-num {
            font-size: clamp(50px, 10vw, 76px);
        }
    }

    @media (max-width: 575.98px) {
        .card-num {
            font-size: clamp(48px, 18vw, 68px);
            -webkit-text-stroke-width: 1.5px;
        }

        .bg-watermark-wrapper {
            display: block !important;
            overflow: hidden;
        }

        .bg-watermark-text {
            display: block;
            visibility: visible;
            opacity: 1;
            font-size: clamp(64px, 19vw, 94px);
        }

        .booking-line {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        .booking-line-trigger > div:first-child {
            min-width: 0;
            gap: .65rem !important;
        }

        .booking-text {
            font-size: .73rem;
            line-height: 1.35;
        }

        .star-icon {
            font-size: 1.2rem;
        }

        .arrow-mask-icon.static-arrow {
            width: 31px;
            height: 31px;
        }
    }
</style>

<script>
    let watermarkAnimationFrame = null;

    function updateWatermarkPosition() {
        const watermark = document.getElementById('bgWatermark');
        const firstText = watermark?.querySelector('.bg-watermark-text');

        if (!watermark || !firstText) {
            return;
        }

        const textWidth = firstText.offsetWidth;

        if (!textWidth) {
            return;
        }

        const scrollOffset = (window.scrollY * 0.45) % textWidth;

        watermark.style.transform = `translate3d(${-textWidth - scrollOffset}px, 0, 0)`;
    }

    function scheduleWatermarkPosition() {
        if (watermarkAnimationFrame !== null) {
            return;
        }

        watermarkAnimationFrame = window.requestAnimationFrame(() => {
            watermarkAnimationFrame = null;
            updateWatermarkPosition();
        });
    }

    function setWatermarkText(text) {
        const watermarkTexts = document.querySelectorAll('.bg-watermark-text');

        watermarkTexts.forEach(watermarkText => {
            watermarkText.innerText = text;
        });

        requestAnimationFrame(updateWatermarkPosition);
    }

    let activeViewId = null;
    let activeCardElement = null;
    let isScrollingToView = false;
    let panelAutoCloseFrame = null;
    let panelScrollTimer = null;

    function scrollToOpenedView(panel) {
        const headerHeight = document.getElementById('header')?.offsetHeight ?? 0;
        const targetPosition = window.scrollY + panel.getBoundingClientRect().top - headerHeight - 24;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        isScrollingToView = true;
        window.clearTimeout(panelScrollTimer);

        window.scrollTo({
            top: Math.max(0, targetPosition),
            behavior: reduceMotion ? 'auto' : 'smooth',
        });

        panelScrollTimer = window.setTimeout(() => {
            isScrollingToView = false;
        }, reduceMotion ? 0 : 1000);
    }

    function closeViewWhenScrolledAway() {
        panelAutoCloseFrame = null;

        if (isScrollingToView || !activeViewId) {
            return;
        }

        const activePanel = document.getElementById(activeViewId);

        if (!activePanel || activePanel.classList.contains('d-none')) {
            return;
        }

        const headerHeight = document.getElementById('header')?.offsetHeight ?? 0;
        const panelPosition = activePanel.getBoundingClientRect();

        if (
            panelPosition.bottom < headerHeight + 24
            || panelPosition.top > window.innerHeight - 80
        ) {
            resetView();
        }
    }

    function schedulePanelAutoClose() {
        if (panelAutoCloseFrame !== null) {
            return;
        }

        panelAutoCloseFrame = window.requestAnimationFrame(closeViewWhenScrolledAway);
    }

    function switchView(targetId, watermarkText, cardElement) {
        const targetView = document.getElementById(targetId);
        const cards = document.querySelectorAll('.meeting-card');
        const panels = document.querySelectorAll('.view-panel');

        if (!targetView) {
            return;
        }

        if (cardElement.classList.contains('card-active')) {
            resetView();

            return;
        }

        cards.forEach(card => {
            card.classList.remove('card-active');
            card.setAttribute('aria-expanded', 'false');
        });

        panels.forEach(panel => {
            panel.classList.remove('active');
            panel.classList.add('d-none');
        });

        cardElement.classList.add('card-active');
        cardElement.setAttribute('aria-expanded', 'true');
        targetView.classList.remove('d-none');
        activeViewId = targetId;
        activeCardElement = cardElement;

        requestAnimationFrame(() => {
            targetView.classList.add('active');
            scrollToOpenedView(targetView);
        });

        if (watermarkText) {
            setWatermarkText(watermarkText);
        }
    }

    function resetView(returnToCard = false) {
        const panels = document.querySelectorAll('.view-panel');
        const cards = document.querySelectorAll('.meeting-card');
        const cardToReturn = returnToCard ? activeCardElement : null;

        panels.forEach(panel => {
            panel.classList.remove('active');
            panel.classList.add('d-none');
        });

        cards.forEach(card => {
            card.classList.remove('card-active');
            card.setAttribute('aria-expanded', 'false');
        });

        activeViewId = null;
        activeCardElement = null;
        isScrollingToView = false;
        window.clearTimeout(panelScrollTimer);

        setWatermarkText('ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ');

        if (!cardToReturn || !document.documentElement.contains(cardToReturn)) {
            return;
        }

        const headerHeight = document.getElementById('header')?.offsetHeight ?? 0;
        const targetPosition = window.scrollY + cardToReturn.getBoundingClientRect().top - headerHeight - 24;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        window.requestAnimationFrame(() => {
            window.scrollTo({
                top: Math.max(0, targetPosition),
                behavior: reduceMotion ? 'auto' : 'smooth',
            });
        });
    }

    window.addEventListener(
        'scroll',
        () => {
            scheduleWatermarkPosition();
            schedulePanelAutoClose();
        },
        { passive: true }
    );

    window.addEventListener(
        'resize',
        scheduleWatermarkPosition
    );

    document.addEventListener(
        'DOMContentLoaded',
        () => {
            scheduleWatermarkPosition();
        }
    );
</script>
