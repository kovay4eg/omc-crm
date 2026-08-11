<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2438da">
    <meta name="robots" content="index,follow">
    <title>Завантажити ОМЦ CRM</title>
    <meta name="description" content="Офіційні посилання для завантаження ОМЦ CRM на Android та iOS.">
    <style>
        :root {
            color-scheme: light;
            --blue: #2438da;
            --blue-dark: #1726ad;
            --ink: #171925;
            --muted: #687089;
            --page: #f4f6ff;
            --surface: rgba(255, 255, 255, .92);
            --line: rgba(46, 57, 139, .13);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 8% 8%, rgba(57, 76, 255, .19), transparent 33rem),
                radial-gradient(circle at 92% 88%, rgba(56, 197, 176, .15), transparent 30rem),
                var(--page);
            background-size: 115% 115%;
            animation: backgroundMove 12s ease-in-out infinite alternate;
        }

        .page {
            width: min(1040px, calc(100% - 32px));
            margin: 0 auto;
            padding: 30px 0 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand img {
            width: min(310px, 76vw);
            max-height: 96px;
            object-fit: contain;
        }

        .hero {
            max-width: 760px;
            margin: 44px auto 34px;
            text-align: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 15px;
            color: var(--blue);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--blue);
            box-shadow: 0 0 0 6px rgba(36, 56, 218, .11);
            content: "";
            animation: pulse 2.2s ease-in-out infinite;
        }

        h1 {
            margin: 0;
            font-size: clamp(38px, 6.5vw, 68px);
            line-height: 1.02;
            letter-spacing: -.05em;
        }

        .platforms {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .platform-card {
            position: relative;
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            padding: clamp(24px, 4vw, 38px);
            border: 1px solid var(--line);
            border-radius: 34px;
            background: var(--surface);
            box-shadow: 0 26px 70px rgba(31, 43, 126, .11);
            backdrop-filter: blur(16px);
            opacity: 0;
            transform: translateY(28px);
            animation: cardIn .72s cubic-bezier(.2, .8, .2, 1) forwards;
        }

        .platform-card.ios { animation-delay: .16s; }

        .platform-card::before {
            position: absolute;
            width: 220px;
            height: 220px;
            right: -100px;
            top: -120px;
            border-radius: 50%;
            background: rgba(36, 56, 218, .08);
            content: "";
        }

        .platform-card.ios::before { background: rgba(23, 25, 35, .07); }

        .platform-title {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 22px;
        }

        .platform-icon {
            display: grid;
            width: 58px;
            height: 58px;
            place-items: center;
            border-radius: 19px;
            background: #e8ebff;
            color: var(--blue);
        }

        .platform-card.ios .platform-icon {
            background: #eceef3;
            color: #171923;
        }

        .platform-icon svg {
            width: 29px;
            height: 29px;
            fill: currentColor;
        }

        h2 {
            margin: 0;
            font-size: 32px;
            letter-spacing: -.03em;
        }

        .qr-link {
            display: block;
            width: min(100%, 300px);
            padding: 13px;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: #fff;
            box-shadow: 0 18px 38px rgba(24, 34, 105, .10);
            animation: qrFloat 4.2s ease-in-out infinite;
        }

        .platform-card.ios .qr-link { animation-delay: .55s; }

        .qr-link img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 18px;
        }

        .button {
            position: relative;
            display: inline-flex;
            width: 100%;
            min-height: 58px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            overflow: hidden;
            margin-top: 25px;
            padding: 14px 20px;
            border-radius: 18px;
            background: var(--blue);
            color: #fff;
            font-size: 17px;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 15px 30px rgba(36, 56, 218, .23);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .platform-card.ios .button {
            background: #171923;
            box-shadow: 0 15px 30px rgba(23, 25, 35, .18);
        }

        .button::after {
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255,255,255,.26), transparent 70%);
            transform: translateX(-120%);
            content: "";
            animation: shimmer 3.8s ease-in-out infinite;
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 19px 36px rgba(36, 56, 218, .28);
        }

        .direct-link {
            width: 100%;
            margin: 17px 0 0;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        .direct-link a {
            display: block;
            margin-top: 5px;
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        footer {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            padding: 28px 4px 0;
            color: #777e99;
            font-size: 12px;
        }

        @keyframes cardIn {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes qrFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }

        @keyframes shimmer {
            0%, 55% { transform: translateX(-120%); }
            85%, 100% { transform: translateX(120%); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 6px rgba(36, 56, 218, .11); }
            50% { transform: scale(1.12); box-shadow: 0 0 0 10px rgba(36, 56, 218, .05); }
        }

        @keyframes backgroundMove {
            to { background-position: 5% 3%, 95% 92%, center; }
        }

        @media (max-width: 760px) {
            .page { padding-top: 20px; }
            .hero { margin: 34px auto 28px; }
            .platforms { grid-template-columns: 1fr; }
            footer { align-items: center; flex-direction: column; text-align: center; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>
<x-preloader />

<main class="page">
    <header class="brand">
        <a href="{{ url('/') }}" aria-label="Перейти на головну сторінку ОМЦ">
            <img src="{{ asset('storage/homepage/Лого ПОМЦ.png') }}" alt="Обласний молодіжний центр">
        </a>
    </header>

    <section class="hero">
        <p class="eyebrow">Офіційний застосунок ОМЦ</p>
        <h1>Завантажити ОМЦ CRM</h1>
    </section>

    <section class="platforms" aria-label="Завантаження застосунку">
        <article class="platform-card android" id="android">
            <div class="platform-title">
                <span class="platform-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M17.6 9.48 19.44 6.3a.63.63 0 0 0-.23-.86.62.62 0 0 0-.85.23l-1.88 3.25A11.4 11.4 0 0 0 12 8c-1.58 0-3.1.32-4.48.92L5.64 5.67a.62.62 0 1 0-1.08.63L6.4 9.48A8.92 8.92 0 0 0 3 16h18a8.92 8.92 0 0 0-3.4-6.52ZM8 13.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm8 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/></svg>
                </span>
                <h2>Android</h2>
            </div>

            <a class="qr-link" href="{{ $androidRecommendedUrl }}" aria-label="Завантажити ОМЦ CRM для Android">
                <img src="{{ asset('images/qr/omc-android-download-qr-square.png') }}" alt="QR-код завантаження ОМЦ CRM для Android">
            </a>

            <a class="button" href="{{ $androidRecommendedUrl }}" download>
                <span aria-hidden="true">↓</span>
                Завантажити для Android
            </a>
            <p class="direct-link">
                Посилання:
                <a href="{{ $androidRecommendedUrl }}">{{ $androidRecommendedUrl }}</a>
            </p>
        </article>

        <article class="platform-card ios" id="ios">
            <div class="platform-title">
                <span class="platform-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M16.7 12.9c0-2.4 2-3.6 2.1-3.7a4.6 4.6 0 0 0-3.6-1.9c-1.5-.2-3 .9-3.8.9-.8 0-2-.9-3.3-.9a4.9 4.9 0 0 0-4.2 2.5c-1.8 3.1-.5 7.7 1.3 10.2.9 1.2 1.9 2.6 3.2 2.5 1.3-.1 1.8-.8 3.4-.8 1.6 0 2 .8 3.4.8 1.4 0 2.3-1.2 3.1-2.5 1-1.4 1.4-2.8 1.4-2.9-.1 0-3-.9-3-4.2ZM14.2 5.7c.7-.9 1.2-2.1 1.1-3.2-1.1 0-2.4.7-3.2 1.6-.7.8-1.3 2-1.1 3.1 1.2.1 2.4-.6 3.2-1.5Z"/></svg>
                </span>
                <h2>iPhone та iPad</h2>
            </div>

            <a class="qr-link" href="{{ $iosDownloadUrl }}" aria-label="Відкрити ОМЦ CRM для iOS">
                <img src="{{ asset('images/qr/omc-ios-download-qr-square.png') }}" alt="QR-код ОМЦ CRM для iOS">
            </a>

            <a class="button" href="{{ $iosDownloadUrl }}">
                <span aria-hidden="true">↗</span>
                Завантажити для iOS
            </a>
            <p class="direct-link">
                Посилання:
                <a href="{{ $iosDownloadUrl }}">{{ $iosDownloadUrl }}</a>
            </p>
        </article>
    </section>

    <footer>
        <span>developed by Roman Koshovyi</span>
        <span>© {{ now()->year }} Обласний молодіжний центр</span>
    </footer>
</main>
</body>
</html>
