<style>
    :root {
        --events-blue: #2f2ac8;
        --events-blue-dark: #201cae;
        --events-lilac: #eeecff;
        --events-line: #d9d7f7;
        --events-text: #171717;
    }

    .events-page {
        position: relative;
        min-height: 100vh;
        padding: 94px 0 84px;
        overflow: hidden;
        background: #ffffff;
        color: var(--events-text);
        scroll-margin-top: 90px;
    }

    .events-container {
        position: relative;
        z-index: 2;
        width: min(1200px, calc(100% - 40px));
        margin: 0 auto;
    }

    .events-hero {
        position: relative;
        min-height: 120px;
        margin-bottom: 30px;
    }

    .events-watermark {
        position: absolute;
        top: 154px;
        left: 0;
        z-index: 0;
        width: 100vw;
        height: 180px;
        overflow: hidden;
        pointer-events: none;
        transform: translateY(-50%);
        user-select: none;
    }

    .events-watermark-track {
        position: absolute;
        top: 50%;
        left: 50%;
        width: max-content;
        color: #eeedff;
        font-size: clamp(54px, 9.3vw, 142px);
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        will-change: transform;
        transform: translate3d(-50%, -50%, 0);
    }

    .events-watermark-text {
        display: block;
        padding-right: 52px;
    }

    .events-title {
        position: relative;
        z-index: 1;
        margin: 0;
        padding-top: 39px;
        color: var(--events-blue);
        font-size: clamp(26px, 3vw, 43px);
        font-weight: 800;
        line-height: 1.05;
        text-transform: uppercase;
    }

    .events-grid {
        position: relative;
        z-index: 2;
    }

    .event-grid-item {
        position: relative;
    }

    .event-card {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 0;
        overflow: hidden;
        border-radius: 20px;
        background: var(--events-lilac);
        box-shadow: 0 12px 26px rgba(30, 27, 117, .06);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .event-card:hover {
        z-index: 4;
        transform: translateY(-5px);
        box-shadow: 0 18px 34px rgba(30, 27, 117, .13);
    }

    .event-image {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 0;
        width: 100%;
        aspect-ratio: 1 / .82;
        overflow: hidden;
        border-radius: 0;
        background: #d8d7ee;
    }

    .event-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .event-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        color: #7470b9;
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .event-card.is-cancelled {
        background: #e5e5e9;
    }

    .event-card.is-cancelled .event-image img {
        filter: grayscale(1) brightness(.83);
    }

    .event-card.is-cancelled .event-image::after {
        position: absolute;
        inset: 0;
        content: '';
        background:
            linear-gradient(
                to bottom right,
                transparent calc(50% - 3px),
                rgba(111, 111, 119, .92) calc(50% - 3px),
                rgba(111, 111, 119, .92) calc(50% + 3px),
                transparent calc(50% + 3px)
            );
        pointer-events: none;
    }

    .event-status-badge {
        position: absolute;
        top: 12px;
        left: 50%;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        max-width: calc(100% - 24px);
        min-height: 27px;
        padding: 5px 10px;
        overflow: hidden;
        border-radius: 999px;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        text-overflow: ellipsis;
        text-transform: uppercase;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .13);
        transform: translateX(-50%);
    }

    .event-status-badge--rescheduled {
        background: #e48b00;
    }

    .event-status-badge--cancelled {
        border: 1px solid #e33434;
        background: rgba(255, 255, 255, .96);
        color: #e33434;
        box-shadow: 0 4px 12px rgba(130, 25, 25, .18);
    }

    .event-tooltip {
        position: relative;
        cursor: help;
    }

    .event-tooltip::after {
        position: absolute;
        top: calc(100% + 9px);
        left: 50%;
        z-index: 20;
        width: max-content;
        max-width: min(260px, 70vw);
        padding: 9px 11px;
        border-radius: 8px;
        background: #1c1c25;
        color: #ffffff;
        content: attr(data-tooltip);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        opacity: 0;
        pointer-events: none;
        transform: translate(-50%, -3px);
        transition: opacity .18s ease, transform .18s ease;
    }

    .event-tooltip:hover::after,
    .event-tooltip:focus-visible::after {
        opacity: 1;
        transform: translate(-50%, 0);
    }

    .event-card-body {
        position: relative;
        z-index: 1;
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: calc(82% + 17px) 17px 15px;
    }

    .event-title {
        display: -webkit-box;
        margin: 0 0 9px;
        overflow: hidden;
        color: #171717;
        font-size: clamp(15px, 1.34vw, 18px);
        font-weight: 800;
        line-height: 1.16;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        text-transform: uppercase;
    }

    .event-card.is-cancelled .event-title,
    .event-card.is-cancelled .event-description,
    .event-card.is-cancelled .event-details {
        color: #62626a;
    }

    .event-description {
        display: -webkit-box;
        min-height: 48px;
        margin: 0 0 12px;
        overflow: hidden;
        color: #33333a;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.42;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }

    .event-details {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-top: auto;
        color: #1b1b22;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.2;
    }

    .event-slots {
        color: #22222b;
    }

    .event-slots strong {
        font-weight: 800;
    }

    .event-date-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 9px;
    }

    .event-date-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .event-date-icon {
        width: 15px;
        height: 15px;
        color: var(--events-blue);
    }

    .event-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 38px;
        margin-top: 12px;
    }

    .event-details-link {
        color: #2924c8;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        text-transform: uppercase;
    }

    .event-details-link:hover,
    .event-details-link:focus-visible {
        color: #17128e;
        text-decoration: underline;
    }

    .event-action-shell {
        position: relative;
        display: inline-flex;
        width: 100%;
    }

    .event-register {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 38px;
        padding: 9px 12px;
        border: 0;
        border-radius: 9px;
        background: var(--events-blue);
        color: #ffffff;
        cursor: pointer;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        text-transform: uppercase;
        transition: background .2s ease, transform .2s ease;
    }

    .event-register:hover {
        background: var(--events-blue-dark);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .event-register--disabled {
        background: #a5a5ae;
        cursor: not-allowed;
    }

    .event-register--disabled:hover {
        background: #92929a;
        transform: none;
    }

    .event-action-tooltip {
        position: absolute;
        right: 0;
        bottom: calc(100% + 9px);
        z-index: 20;
        width: max-content;
        max-width: min(260px, 70vw);
        padding: 9px 11px;
        border-radius: 8px;
        background: #1c1c25;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        opacity: 0;
        pointer-events: none;
        transform: translateY(3px);
        transition: opacity .18s ease, transform .18s ease;
    }

    .event-action-shell--disabled:hover .event-action-tooltip,
    .event-action-shell--disabled:focus-within .event-action-tooltip {
        opacity: 1;
        transform: translateY(0);
    }

    .events-show-all {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        margin-top: 31px;
        border: 1px solid var(--events-line);
        border-radius: 10px;
        background: #ffffff;
        color: var(--events-blue);
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
        font-weight: 800;
        text-transform: uppercase;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }

    .events-show-all:hover {
        border-color: var(--events-blue);
        background: var(--events-blue);
        color: #ffffff;
    }

    .events-empty {
        padding: 72px 20px;
        border: 1px solid var(--events-line);
        border-radius: 20px;
        color: var(--events-blue);
        font-size: 19px;
        font-weight: 800;
        text-align: center;
    }

    .event-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(17, 17, 28, .58);
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }

    .event-modal.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .event-modal-dialog {
        width: min(100%, 510px);
        padding: 27px;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 20px 55px rgba(0, 0, 0, .28);
        transform: translateY(14px);
        transition: transform .2s ease;
    }

    .event-modal.is-open .event-modal-dialog {
        transform: translateY(0);
    }

    .event-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 19px;
    }

    .event-modal-title {
        margin: 0;
        color: var(--events-blue);
        font-size: 24px;
        font-weight: 800;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .event-modal-event-name {
        margin: 7px 0 0;
        color: #4b4b55;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.35;
    }

    .event-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        border: 0;
        border-radius: 50%;
        background: #efeff7;
        color: var(--events-blue);
        cursor: pointer;
        font-size: 25px;
        line-height: 1;
    }

    .event-form-group {
        margin-bottom: 14px;
    }

    .event-form-label {
        display: block;
        margin-bottom: 6px;
        color: #202028;
        font-size: 14px;
        font-weight: 700;
    }

    .event-form-input {
        width: 100%;
        min-height: 46px;
        padding: 11px 13px;
        border: 1px solid #cbc9e5;
        border-radius: 9px;
        outline: none;
        color: #1c1c24;
        font-family: inherit;
        font-size: 15px;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .event-form-input:focus {
        border-color: var(--events-blue);
        box-shadow: 0 0 0 3px rgba(47, 42, 200, .12);
    }

    .event-form-message {
        display: none;
        margin: 0 0 14px;
        padding: 10px 12px;
        border-radius: 8px;
        background: #fff0f0;
        color: #a42020;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.35;
    }

    .event-form-message.is-visible {
        display: block;
    }

    .event-modal-submit {
        width: 100%;
        min-height: 47px;
        margin-top: 4px;
        border: 0;
        border-radius: 9px;
        background: var(--events-blue);
        color: #ffffff;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
        font-weight: 800;
        text-transform: uppercase;
        transition: background .2s ease;
    }

    .event-modal-submit:hover {
        background: var(--events-blue-dark);
    }

    .event-modal-submit:disabled {
        cursor: wait;
        opacity: .7;
    }

    .event-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 2100;
        width: min(360px, calc(100vw - 40px));
        padding: 14px 16px;
        border-radius: 10px;
        background: #20202a;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .2);
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
        opacity: 0;
        pointer-events: none;
        transform: translateY(15px);
        transition: opacity .2s ease, transform .2s ease;
    }

    .event-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .event-toast--success {
        background: #147347;
    }

    .event-toast--warning {
        background: #8b5b00;
    }

    @media (max-width: 767.98px) {
        .events-page {
            padding-top: 68px;
        }

        .events-container {
            width: min(100% - 28px, 540px);
        }

        .events-hero {
            min-height: 102px;
            margin-bottom: 22px;
        }

        .events-watermark {
            top: 121px;
            height: 150px;
        }

        .events-title {
            padding-top: 33px;
        }

        .event-modal-dialog {
            padding: 22px 18px;
        }
    }
</style>

<section id="events-section" class="events-page">
    <div class="events-watermark" aria-hidden="true">
        <div class="events-watermark-track" id="eventsWatermarkTrack">
            <span class="events-watermark-text">
                АНОНСИ ЗАХОДІВ&nbsp;&nbsp;АНОНСИ ЗАХОДІВ&nbsp;&nbsp;АНОНСИ ЗАХОДІВ&nbsp;&nbsp;АНОНСИ ЗАХОДІВ&nbsp;&nbsp;АНОНСИ ЗАХОДІВ
            </span>
        </div>
    </div>

    <div class="events-container">
        <section class="events-hero" aria-labelledby="events-title">
            <h1 class="events-title" id="events-title">
                Анонси заходів
            </h1>
        </section>

        @if ($events->isNotEmpty())
            <div class="row g-4 events-grid">
                @foreach ($events as $event)
                    @php
                        $isCancelled = $event->status === \App\Enums\EventStatus::Cancelled;
                        $isRescheduled = $event->status === \App\Enums\EventStatus::Rescheduled;

                        $isInternalForm = $event->registration_type === 'internal';
                        $isGoogleForm = $event->registration_type === 'external';

                        $showRegisterControl = (bool) $event->has_registration_button
                            && ($isInternalForm || $isGoogleForm);

                        $rescheduleTooltip = $isRescheduled
                            && $event->reschedule_public
                            && filled($event->reschedule_reason)
                                ? $event->reschedule_reason
                                : null;

                        $cancelTooltip = $isCancelled
                            ? (
                                $event->cancel_public && filled($event->cancel_reason)
                                    ? $event->cancel_reason
                                    : 'Захід скасовано'
                            )
                            : null;

                        $blockedMessage = match (true) {
                            $isCancelled => 'Захід скасовано.',
                            $event->is_full => 'Реєстрацію закрито: вільних місць немає.',
                            $isGoogleForm && empty($event->google_form_url) => 'Посилання на Google-форму ще не додано.',
                            default => 'Реєстрація на цей захід недоступна.',
                        };

                        $registrationBlocked = !$event->registration_is_available
                            || ($isGoogleForm && empty($event->google_form_url));
                    @endphp

                    <div class="col-12 col-md-6 col-lg-4 event-grid-item">
                        <article class="event-card {{ $isCancelled ? 'is-cancelled' : '' }}">
                            @if ($isRescheduled)
                                <span
                                    class="event-status-badge event-status-badge--rescheduled {{ $rescheduleTooltip ? 'event-tooltip' : '' }}"
                                    @if ($rescheduleTooltip)
                                        data-tooltip="{{ $rescheduleTooltip }}"
                                        tabindex="0"
                                    @endif
                                >
                                    Перенесено
                                </span>
                            @endif

                            @if ($isCancelled)
                                <span
                                    class="event-status-badge event-status-badge--cancelled event-tooltip"
                                    data-tooltip="{{ $cancelTooltip }}"
                                    tabindex="0"
                                >
                                    Скасовано
                                </span>
                            @endif

                            <div class="event-image">
                                @if ($event->image)
                                    <img
                                        src="{{ asset('storage/' . $event->image) }}"
                                        alt="{{ $event->title }}"
                                    >
                                @else
                                    <div class="event-image-placeholder">
                                        Фото заходу
                                    </div>
                                @endif
                            </div>

                            <div class="event-card-body">
                                <h2 class="event-title">{{ $event->title }}</h2>

                                <p class="event-description">{{ $event->description }}</p>

                                <div class="event-details">
                                    @if ($event->show_available_slots)
                                        <div class="event-slots">
                                            <strong>К-сть місць:</strong>
                                            {{ $event->max_participants ?? 'Без обмежень' }}
                                        </div>

                                        @if ($event->max_participants !== null)
                                            <div class="event-slots">
                                                <strong>Вільно:</strong>
                                                {{ $event->available_participants }}
                                            </div>
                                        @endif
                                    @endif

                                    <div class="event-date-row">
                                        <span class="event-date-item">
                                            <svg class="event-date-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                                                <path d="M8 3v4M16 3v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>

                                            {{ $event->event_date->format('d.m.y') }}
                                        </span>

                                        <span class="event-date-item">
                                            <svg class="event-date-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="2"/>
                                                <path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>

                                            {{ $event->event_date->format('H:i') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="event-card-footer">
                                    <a class="event-details-link" href="{{ route('events.show', ['event' => $event]) }}">
                                        Детальніше
                                    </a>

                                    @if ($showRegisterControl)
                                        @if ($registrationBlocked)
                                            <span class="event-action-shell event-action-shell--disabled">
                                                <button
                                                    type="button"
                                                    class="event-register event-register--disabled"
                                                    aria-disabled="true"
                                                    data-registration-closed
                                                    data-message="{{ $blockedMessage }}"
                                                >
                                                    Реєстрація
                                                </button>

                                                <span class="event-action-tooltip" role="tooltip">
                                                    {{ $blockedMessage }}
                                                </span>
                                            </span>
                                        @elseif ($isInternalForm)
                                            <button
                                                type="button"
                                                class="event-register"
                                                data-registration-open
                                                data-event-id="{{ $event->id }}"
                                                data-event-title="{{ $event->title }}"
                                            >
                                                Реєстрація
                                            </button>
                                        @else
                                            <a
                                                class="event-register"
                                                href="{{ $event->google_form_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                Реєстрація
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

        @else
            <div class="events-empty">
                Наразі немає запланованих заходів.
            </div>
        @endif
    </div>
</section>

<div class="event-modal" id="eventRegistrationModal" aria-hidden="true">
    <div
        class="event-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="eventModalTitle"
    >
        <div class="event-modal-header">
            <div>
                <h2 class="event-modal-title" id="eventModalTitle">
                    Реєстрація
                </h2>

                <p class="event-modal-event-name" id="eventModalEventName"></p>
            </div>

            <button
                type="button"
                class="event-modal-close"
                aria-label="Закрити вікно"
                data-modal-close
            >
                ×
            </button>
        </div>

        <form id="eventRegistrationForm">
            <input type="hidden" name="event_id" id="eventModalEventId">

            <div class="event-form-message" id="eventFormMessage"></div>

            <div class="event-form-group">
                <label class="event-form-label" for="registrationName">Ім’я</label>

                <input
                    class="event-form-input"
                    id="registrationName"
                    name="name"
                    type="text"
                    maxlength="255"
                    required
                >
            </div>

            <div class="event-form-group">
                <label class="event-form-label" for="registrationPhone">Телефон</label>

                <input
                    class="event-form-input"
                    id="registrationPhone"
                    name="phone"
                    type="tel"
                    maxlength="30"
                    required
                >
            </div>

            <div class="event-form-group">
                <label class="event-form-label" for="registrationEmail">Email</label>

                <input
                    class="event-form-input"
                    id="registrationEmail"
                    name="email"
                    type="email"
                    maxlength="255"
                    required
                >
            </div>

            <button type="submit" class="event-modal-submit" id="eventModalSubmit">
                Надіслати заявку
            </button>
        </form>
    </div>
</div>

<div class="event-toast" id="eventToast" role="status" aria-live="polite"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('eventRegistrationModal');
        const form = document.getElementById('eventRegistrationForm');
        const formMessage = document.getElementById('eventFormMessage');
        const modalEventId = document.getElementById('eventModalEventId');
        const modalEventName = document.getElementById('eventModalEventName');
        const submitButton = document.getElementById('eventModalSubmit');
        const toast = document.getElementById('eventToast');
        const eventsSection = document.getElementById('events-section');
        const watermarkTrack = document.getElementById('eventsWatermarkTrack');

        let toastTimeout;

        function updateWatermarkPosition() {
            if (!eventsSection || !watermarkTrack) {
                return;
            }

            const sectionTop =
                eventsSection.getBoundingClientRect().top + window.scrollY;

            const scrollInsideSection = window.scrollY - sectionTop;
            const offset = scrollInsideSection * 0.28;

            watermarkTrack.style.transform =
                `translate3d(calc(-50% - ${offset}px), -50%, 0)`;
        }

        function showToast(message, type = 'warning') {
            window.clearTimeout(toastTimeout);

            toast.textContent = message;
            toast.className = `event-toast event-toast--${type} is-visible`;

            toastTimeout = window.setTimeout(() => {
                toast.className = 'event-toast';
            }, 4000);
        }

        function showFormMessage(message) {
            formMessage.textContent = message;
            formMessage.classList.add('is-visible');
        }

        function clearFormMessage() {
            formMessage.textContent = '';
            formMessage.classList.remove('is-visible');
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            clearFormMessage();
        }

        function openModal(button) {
            form.reset();
            clearFormMessage();

            modalEventId.value = button.dataset.eventId;
            modalEventName.textContent = button.dataset.eventTitle;

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            window.setTimeout(() => {
                document.getElementById('registrationName').focus();
            }, 100);
        }

        window.addEventListener('scroll', updateWatermarkPosition, {
            passive: true,
        });

        updateWatermarkPosition();

        document.querySelectorAll('[data-registration-open]').forEach(button => {
            button.addEventListener('click', () => openModal(button));
        });

        document.querySelectorAll('[data-registration-closed]').forEach(button => {
            button.addEventListener('click', () => {
                showToast(button.dataset.message, 'warning');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', event => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            clearFormMessage();

            const formData = new FormData(form);

            submitButton.disabled = true;
            submitButton.textContent = 'Надсилаємо…';

            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(Object.fromEntries(formData.entries())),
                });

                let data = {};

                try {
                    data = await response.json();
                } catch (error) {
                    data = {};
                }

                if (!response.ok) {
                    const validationErrors = data.errors ?? {};
                    const firstError = Object.values(validationErrors)[0];

                    showFormMessage(
                        Array.isArray(firstError)
                            ? firstError[0]
                            : (data.message ?? 'Не вдалося надіслати заявку. Спробуйте ще раз.')
                    );

                    return;
                }

                closeModal();
                showToast(data.message ?? 'Реєстрація успішна.', 'success');

                window.setTimeout(() => {
                    window.location.reload();
                }, 900);
            } catch (error) {
                showFormMessage('Помилка з’єднання. Перевірте інтернет і спробуйте ще раз.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Надіслати заявку';
            }
        });

    });
</script>
