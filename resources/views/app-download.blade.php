<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2438da">
    <meta name="robots" content="index,follow">
    <title>Завантажити ОМЦ CRM для Android та iOS</title>
    <meta name="description" content="Офіційна сторінка мобільного застосунку ОМЦ CRM для Android та iOS.">
    <style>
        :root {
            color-scheme: light;
            --blue: #2438da;
            --blue-dark: #1726ad;
            --ios: #171923;
            --ink: #151722;
            --muted: #626982;
            --page: #f4f6ff;
            --surface: #ffffff;
            --line: #dfe3fa;
            --green: #087f70;
            --amber: #b36a00;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 8% 8%, rgba(69, 88, 255, .16), transparent 34rem),
                radial-gradient(circle at 94% 88%, rgba(43, 194, 169, .13), transparent 30rem),
                var(--page);
            color: var(--ink);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 36px 0 30px;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand img {
            width: min(330px, 72vw);
            max-height: 104px;
            object-fit: contain;
            object-position: left center;
        }

        .brand-badge {
            padding: 10px 16px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, .86);
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .hero {
            max-width: 850px;
            margin: 58px auto 42px;
            text-align: center;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 18px;
            color: var(--green);
            font-size: 14px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .hero-label::before {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #1fb49d;
            box-shadow: 0 0 0 6px rgba(31, 180, 157, .13);
            content: "";
        }

        h1 {
            margin: 0;
            font-size: clamp(40px, 7vw, 76px);
            line-height: 1.02;
            letter-spacing: -.05em;
        }

        .lead {
            max-width: 720px;
            margin: 22px auto 0;
            color: var(--muted);
            font-size: clamp(18px, 2.3vw, 22px);
            line-height: 1.55;
        }

        .platforms {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .platform-card {
            display: flex;
            min-width: 0;
            flex-direction: column;
            padding: clamp(24px, 4vw, 38px);
            border: 1px solid var(--line);
            border-radius: 34px;
            background: var(--surface);
            box-shadow: 0 24px 64px rgba(31, 43, 126, .10);
        }

        .platform-card:target {
            outline: 4px solid rgba(36, 56, 218, .14);
            outline-offset: 4px;
        }

        .platform-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .platform-icon {
            display: grid;
            width: 64px;
            height: 64px;
            place-items: center;
            border-radius: 20px;
            background: #e9ecff;
            color: var(--blue);
            font-size: 23px;
            font-weight: 1000;
        }

        .platform-card.ios .platform-icon {
            background: #eceef3;
            color: var(--ios);
        }

        .status {
            padding: 8px 12px;
            border-radius: 999px;
            background: #e5f7f2;
            color: var(--green);
            font-size: 12px;
            font-weight: 900;
        }

        .status.pending {
            background: #fff3dc;
            color: var(--amber);
        }

        .platform-card h2 {
            margin: 24px 0 8px;
            font-size: 34px;
            letter-spacing: -.03em;
        }

        .platform-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .qr-box {
            display: grid;
            width: min(100%, 310px);
            place-items: center;
            align-self: center;
            margin: 26px 0;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 26px;
            background: #fff;
        }

        .qr-box img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 16px;
        }

        .button {
            display: inline-flex;
            min-height: 58px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 20px;
            border-radius: 18px;
            background: var(--blue);
            color: #fff;
            font-size: 17px;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 15px 28px rgba(36, 56, 218, .22);
            transition: transform .18s ease, background .18s ease;
        }

        .button:hover { transform: translateY(-2px); background: var(--blue-dark); }
        .platform-card.ios .button { background: var(--ios); box-shadow: 0 15px 28px rgba(23, 25, 35, .16); }

        .button.secondary {
            min-height: 48px;
            margin-top: 12px;
            background: transparent;
            color: var(--blue-dark);
            border: 1px solid rgba(36, 56, 218, .24);
            box-shadow: none;
            font-size: 14px;
        }

        .button.secondary:hover { background: rgba(36, 56, 218, .07); }

        .direct-link {
            margin-top: 18px !important;
            font-size: 13px;
            text-align: center;
        }

        .direct-link a {
            display: block;
            margin-top: 5px;
            color: var(--blue-dark);
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .security-proof {
            display: grid;
            gap: 8px;
            margin-top: 20px;
            padding: 18px;
            border: 1px solid #bee6dc;
            border-radius: 20px;
            background: #f1fbf8;
        }

        .security-proof strong { color: var(--green); }

        .security-proof span {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .security-proof code {
            color: var(--ink);
            font-size: 11px;
            line-height: 1.55;
            overflow-wrap: anywhere;
            user-select: all;
        }

        .notice {
            margin-top: 28px;
            padding: 22px 24px;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: rgba(255, 255, 255, .78);
            color: var(--muted);
            line-height: 1.55;
            text-align: center;
        }

        .notice strong { color: var(--ink); }

        .notice.important {
            border-color: #f2cf91;
            background: #fff9ed;
            color: #72501a;
        }

        footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 30px 4px 0;
            color: #777e99;
            font-size: 13px;
        }

        @media (max-width: 820px) {
            .page { padding-top: 22px; }
            .brand { align-items: flex-start; flex-direction: column; }
            .hero { margin: 40px auto 30px; text-align: left; }
            .hero-label { margin-left: 8px; }
            .platforms { grid-template-columns: 1fr; }
            footer { align-items: center; flex-direction: column; text-align: center; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition: none !important; }
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
        <span class="brand-badge">Офіційний застосунок</span>
    </header>

    <section class="hero">
        <p class="hero-label">Мобільне адміністрування ОМЦ</p>
        <h1>Оберіть свій пристрій</h1>
        <p class="lead">Події, реєстрації, контент і робота команди ОМЦ — у захищеному застосунку для Android та iOS.</p>
    </section>

    <section class="platforms" aria-label="Завантаження застосунку">
        <article class="platform-card android" id="android">
            <div class="platform-head">
                <span class="platform-icon" aria-hidden="true">A</span>
                <span class="status">Доступно · v{{ $androidVersion }}</span>
            </div>
            <h2>Android</h2>
            <p>Компактна версія для сучасних телефонів завантажується швидше. Універсальна збірка залишається для Nox і старіших пристроїв.</p>

            <a class="qr-box" href="{{ $androidRecommendedUrl }}" aria-label="Завантажити компактну версію ОМЦ CRM для Android">
                <img src="{{ asset('images/qr/omc-android-download-qr-square.png') }}" alt="QR-код завантаження ОМЦ CRM для Android">
            </a>

            <a class="button" href="{{ $androidRecommendedUrl }}" download>
                <span aria-hidden="true">↓</span>
                Android ARM64 · рекомендовано
            </a>
            <a class="button secondary" href="{{ $androidDownloadUrl }}" download>
                Універсальна версія · Nox
            </a>
            <p class="direct-link">Постійне посилання:<a href="{{ $androidDownloadUrl }}">{{ $androidDownloadUrl }}</a></p>

            @if (is_string($androidSigningSha256) && is_string($androidApkSha256))
                <div class="security-proof" aria-label="Перевірка цифрового підпису Android-застосунку">
                    <strong>Перевірений production-підпис ОМЦ</strong>
                    <span>SHA-256 сертифіката підпису</span>
                    <code>{{ $androidSigningSha256 }}</code>
                    <span>SHA-256 APK ARM64</span>
                    <code>{{ $androidApkSha256 }}</code>
                    @if (is_string($androidUniversalSha256))
                        <span>SHA-256 універсального APK</span>
                        <code>{{ $androidUniversalSha256 }}</code>
                    @endif
                </div>
            @endif
        </article>

        <article class="platform-card ios" id="ios">
            <div class="platform-head">
                <span class="platform-icon" aria-hidden="true">iOS</span>
                <span class="status {{ $iosAvailable ? '' : 'pending' }}">
                    {{ $iosAvailable ? 'Доступно · v'.$iosVersion : 'Готується' }}
                </span>
            </div>
            <h2>iPhone та iPad</h2>
            <p>
                {{ $iosAvailable
                    ? 'Відскануйте QR-код, щоб відкрити офіційну сторінку встановлення.'
                    : 'iOS готується до публікації. Постійний QR і URL вже готові та змінюватися не будуть.' }}
            </p>

            <a class="qr-box" href="{{ $iosDownloadUrl }}" aria-label="Відкрити сторінку ОМЦ CRM для iOS">
                <img src="{{ asset('images/qr/omc-ios-download-qr-square.png') }}" alt="Постійний QR-код ОМЦ CRM для iOS">
            </a>

            <a class="button" href="{{ $iosDownloadUrl }}">
                <span aria-hidden="true">{{ $iosAvailable ? '↗' : '◷' }}</span>
                {{ $iosAvailable ? 'Завантажити для iOS' : 'iOS — повідомити пізніше' }}
            </a>
            <p class="direct-link">Постійне посилання:<a href="{{ $iosDownloadUrl }}">{{ $iosDownloadUrl }}</a></p>
        </article>
    </section>

    <aside class="notice important">
        <strong>Важливо для оновлення до v{{ $androidVersion }}:</strong>
        застосунок отримав постійний production-підпис ОМЦ. Якщо у вас встановлена версія 1.0.3 або старіша,
        один раз видаліть її перед установленням нової. Дані CRM зберігаються на сервері, але потрібно буде
        повторно увійти та створити локальний PIN.
    </aside>

    <aside class="notice">
        <strong>Безпека:</strong> не вимикайте Google Play Protect. Якщо Android називає файл шкідливим,
        скасуйте встановлення та повідомте адміністратору.
        <strong>Android:</strong> браузер може попросити дозвіл на встановлення APK.
        <strong>iOS:</strong> після публікації в App Store або TestFlight цей QR автоматично перенаправить на актуальну версію.
    </aside>

    <footer>
        <span>developed by Roman Koshovyi</span>
        <span>© {{ now()->year }} Обласний молодіжний центр</span>
    </footer>
</main>
</body>
</html>
