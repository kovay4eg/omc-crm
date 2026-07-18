@php
    $contactAddress = $settings?->contact_address ?: 'м. Полтава, просп. Віталія Грицаєнка, 25';
    $contactPhone = $settings?->contact_phone ?: '+380 (095) 580-90-62';
    $contactEmail = $settings?->contact_email ?: 'poltomc@gmail.com';
    $contactPhoneLink = preg_replace('/[^0-9+]/', '', $contactPhone);
    $defaultMapUrl = 'https://www.google.com/maps?q=' . rawurlencode($contactAddress) . '&output=embed';
    $mapUrl = $settings?->google_maps_url ?: $defaultMapUrl;

    $socialNetworks = [
        ['enabled' => $settings?->facebook_enabled, 'url' => $settings?->facebook_url, 'name' => 'Facebook', 'icon' => 'facebook.svg'],
        ['enabled' => $settings?->instagram_enabled, 'url' => $settings?->instagram_url, 'name' => 'Instagram', 'icon' => 'instagram.svg'],
        ['enabled' => $settings?->telegram_enabled, 'url' => $settings?->telegram_url, 'name' => 'Telegram', 'icon' => 'telegram.svg'],
        ['enabled' => $settings?->youtube_enabled, 'url' => $settings?->youtube_url, 'name' => 'YouTube', 'icon' => 'youtube.svg'],
        ['enabled' => $settings?->tiktok_enabled, 'url' => $settings?->tiktok_url, 'name' => 'TikTok', 'icon' => 'tiktok.svg'],
    ];
@endphp

<style>
    .contacts-section {
        position: relative;
        overflow: hidden;
        padding: 94px 0 84px;
        background: #fff;
        color: #171717;
        scroll-margin-top: 90px;
    }

    .contacts-content {
        position: relative;
        z-index: 1;
    }

    .contacts-heading {
        position: relative;
        display: flex;
        min-height: clamp(120px, 13vw, 170px);
        align-items: center;
        margin-bottom: clamp(42px, 5vw, 70px);
    }

    .contacts-watermark {
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

    .contacts-watermark-track {
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

    .contacts-title {
        position: relative;
        z-index: 1;
        margin: 0;
        color: #2d35c8;
        font-size: clamp(24px, 3vw, 36px);
        font-weight: 800;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .contacts-card,
    .contacts-map {
        height: 100%;
        overflow: hidden;
        border-radius: 20px;
        background: #eeecff;
        box-shadow: 0 12px 26px rgba(30, 27, 117, .06);
    }

    .contacts-card {
        display: flex;
        flex-direction: column;
        padding: clamp(26px, 3vw, 38px);
    }

    .contacts-card-title {
        margin: 0 0 18px;
        color: #2d35c8;
        font-size: clamp(20px, 2.2vw, 28px);
        font-weight: 800;
        text-transform: uppercase;
    }

    .contacts-list {
        display: grid;
        margin: 0;
    }

    .contacts-list-item {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
        padding: 17px 0;
        border-top: 1px solid #d9d7f7;
    }

    .contacts-list-item:last-child {
        border-bottom: 1px solid #d9d7f7;
    }

    .contacts-list-icon {
        display: grid;
        width: 28px;
        height: 28px;
        place-items: center;
        border-radius: 50%;
        border: 1px solid #3430d2;
        background: #fff;
        color: #3430d2;
        font-size: 15px;
        font-style: normal;
        font-weight: 800;
    }

    .contacts-list-label {
        display: block;
        margin-bottom: 3px;
        color: #5f6191;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .contacts-list-value,
    .contacts-list-value:hover {
        color: #171717;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.4;
        text-decoration: none;
    }

    .contacts-socials {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 28px;
    }

    .contacts-social-link {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid #d1d0fa;
        border-radius: 50%;
        background: #fff;
        transition: transform .2s ease, background .2s ease;
    }

    .contacts-social-link:hover {
        background: #e7e6ff;
        transform: translateY(-3px);
    }

    .contacts-social-link img {
        width: 18px;
        height: 18px;
        object-fit: contain;
    }

    .contacts-map iframe {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 428px;
        border: 0;
    }

    @media (max-width: 767px) {
        .contacts-section {
            padding: 74px 0;
        }

        .contacts-heading {
            min-height: 110px;
            margin-bottom: 34px;
        }

        .contacts-map iframe {
            min-height: 340px;
        }
    }
</style>

<section id="contacts-section" class="contacts-section">
    <div class="container-1200 contacts-content">
        <div class="contacts-heading">
            <div class="contacts-watermark" aria-hidden="true">
                <div class="contacts-watermark-track" id="contactsWatermarkTrack">
                    КОНТАКТИ&nbsp;&nbsp;КОНТАКТИ&nbsp;&nbsp;КОНТАКТИ&nbsp;&nbsp;КОНТАКТИ
                </div>
            </div>

            <h2 class="contacts-title">Контакти</h2>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-5">
                <article class="contacts-card">
                    <h3 class="contacts-card-title">Наші контакти</h3>

                    <div class="contacts-list">
                        <div class="contacts-list-item">
                            <i class="contacts-list-icon" aria-hidden="true">⌖</i>
                            <div>
                                <span class="contacts-list-label">Адреса</span>
                                <span class="contacts-list-value">{{ $contactAddress }}</span>
                            </div>
                        </div>

                        <div class="contacts-list-item">
                            <i class="contacts-list-icon" aria-hidden="true">☎</i>
                            <div>
                                <span class="contacts-list-label">Телефон адміністратора</span>
                                <a class="contacts-list-value" href="tel:{{ $contactPhoneLink }}">{{ $contactPhone }}</a>
                            </div>
                        </div>

                        <div class="contacts-list-item">
                            <i class="contacts-list-icon" aria-hidden="true">✉</i>
                            <div>
                                <span class="contacts-list-label">Електронна пошта</span>
                                <a class="contacts-list-value" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                            </div>
                        </div>
                    </div>

                    @if (collect($socialNetworks)->contains(fn ($network) => $network['enabled'] && $network['url']))
                        <div class="contacts-socials" aria-label="Соціальні мережі">
                            @foreach ($socialNetworks as $network)
                                @if ($network['enabled'] && $network['url'])
                                    <a
                                        class="contacts-social-link"
                                        href="{{ $network['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="{{ $network['name'] }}"
                                    >
                                        <img src="{{ asset('icons/' . $network['icon']) }}" alt="">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </article>
            </div>

            <div class="col-12 col-lg-7">
                <div class="contacts-map">
                    <iframe
                        src="{{ $mapUrl }}"
                        title="Карта розташування Обласного молодіжного центру"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const section = document.getElementById('contacts-section');
        const watermarkTrack = document.getElementById('contactsWatermarkTrack');

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
