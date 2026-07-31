<div>
    <!-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius -->
</div>
@php
    $isCancelled = $event->status === \App\Enums\EventStatus::Cancelled;
    $isRescheduled = $event->status === \App\Enums\EventStatus::Rescheduled;
    $isExternalRegistration = $event->registration_type === 'external';
    $shareVersion = $event->updated_at?->timestamp ?? $event->getKey();
    $eventShareUrl = secure_url('events/' . $event->getKey() . '?share=' . $shareVersion);
    $eventShareImageUrl = secure_url('events/' . $event->getKey() . '/share-image.jpg?v=' . $shareVersion);
@endphp

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-favicon />
    <x-social-meta
        :title="$smmTitle"
        :description="$smmDescription"
        :image="$eventShareImageUrl"
        image-type="image/jpeg"
        :url="$eventShareUrl"
        type="article"
    />

    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body { margin: 0; padding: 0; overflow-x: hidden; }
        body { background: #fff; color: #171717; font-family: 'Commissioner', sans-serif; }
        .event-detail-page { position: relative; min-height: 100vh; padding: 150px 0 100px; overflow: hidden; }
        .event-detail-container { position: relative; z-index: 1; width: min(1100px, calc(100% - 40px)); margin: 0 auto; }
        .event-detail-watermark { position: absolute; top: 145px; left: 0; width: 100vw; overflow: hidden; color: #eef0ff; font-size: clamp(64px, 11vw, 160px); font-weight: 800; line-height: 1; white-space: nowrap; pointer-events: none; }
        .event-detail-kicker { position: relative; z-index: 1; margin: 0 0 18px; color: #2d35c8; font-size: 14px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .event-detail-card { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(270px, .85fr) minmax(0, 1.35fr); overflow: hidden; border-radius: 22px; background: #ececff; box-shadow: 0 18px 40px rgba(30, 27, 117, .09); }
        .event-detail-poster { min-height: 390px; background: #d8d9f1; }
        .event-detail-poster img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .event-detail-placeholder { display: grid; width: 100%; height: 100%; min-height: 390px; place-items: center; color: #6870bf; font-weight: 800; text-transform: uppercase; }
        .event-detail-content { display: flex; flex-direction: column; padding: clamp(26px, 5vw, 52px); }
        .event-detail-status { display: inline-flex; width: fit-content; margin-bottom: 16px; padding: 7px 11px; border-radius: 999px; color: #fff; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .event-detail-status--rescheduled { background: #e48b00; }
        .event-detail-status--cancelled { border: 1px solid #df3939; background: #fff; color: #df3939; }
        .event-detail-title { margin: 0 0 18px; color: #171717; font-size: clamp(26px, 4vw, 44px); font-weight: 800; line-height: 1.08; text-transform: uppercase; }
        .event-detail-description { margin: 0; color: #40404a; font-size: 16px; line-height: 1.65; white-space: pre-line; }
        .event-detail-meta { display: flex; flex-wrap: wrap; gap: 12px 18px; margin-top: 25px; color: #202aa9; font-size: 14px; font-weight: 800; }
        .event-detail-note { margin-top: 18px; color: #7a5700; font-size: 14px; font-weight: 700; line-height: 1.45; }
        .event-detail-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 30px; }
        .event-detail-action { display: inline-flex; min-height: 46px; align-items: center; justify-content: center; padding: 12px 18px; border: 1px solid #3430d2; border-radius: 10px; background: #3430d2; color: #fff; font-size: 14px; font-weight: 800; text-decoration: none; transition: transform .2s ease, background .2s ease; }
        .event-detail-action:hover { background: #2420b9; color: #fff; transform: translateY(-2px); }
        .event-detail-action--muted { border-color: #a7a7b1; background: #a7a7b1; pointer-events: none; }
        .event-detail-back { display: inline-flex; margin-top: 30px; color: #2d35c8; font-size: 14px; font-weight: 800; text-decoration: none; }
        .event-detail-back:hover { color: #201fb1; }
        @media (max-width: 767px) { .event-detail-page { padding-top: 115px; padding-bottom: 72px; } .event-detail-card { grid-template-columns: 1fr; } .event-detail-poster, .event-detail-placeholder { min-height: 250px; } }
    </style>
</head>
<body>
    <x-preloader />
    <x-header />

    <main class="event-detail-page">
        <div class="event-detail-watermark" aria-hidden="true">АНОНС ЗАХОДУ&nbsp;&nbsp;АНОНС ЗАХОДУ</div>

        <div class="event-detail-container">
            <p class="event-detail-kicker">Анонс заходу</p>

            <article class="event-detail-card">
                <div class="event-detail-poster">
                    @if ($event->image)
                        <img src="{{ route('events.image', ['event' => $event]) }}" alt="Афіша заходу {{ $event->title }}">
                    @else
                        <div class="event-detail-placeholder">Афіша заходу</div>
                    @endif
                </div>

                <div class="event-detail-content">
                    @if ($isRescheduled)
                        <span class="event-detail-status event-detail-status--rescheduled">Перенесено</span>
                    @elseif ($isCancelled)
                        <span class="event-detail-status event-detail-status--cancelled">Скасовано</span>
                    @endif

                    <h1 class="event-detail-title">{{ $event->title }}</h1>
                    <p class="event-detail-description">{{ $event->description }}</p>

                    <div class="event-detail-meta">
                        <span>▣ {{ $event->event_date->format('d.m.Y') }}</span>
                        <span>◷ {{ $event->event_date->format('H:i') }}</span>
                        @if ($event->show_available_slots && $event->max_participants !== null)
                            <span>Вільно: {{ $event->available_participants }}</span>
                        @endif
                    </div>

                    @if ($isRescheduled && $event->reschedule_public && $event->reschedule_reason)
                        <p class="event-detail-note">Причина перенесення: {{ $event->reschedule_reason }}</p>
                    @elseif ($isCancelled)
                        <p class="event-detail-note">{{ $event->cancel_public && $event->cancel_reason ? 'Причина скасування: ' . $event->cancel_reason : 'Захід скасовано.' }}</p>
                    @endif

                    <div class="event-detail-actions">
                        @if ($event->has_registration_button)
                            @if (! $event->registration_is_available)
                                <span class="event-detail-action event-detail-action--muted">Реєстрація закрита</span>
                            @elseif ($isExternalRegistration)
                                <a class="event-detail-action" href="{{ $event->google_form_url }}" target="_blank" rel="noopener noreferrer">Зареєструватися</a>
                            @else
                                <a class="event-detail-action" href="{{ url('/#events-section') }}">Зареєструватися на сайті</a>
                            @endif
                        @endif
                    </div>
                </div>
            </article>

            <x-share-buttons
                :url="$eventShareUrl"
                :title="$smmTitle"
                :description="$smmDescription"
            />

            <a href="{{ url('/#events-section') }}" class="event-detail-back">← Усі анонси заходів</a>
        </div>
    </main>

    <x-footer />
</body>
</html>
