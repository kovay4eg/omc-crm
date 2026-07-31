<style>
    .event-summaries-section {
        position: relative;
        padding: clamp(80px, 10vw, 130px) 0;
        overflow: hidden;
        background: #fff;
    }

    .event-summaries-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 0;
        width: 100vw;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
        transform: translate(-50%, -50%);
    }

    .event-summaries-watermark-track {
        position: absolute;
        top: 50%;
        left: 50%;
        width: max-content;
        color: #eef0ff;
        font-size: clamp(64px, 11vw, 160px);
        font-weight: 800;
        letter-spacing: -.05em;
        line-height: 1;
        white-space: nowrap;
        transform: translate3d(-50%, -50%, 0);
        will-change: transform;
    }

    .event-summaries-content {
        position: relative;
        z-index: 1;
    }

    .event-summaries-heading {
        position: relative;
        display: flex;
        min-height: clamp(120px, 13vw, 170px);
        align-items: center;
        margin-bottom: clamp(42px, 5vw, 70px);
    }

    .event-summaries-title {
        position: relative;
        z-index: 1;
        margin: 0;
        color: #2d35c8;
        font-size: clamp(24px, 3vw, 36px);
        font-weight: 800;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .event-summary-card {
        display: flex;
        height: 100%;
        flex-direction: column;
        overflow: hidden;
        border-radius: 20px;
        background: #ececff;
        box-shadow: 0 12px 28px rgba(42, 52, 155, .08);
    }

    .event-summary-card-poster {
        width: 100%;
        aspect-ratio: 1 / .78;
        overflow: hidden;
        background: #d8d9f1;
    }

    .event-summary-card-poster img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .event-summary-card-placeholder {
        display: grid;
        width: 100%;
        height: 100%;
        place-items: center;
        color: #6870bf;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .event-summary-card-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 18px;
    }

    .event-summary-card-title {
        margin: 0 0 8px;
        color: #121212;
        font-size: 17px;
        font-weight: 800;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .event-summary-card-description {
        display: -webkit-box;
        margin: 0 0 18px;
        overflow: hidden;
        color: #4b4b56;
        font-size: 14px;
        line-height: 1.45;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }

    .event-summary-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: auto;
        color: #202aa9;
        font-size: 13px;
        font-weight: 700;
    }

    .event-summary-card-link {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        margin-top: 16px;
        padding: 11px 16px;
        border-radius: 9px;
        background: #3430d2;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        text-transform: uppercase;
        transition: .2s ease;
    }

    .event-summary-card-link:hover {
        background: #2320b8;
        color: #fff;
        transform: translateY(-2px);
    }

    .event-summaries-empty {
        padding: 28px;
        border: 1px solid #dce0ff;
        border-radius: 16px;
        color: #5f6191;
        text-align: center;
    }

    @media (max-width: 767px) {
        .event-summaries-section {
            padding: 74px 0;
        }

        .event-summaries-heading {
            min-height: 110px;
            margin-bottom: 34px;
        }

        .event-summaries-title {
            font-size: 24px;
        }
    }
</style>

<section id="event-summaries-section" class="event-summaries-section">
    <div class="container-1200 event-summaries-content">
        <div class="event-summaries-heading">
            <div class="event-summaries-watermark" aria-hidden="true">
                <div class="event-summaries-watermark-track" id="eventSummariesWatermarkTrack">
                    ПІДСУМКИ ЗАХОДІВ&nbsp;&nbsp;ПІДСУМКИ ЗАХОДІВ&nbsp;&nbsp;ПІДСУМКИ ЗАХОДІВ
                </div>
            </div>

            <h2 class="event-summaries-title">Підсумки заходів</h2>
        </div>

        @if ($eventSummaries->isNotEmpty())
            <div class="row g-4">
                @foreach ($eventSummaries as $summary)
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="event-summary-card">
                            <div class="event-summary-card-poster">
                                @if ($summary->event->image)
                                    <img
                                        src="{{ route('events.image', ['event' => $summary->event]) }}"
                                        alt="Афіша заходу {{ $summary->event->title }}"
                                    >
                                @else
                                    <div class="event-summary-card-placeholder">Афіша заходу</div>
                                @endif
                            </div>

                            <div class="event-summary-card-body">
                                <h3 class="event-summary-card-title">{{ $summary->event->title }}</h3>

                                <p class="event-summary-card-description">
                                    {{ \Illuminate\Support\Str::limit($summary->event->description, 150) }}
                                </p>

                                <div class="event-summary-card-meta">
                                    <span>▣ {{ $summary->event->event_date->format('d.m.Y') }}</span>
                                    <span>◷ {{ $summary->event->event_date->format('H:i') }}</span>
                                </div>

                                <a
                                    href="{{ route('event-summaries.show', ['eventSummary' => $summary]) }}"
                                    class="event-summary-card-link"
                                >
                                    Підсумок
                                </a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @else
            <div class="event-summaries-empty">
                Опублікованих підсумків заходів поки немає.
            </div>
        @endif
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const section = document.getElementById('event-summaries-section');
        const watermarkTrack = document.getElementById('eventSummariesWatermarkTrack');

        if (!section || !watermarkTrack) {
            return;
        }

        function updateWatermarkPosition() {
            const sectionTop = section.getBoundingClientRect().top + window.scrollY;
            const scrollInsideSection = window.scrollY - sectionTop;
            const offset = scrollInsideSection * .24;

            watermarkTrack.style.transform =
                `translate3d(calc(-50% - ${offset}px), -50%, 0)`;
        }

        window.addEventListener('scroll', updateWatermarkPosition, {
            passive: true,
        });

        updateWatermarkPosition();
    });
</script>
