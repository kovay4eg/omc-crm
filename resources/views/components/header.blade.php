@php
    $settings = \App\Models\HomepageSetting::first();
    $isStandalonePublicPage = request()->routeIs(
        'event-summaries.*',
        'events.show',
        'reporting',
        'calendar-plan',
    );
@endphp

<x-accessibility-controls />

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    .header {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        width: 100%;
        transition: .3s ease;
    }

    .header,
    .header * {
        box-sizing: border-box;
    }

    .header.scrolled {
        background: rgba(255, 255, 255, .8);
        box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        backdrop-filter: blur(12px);
    }

    .container-1200 {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .header-inner {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 0;
    }

    .logo {
        position: relative;
        z-index: 1200;
        flex-shrink: 0;
    }

    .logo img {
        display: block;
        width: auto !important;
        height: auto !important;
        max-width: 120px;
        image-rendering: crisp-edges;
        image-rendering: -webkit-optimize-contrast;
        transform: none !important;
        filter: none !important;
        backface-visibility: hidden;
    }

    .nav {
        display: flex;
        align-items: center;
        gap: 32px;
    }

    .nav-item {
        position: relative;
        display: flex;
        align-items: center;
    }

    .nav a,
    .nav-btn {
        position: relative;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 0;
        border: none;
        background: none;
        color: #111;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        transition: .3s;
    }

    .nav a::after,
    .nav-btn::after {
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 0;
        height: 2px;
        background: #2b3cff;
        content: '';
        transition: .3s;
    }

    .nav a:hover,
    .nav-btn:hover,
    .nav-item:hover .nav-btn {
        color: #2b3cff;
    }

    .nav a:hover::after,
    .nav-btn:hover::after,
    .nav-item:hover .nav-btn::after,
    .nav > a.active::after,
    .nav-btn.active::after {
        width: 100%;
    }

    .nav > a.active,
    .nav-btn.active {
        color: #2b3cff !important;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 50%;
        z-index: 1100;
        display: flex;
        flex-direction: column;
        min-width: 220px;
        padding: 12px 0;
        border: 1px solid rgba(255, 255, 255, .4);
        border-radius: 12px;
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
        opacity: 0;
        visibility: hidden;
        transform: translateX(-50%) translateY(15px);
        transition: .3s ease;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .nav-item:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(10px);
    }

    .dropdown-menu a {
        padding: 10px 20px;
        color: #333;
        font-size: 15px;
        font-weight: 500;
        transition: .2s;
    }

    .dropdown-menu a::after {
        display: none;
    }

    .dropdown-menu a:hover {
        padding-left: 25px;
        background: rgba(43, 60, 255, .05);
        color: #2b3cff;
    }

    .socials {
        display: flex;
        gap: 14px;
    }

    .socials img {
        width: 20px;
        transition: .25s;
    }

    .socials img:hover {
        transform: translateY(-4px) scale(1.15);
    }

    .mobile-socials {
        display: none;
    }

    .burger {
        position: relative;
        z-index: 1300;
        display: none;
        width: 28px;
        height: 20px;
        cursor: pointer;
    }

    .burger span {
        position: absolute;
        left: 0;
        width: 100%;
        height: 3px;
        background: #000;
        transition: .3s;
    }

    .burger span:nth-child(1) {
        top: 0;
    }

    .burger span:nth-child(2) {
        top: 8px;
    }

    .burger span:nth-child(3) {
        top: 16px;
    }

    .burger.active span:nth-child(1) {
        top: 8px;
        transform: rotate(45deg);
    }

    .burger.active span:nth-child(2) {
        opacity: 0;
    }

    .burger.active span:nth-child(3) {
        top: 8px;
        transform: rotate(-45deg);
    }

    /* Перемикаємося на бургер до того, як навігація може перейти у другий рядок. */
    @media (max-width: 1100px) {
        .header-inner {
            padding-right: 20px;
            padding-left: 20px;
        }

        .burger {
            position: absolute;
            top: 50%;
            right: 25px;
            display: block;
            transform: translateY(-50%);
        }

        .logo img {
            width: 85px !important;
            max-width: 85px !important;
            height: auto !important;
            object-fit: contain;
        }

        .nav {
            position: absolute;
            top: 100%;
            left: 50%;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 100vw;
            max-width: 100vw;
            max-height: calc(100vh - 96px);
            gap: 20px;
            padding: 35px 0 30px;
            overflow-x: hidden;
            overflow-y: auto;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
            pointer-events: none;
            transition: .35s ease;
        }

        .nav.active {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        .nav-item {
            display: block;
            min-width: 0;
            max-width: 100%;
            width: 100%;
            text-align: center;
        }

        .nav > a,
        .nav-btn {
            justify-content: center;
            width: 100%;
        }

        .dropdown-menu {
            position: static;
            display: none;
            min-width: 0 !important;
            max-width: 100% !important;
            width: 100%;
            margin-top: 10px;
            padding: 10px 0;
            border: none;
            border-radius: 0;
            background: #f9f9f9;
            box-shadow: none;
            opacity: 1;
            visibility: visible;
            transform: none;
            text-align: center;
            backdrop-filter: none;
        }

        .nav-item.open .dropdown-menu {
            display: flex !important;
            gap: 2px;
        }

        .nav-item.open .nav-btn svg {
            transform: rotate(180deg);
        }

        .dropdown-menu a,
        .dropdown-menu a:hover {
            display: flex;
            width: 100%;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            color: #222;
            font-size: 16px;
            text-align: center;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .socials {
            display: none;
        }

        .mobile-socials {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }

        .mobile-socials img {
            width: 24px;
            transition: .25s;
        }

        .mobile-socials img:hover {
            transform: scale(1.2);
        }
    }

    html {
        scroll-behavior: smooth;
    }
</style>

<header class="header" id="header">
    <div class="container-1200 header-inner">
        <div class="logo">
            <a href="/">
                @if (!empty($settings?->logo))
                    <img
                        src="{{ asset('storage/' . $settings->logo) }}"
                        alt="Логотип"
                        draggable="false"
                    >
                @else
                    <img
                        src="/images/logo.png"
                        alt="Логотип"
                        draggable="false"
                    >
                @endif
            </a>
        </div>

        <div class="burger" id="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="nav" id="navMenu">
            <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
                Головна
            </a>

            <div class="nav-item" id="aboutDropdown">
                <button class="nav-btn" id="aboutMenuButton" type="button" aria-expanded="false" aria-controls="aboutSubmenu">
                    Про нас

                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div class="dropdown-menu" id="aboutSubmenu">
                    <a href="#about-section">Про нас</a>

                    <a href="#team-section" class="about-hidden-link">
                        Команда
                    </a>

                    <a href="#statut-section" class="about-hidden-link">
                        Статут
                    </a>

                    <a href="#reports-section" class="about-hidden-link">
                        Звітність
                    </a>

                    <a href="#calendar-plan-section" class="about-hidden-link">
                        Календарний план
                    </a>
                </div>
            </div>

            <a href="{{ $isStandalonePublicPage ? url('/#structure-section') : '#structure-section' }}">
                Де ми можемо зустрітися?
            </a>

            <a
                href="{{ $isStandalonePublicPage ? url('/#events-section') : '#events-section' }}"
                id="eventsNavLink"
                class="{{ request()->routeIs('events.show') ? 'active' : '' }}"
            >
                Анонси заходів
            </a>

            <a
                href="{{ $isStandalonePublicPage ? url('/#event-summaries-section') : '#event-summaries-section' }}"
                id="eventSummariesNavLink"
                class="{{ request()->routeIs('event-summaries.*') ? 'active' : '' }}"
            >
                Підсумки заходів
            </a>

            <a
                href="{{ $isStandalonePublicPage ? url('/#contacts-section') : '#contacts-section' }}"
                id="contactsNavLink"
            >
                Контакти
            </a>

            <div class="mobile-socials">
                @if ($settings?->facebook_enabled && $settings?->facebook_url)
                    <a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('icons/facebook.svg') }}" alt="Facebook">
                    </a>
                @endif

                @if ($settings?->instagram_enabled && $settings?->instagram_url)
                    <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('icons/instagram.svg') }}" alt="Instagram">
                    </a>
                @endif

                @if ($settings?->telegram_enabled && $settings?->telegram_url)
                    <a href="{{ $settings->telegram_url }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('icons/telegram.svg') }}" alt="Telegram">
                    </a>
                @endif

                @if ($settings?->youtube_enabled && $settings?->youtube_url)
                    <a href="{{ $settings->youtube_url }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('icons/youtube.svg') }}" alt="YouTube">
                    </a>
                @endif

                @if ($settings?->tiktok_enabled && $settings?->tiktok_url)
                    <a href="{{ $settings->tiktok_url }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('icons/tiktok.svg') }}" alt="TikTok">
                    </a>
                @endif
            </div>
        </nav>

        <div class="socials">
            @if ($settings?->facebook_enabled && $settings?->facebook_url)
                <a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('icons/facebook.svg') }}" alt="Facebook">
                </a>
            @endif

            @if ($settings?->instagram_enabled && $settings?->instagram_url)
                <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('icons/instagram.svg') }}" alt="Instagram">
                </a>
            @endif

            @if ($settings?->telegram_enabled && $settings?->telegram_url)
                <a href="{{ $settings->telegram_url }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('icons/telegram.svg') }}" alt="Telegram">
                </a>
            @endif

            @if ($settings?->youtube_enabled && $settings?->youtube_url)
                <a href="{{ $settings->youtube_url }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('icons/youtube.svg') }}" alt="YouTube">
                </a>
            @endif

            @if ($settings?->tiktok_enabled && $settings?->tiktok_url)
                <a href="{{ $settings->tiktok_url }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('icons/tiktok.svg') }}" alt="TikTok">
                </a>
            @endif
        </div>
    </div>
</header>

<script>
    const mobileNavigationBreakpoint = 1100;
    const burger = document.getElementById('burger');
    const nav = document.getElementById('navMenu');
    const header = document.getElementById('header');
    const aboutDropdown = document.getElementById('aboutDropdown');
    const aboutMenuButton = document.getElementById('aboutMenuButton');
    const eventsNavLink = document.getElementById('eventsNavLink');
    const eventSummariesNavLink = document.getElementById('eventSummariesNavLink');
    const contactsNavLink = document.getElementById('contactsNavLink');

    const sectionNavigation = [
        {
            link: eventsNavLink,
            sectionId: 'events-section',
        },
        {
            link: eventSummariesNavLink,
            sectionId: 'event-summaries-section',
        },
        {
            link: contactsNavLink,
            sectionId: 'contacts-section',
        },
    ];

    function activateSectionMenu(activeLink) {
        if (!nav || !activeLink) {
            return;
        }

        nav.querySelectorAll(':scope > a').forEach(link => {
            link.classList.remove('active');
        });

        const aboutButton = document.querySelector('#aboutDropdown .nav-btn');

        if (aboutButton) {
            aboutButton.classList.remove('active');
        }

        activeLink.classList.add('active');
    }

    function getActiveSectionLink() {
        if (!header || window.location.pathname !== '/') {
            return null;
        }

        const activationPoint = header.offsetHeight + 30;

        return sectionNavigation.find(({ sectionId }) => {
            const section = document.getElementById(sectionId);

            if (!section) {
                return false;
            }

            const position = section.getBoundingClientRect();

            return position.top <= activationPoint && position.bottom > activationPoint;
        })?.link ?? null;
    }

    function updateHeaderState() {
        if (header) {
            if (window.scrollY > 20) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }

        const activeSectionLink = getActiveSectionLink();

        if (activeSectionLink) {
            window.requestAnimationFrame(() => activateSectionMenu(activeSectionLink));

            return;
        }

        if (window.location.pathname === '/') {
            sectionNavigation.forEach(({ link }) => {
                link?.classList.remove('active');
            });
        }
    }

    if (burger && nav) {
        burger.addEventListener('click', () => {
            burger.classList.toggle('active');
            nav.classList.toggle('active');

            if (!nav.classList.contains('active')) {
                aboutDropdown?.classList.remove('open');
                aboutMenuButton?.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (aboutDropdown && aboutMenuButton) {
        aboutMenuButton.addEventListener('click', event => {
            if (window.innerWidth <= mobileNavigationBreakpoint) {
                event.preventDefault();

                const isOpen = aboutDropdown.classList.toggle('open');

                aboutMenuButton.setAttribute('aria-expanded', String(isOpen));
            }
        });
    }

    nav?.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= mobileNavigationBreakpoint && burger) {
                burger.classList.remove('active');
                nav.classList.remove('active');
                aboutDropdown?.classList.remove('open');
                aboutMenuButton?.setAttribute('aria-expanded', 'false');
            }
        });
    });

    sectionNavigation.forEach(({ link, sectionId }) => {
        if (!link) {
            return;
        }

        link.addEventListener('click', event => {
            const section = document.getElementById(sectionId);

            if (!section) {
                return;
            }

            event.preventDefault();

            const headerOffset = header ? header.offsetHeight + 16 : 16;

            const targetPosition =
                window.scrollY
                + section.getBoundingClientRect().top
                - headerOffset;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth',
            });

            history.replaceState(null, '', `#${sectionId}`);

            activateSectionMenu(link);

            if (window.innerWidth <= mobileNavigationBreakpoint && burger && nav) {
                burger.classList.remove('active');
                nav.classList.remove('active');
            }
        });
    });

    window.addEventListener('scroll', updateHeaderState, {
        passive: true,
    });

    updateHeaderState();
</script>
