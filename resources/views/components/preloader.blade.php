@php
    $preloaderSettings = \App\Models\HomepageSetting::first();
    $preloaderLogo = $preloaderSettings?->logo
        ? asset('storage/' . $preloaderSettings->logo)
        : asset('storage/homepage/Лого ПОМЦ.png');
@endphp

<script>document.documentElement.classList.add('is-site-loading');</script>

<style>
    html.is-site-loading,
    html.is-site-loading body { overflow: hidden; }

    .site-preloader {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: grid;
        place-items: center;
        overflow: hidden;
        background: #fbfbff;
        opacity: 1;
        transition: opacity .55s ease, visibility .55s ease;
    }

    .site-preloader::before,
    .site-preloader::after {
        position: absolute;
        width: min(55vw, 680px);
        aspect-ratio: 1;
        border-radius: 50%;
        content: '';
        pointer-events: none;
    }

    .site-preloader::before {
        top: -34%;
        left: -16%;
        background: rgba(72, 67, 239, .11);
        animation: preloaderBlobOne 5s ease-in-out infinite alternate;
    }

    .site-preloader::after {
        right: -20%;
        bottom: -42%;
        background: rgba(99, 109, 255, .12);
        animation: preloaderBlobTwo 6s ease-in-out infinite alternate;
    }

    .site-preloader.is-leaving {
        visibility: hidden;
        opacity: 0;
    }

    .site-preloader-content {
        position: relative;
        z-index: 1;
        display: grid;
        justify-items: center;
    }

    .site-preloader-orbit {
        position: absolute;
        width: clamp(180px, 24vw, 250px);
        aspect-ratio: 1;
        border: 1px solid rgba(47, 42, 200, .2);
        border-radius: 50%;
        animation: preloaderOrbit 4.5s linear infinite;
    }

    .site-preloader-orbit::before,
    .site-preloader-orbit::after {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #3430d2;
        content: '';
        box-shadow: 0 0 0 7px rgba(52, 48, 210, .1);
    }

    .site-preloader-orbit::before { top: 12%; left: 8%; }
    .site-preloader-orbit::after { right: 9%; bottom: 11%; background: #6e72ff; }

    .site-preloader-logo-wrap {
        position: relative;
        display: grid;
        width: min(420px, 78vw);
        min-height: 150px;
        place-items: center;
        padding: 24px 30px;
        border: 1px solid rgba(51, 48, 210, .09);
        border-radius: 26px;
        background: rgba(255, 255, 255, .92);
        box-shadow: 0 20px 60px rgba(44, 47, 170, .13);
        animation: preloaderLogoFloat 2.6s ease-in-out infinite;
    }

    .site-preloader-logo-wrap::before {
        position: absolute;
        inset: 8px;
        border: 1px solid rgba(52, 48, 210, .1);
        border-radius: 20px;
        content: '';
        pointer-events: none;
    }

    .site-preloader-logo {
        position: relative;
        z-index: 1;
        display: block;
        width: 100%;
        max-height: 104px;
        object-fit: contain;
        animation: preloaderLogoReveal 1.2s cubic-bezier(.22, 1, .36, 1) both;
    }

    .site-preloader-dots {
        display: flex;
        gap: 8px;
        margin-top: 28px;
    }

    .site-preloader-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #3430d2;
        animation: preloaderDot 1.1s ease-in-out infinite;
    }

    .site-preloader-dots span:nth-child(2) { animation-delay: .15s; }
    .site-preloader-dots span:nth-child(3) { animation-delay: .3s; }

    @keyframes preloaderOrbit { to { transform: rotate(360deg); } }
    @keyframes preloaderLogoFloat { 50% { transform: translateY(-8px); } }
    @keyframes preloaderLogoReveal { from { opacity: 0; transform: scale(.88); } to { opacity: 1; transform: scale(1); } }
    @keyframes preloaderDot { 50% { opacity: .35; transform: translateY(-8px) scale(.85); } }
    @keyframes preloaderBlobOne { to { transform: translate(10%, 14%) scale(1.12); } }
    @keyframes preloaderBlobTwo { to { transform: translate(-12%, -8%) scale(.9); } }

    @media (max-width: 575px) {
        .site-preloader-logo-wrap { min-height: 124px; padding: 18px 22px; border-radius: 22px; }
        .site-preloader-logo { max-height: 78px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .site-preloader *,
        .site-preloader::before,
        .site-preloader::after { animation: none !important; }
    }
</style>

<div id="sitePreloader" class="site-preloader" role="status" aria-live="polite">
    <div class="site-preloader-content">
        <div class="site-preloader-orbit" aria-hidden="true"></div>

        <div class="site-preloader-logo-wrap">
            <img class="site-preloader-logo" src="{{ $preloaderLogo }}" alt="Обласний молодіжний центр">
        </div>

        <div class="site-preloader-dots" aria-hidden="true"><span></span><span></span><span></span></div>
        <span class="visually-hidden">Завантаження сайту</span>
    </div>
</div>

<script>
    (() => {
        const preloader = document.getElementById('sitePreloader');

        if (!preloader) return;

        let isHidden = false;

        function hidePreloader() {
            if (isHidden) return;

            isHidden = true;
            preloader.classList.add('is-leaving');
            document.documentElement.classList.remove('is-site-loading');
            window.setTimeout(() => preloader.remove(), 600);
        }

        function finishLoading() { window.setTimeout(hidePreloader, 320); }

        if (document.readyState === 'complete') {
            finishLoading();
        } else {
            window.addEventListener('load', finishLoading, { once: true });
        }

        window.addEventListener('pageshow', event => {
            if (event.persisted) hidePreloader();
        });

        window.setTimeout(hidePreloader, 7000);
    })();
</script>
