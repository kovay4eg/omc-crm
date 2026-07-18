<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $summary->event->title }} — Підсумок заходу</title>

    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body { margin: 0; padding: 0; overflow-x: hidden; }
        body { background: #fff; color: #171717; font-family: 'Commissioner', sans-serif; }
        .summary-page { min-height: 100vh; padding: 150px 0 100px; }
        .summary-container { position: relative; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .summary-watermark { position: absolute; top: 86px; left: 0; width: 100vw; overflow: hidden; color: #eef0ff; font-size: clamp(65px, 11vw, 160px); font-weight: 800; letter-spacing: -.05em; line-height: 1; white-space: nowrap; pointer-events: none; }
        .summary-title { position: relative; z-index: 1; margin: 0 0 36px; color: #2d35c8; font-size: clamp(24px, 3vw, 36px); font-weight: 800; text-transform: uppercase; }
        .summary-preview-note { position: relative; z-index: 2; display: inline-block; margin-bottom: 20px; padding: 9px 14px; border-radius: 999px; background: #fff1bf; color: #734f00; font-size: 14px; font-weight: 700; }
        .summary-event-card { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(220px, .8fr) minmax(0, 1.5fr); overflow: hidden; border-radius: 20px; background: #e9e9ff; }
        .summary-event-poster { min-height: 260px; background: #d8d9f1; }
        .summary-event-poster img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .summary-event-placeholder { display: grid; width: 100%; height: 100%; min-height: 260px; place-items: center; color: #6870bf; font-weight: 700; text-transform: uppercase; }
        .summary-event-content { padding: clamp(22px, 4vw, 42px); }
        .summary-event-content h1 { margin: 0 0 15px; color: #171717; font-size: clamp(22px, 3vw, 34px); font-weight: 800; line-height: 1.15; text-transform: uppercase; }
        .summary-event-content p { margin: 0; color: #40404a; font-size: 16px; line-height: 1.6; }
        .summary-event-meta { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 22px; color: #202aa9; font-size: 14px; font-weight: 800; }
        .summary-content { position: relative; z-index: 1; max-width: 1000px; margin-top: 42px; }
        .summary-content h2 { margin: 0 0 16px; color: #2d35c8; font-size: clamp(22px, 2.5vw, 31px); font-weight: 800; text-transform: uppercase; }
        .summary-text { color: #303039; font-size: 16px; line-height: 1.72; }
        .summary-gallery { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 32px; }
        .summary-gallery-item { overflow: hidden; border-radius: 16px; background: #d8d9f1; aspect-ratio: 1 / .72; }
        .summary-gallery-item img { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
        .summary-gallery-item:hover img { transform: scale(1.04); }
        .summary-back { display: inline-flex; margin-top: 36px; color: #2d35c8; font-weight: 800; text-decoration: none; }
        .summary-back:hover { color: #201fb1; }
        @media (max-width: 767px) { .summary-page { padding-top: 115px; } .summary-event-card { grid-template-columns: 1fr; } .summary-event-poster { min-height: 220px; } .summary-gallery { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; } }
    </style>
</head>
<body>
    <x-preloader />

    <x-header />

    <main class="summary-page">
        <div class="summary-watermark" aria-hidden="true">
            ПІДСУМКИ ЗАХОДІВ&nbsp;&nbsp;ПІДСУМКИ ЗАХОДІВ
        </div>

        <div class="summary-container">
            @if ($isPreview)
                <div class="summary-preview-note">
                    Попередній перегляд — цей підсумок ще може бути не опублікований на сайті.
                </div>
            @endif

            <h2 class="summary-title">Підсумки заходів</h2>

            <article class="summary-event-card">
                <div class="summary-event-poster">
                    @if ($summary->event->image)
                        <img src="{{ asset('storage/' . $summary->event->image) }}" alt="Афіша заходу {{ $summary->event->title }}">
                    @else
                        <div class="summary-event-placeholder">Афіша заходу</div>
                    @endif
                </div>

                <div class="summary-event-content">
                    <h1>{{ $summary->event->title }}</h1>
                    <p>{{ $summary->event->description }}</p>

                    <div class="summary-event-meta">
                        <span>▣ {{ $summary->event->event_date->format('d.m.Y') }}</span>
                        <span>◷ {{ $summary->event->event_date->format('H:i') }}</span>
                    </div>
                </div>
            </article>

            <section class="summary-content">
                <h2>Підсумок заходу</h2>

                <div class="summary-text">
                    {!! nl2br(e($summary->summary)) !!}
                </div>

                @if ($summary->images->isNotEmpty())
                    <div class="summary-gallery">
                        @foreach ($summary->images as $image)
                            <a
                                class="summary-gallery-item"
                                href="{{ asset('storage/' . $image->image) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <img
                                    src="{{ asset('storage/' . $image->image) }}"
                                    alt="{{ $image->alt_text ?: 'Фото з заходу ' . $summary->event->title }}"
                                >
                            </a>
                        @endforeach
                    </div>
                @endif

                <a href="{{ url('/#event-summaries-section') }}" class="summary-back">
                    ← Усі підсумки заходів
                </a>
            </section>
        </div>
    </main>

    <x-footer />
</body>
</html>
