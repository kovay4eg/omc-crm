@php
    $footerContactSettings = \App\Models\HomepageSetting::first();
    $footerSettings = \App\Models\FooterSetting::first();

    $footerAddress = $footerContactSettings?->contact_address ?: 'м. Полтава, просп. Віталія Грицаєнка, 25';
    $footerPhone = $footerContactSettings?->contact_phone ?: '+380 (095) 580-90-62';
    $footerEmail = $footerContactSettings?->contact_email ?: 'poltomc@gmail.com';
    $footerPhoneLink = preg_replace('/[^0-9+]/', '', $footerPhone);
    $partnerLogos = array_values(array_filter($footerSettings?->partner_logos ?? []));
    $footerSocialNetworks = [
        ['enabled' => $footerContactSettings?->facebook_enabled, 'url' => $footerContactSettings?->facebook_url, 'name' => 'Facebook', 'icon' => 'facebook.svg'],
        ['enabled' => $footerContactSettings?->instagram_enabled, 'url' => $footerContactSettings?->instagram_url, 'name' => 'Instagram', 'icon' => 'instagram.svg'],
        ['enabled' => $footerContactSettings?->telegram_enabled, 'url' => $footerContactSettings?->telegram_url, 'name' => 'Telegram', 'icon' => 'telegram.svg'],
        ['enabled' => $footerContactSettings?->youtube_enabled, 'url' => $footerContactSettings?->youtube_url, 'name' => 'YouTube', 'icon' => 'youtube.svg'],
        ['enabled' => $footerContactSettings?->tiktok_enabled, 'url' => $footerContactSettings?->tiktok_url, 'name' => 'TikTok', 'icon' => 'tiktok.svg'],
    ];
@endphp

<style>
    .site-footer {
        background: #f0f1ff;
        padding-bottom: 84px;
    }

    .site-footer-inner {
        display: flex;
        min-height: 152px;
        align-items: center;
        justify-content: space-between;
        gap: 32px;
        padding-top: 32px;
        padding-bottom: 32px;
    }

    .site-footer-contacts {
        flex: 1 1 520px;
    }

    .site-footer-title {
        margin: 0 0 14px;
        color: #151515;
        font-size: 15px;
        font-weight: 800;
        line-height: 1;
        text-transform: uppercase;
    }

    .site-footer-contact-list {
        display: grid;
        gap: 7px;
    }

    .site-footer-contact-row {
        display: grid;
        grid-template-columns: 18px minmax(0, 1fr);
        gap: 8px;
        align-items: center;
        color: #171717;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.35;
    }

    .site-footer-contact-icon {
        color: #2d35c8;
        font-size: 16px;
        font-style: normal;
        font-weight: 800;
        text-align: center;
    }

    .site-footer-contact-row a {
        color: inherit;
        text-decoration: none;
    }

    .site-footer-contact-row a:hover {
        color: #2d35c8;
        text-decoration: underline;
    }

    .site-footer-socials {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 16px;
    }

    .site-footer-social-link {
        display: grid;
        width: 28px;
        height: 28px;
        place-items: center;
        border: 1px solid #cfd2ff;
        border-radius: 50%;
        background: #fff;
        transition: background .2s ease, transform .2s ease;
    }

    .site-footer-social-link:hover {
        background: #e4e5ff;
        transform: translateY(-2px);
    }

    .site-footer-social-link img {
        width: 13px;
        height: 13px;
        object-fit: contain;
    }

    .site-footer-partners {
        display: grid;
        flex: 0 1 720px;
        grid-template-columns: repeat(3, minmax(140px, 1fr));
        gap: 28px;
        align-items: center;
    }

    .site-footer-partner-logo {
        display: grid;
        min-width: 0;
        min-height: 88px;
        place-items: center;
    }

    .site-footer-partner-logo img {
        display: block;
        width: clamp(148px, 13vw, 190px);
        max-width: 100%;
        max-height: 88px;
        object-fit: contain;
    }

    .site-footer-credit {
        padding: 18px 15px 22px;
        border-top: 1px solid rgba(43, 36, 193, .12);
        color: #8b8da8;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.6;
        text-align: center;
    }

    .site-footer-credit__line {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0 9px;
    }

    .site-footer-credit__year {
        display: block;
        margin-top: 5px;
    }

    @media (max-width: 767px) {
        .site-footer {
            padding-bottom: 72px;
        }

        .site-footer-inner {
            display: grid;
            min-height: 0;
            gap: 28px;
            padding-top: 28px;
            padding-bottom: 28px;
        }

        .site-footer-contact-row {
            font-size: 12px;
        }

        .site-footer-partners {
            width: 100%;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px 12px;
        }

        .site-footer-partner-logo {
            min-width: 0;
            min-height: 72px;
        }

        .site-footer-partner-logo img {
            width: min(100%, 118px);
            min-width: 0;
            max-width: 118px;
            max-height: 72px;
        }

        .site-footer-credit {
            padding-top: 16px;
            padding-bottom: 18px;
            font-size: 11px;
        }
    }
</style>

<footer class="site-footer">
    <div class="container-1200 site-footer-inner">
        <section class="site-footer-contacts" aria-label="Контакти">
            <h2 class="site-footer-title">Контакти</h2>

            <div class="site-footer-contact-list">
                <div class="site-footer-contact-row">
                    <i class="site-footer-contact-icon" aria-hidden="true">⌖</i>
                    <span>Адреса: {{ $footerAddress }}</span>
                </div>

                <div class="site-footer-contact-row">
                    <i class="site-footer-contact-icon" aria-hidden="true">☎</i>
                    <a href="tel:{{ $footerPhoneLink }}">Телефон: {{ $footerPhone }}</a>
                </div>

                <div class="site-footer-contact-row">
                    <i class="site-footer-contact-icon" aria-hidden="true">✉</i>
                    <a href="mailto:{{ $footerEmail }}">Електронна адреса: {{ $footerEmail }}</a>
                </div>
            </div>

            @if (collect($footerSocialNetworks)->contains(fn ($network) => $network['enabled'] && $network['url']))
                <div class="site-footer-socials" aria-label="Соціальні мережі">
                    @foreach ($footerSocialNetworks as $network)
                        @if ($network['enabled'] && $network['url'])
                            <a
                                class="site-footer-social-link"
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
        </section>

        @if ($partnerLogos)
            <section class="site-footer-partners" aria-label="Партнери та спонсори">
                @foreach ($partnerLogos as $logo)
                    <div class="site-footer-partner-logo">
                        <img
                            src="{{ asset('storage/' . $logo) }}"
                            alt="Логотип партнера {{ $loop->iteration }}"
                        >
                    </div>
                @endforeach
            </section>
        @endif
    </div>

    <div class="site-footer-credit">
        <div class="site-footer-credit__line">
            <span>Website developed by Roman Koshovyi</span>
            <span aria-hidden="true">•</span>
            <span>Design by Karyna Shupyk</span>
        </div>
        <span class="site-footer-credit__year">© {{ now()->year }}</span>
    </div>
</footer>
