<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2538e5">
    <meta name="robots" content="index,follow">
    <title>Завантажити ОМЦ CRM для Android</title>
    <meta name="description" content="Офіційна сторінка завантаження мобільного застосунку ОМЦ CRM для Android.">
    <style>
        :root {
            color-scheme: light;
            --primary: #2538e5;
            --primary-dark: #1826b8;
            --ink: #141624;
            --muted: #626a84;
            --surface: #ffffff;
            --page: #f4f6ff;
            --line: #dfe3ff;
            --success: #087c6d;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 12%, rgba(86, 104, 255, .18), transparent 32rem),
                radial-gradient(circle at 90% 88%, rgba(90, 212, 194, .16), transparent 30rem),
                var(--page);
            color: var(--ink);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; }

        .download-page {
            width: min(1160px, calc(100% - 32px));
            margin: 0 auto;
            padding: 48px 0 32px;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 34px;
        }

        .brand img {
            width: min(330px, 72vw);
            max-height: 108px;
            object-fit: contain;
            object-position: left center;
        }

        .brand-badge {
            flex: 0 0 auto;
            padding: 10px 16px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, .76);
            color: var(--primary-dark);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .download-card {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(300px, .85fr);
            gap: 48px;
            align-items: center;
            padding: clamp(28px, 5vw, 64px);
            border: 1px solid rgba(212, 218, 255, .9);
            border-radius: 40px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 30px 80px rgba(34, 47, 145, .13);
            backdrop-filter: blur(18px);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 18px;
            color: var(--success);
            font-size: 15px;
            font-weight: 800;
        }

        .eyebrow::before {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #20b69f;
            box-shadow: 0 0 0 6px rgba(32, 182, 159, .13);
            content: "";
        }

        h1 {
            max-width: 720px;
            margin: 0;
            font-size: clamp(38px, 6vw, 70px);
            line-height: 1.02;
            letter-spacing: -.045em;
        }

        .lead {
            max-width: 640px;
            margin: 24px 0 0;
            color: var(--muted);
            font-size: clamp(18px, 2.2vw, 22px);
            line-height: 1.55;
        }

        .download-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 34px;
        }

        .download-button,
        .site-button {
            display: inline-flex;
            min-height: 58px;
            align-items: center;
            justify-content: center;
            gap: 11px;
            padding: 15px 24px;
            border-radius: 18px;
            font-size: 17px;
            font-weight: 800;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .download-button {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 16px 30px rgba(37, 56, 229, .24);
        }

        .site-button {
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
        }

        .download-button:hover,
        .site-button:hover {
            transform: translateY(-2px);
        }

        .download-button:hover { background: var(--primary-dark); }

        .direct-link {
            margin: 26px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .direct-link a {
            display: inline-block;
            margin-top: 5px;
            color: var(--primary-dark);
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .qr-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px;
            border: 1px solid var(--line);
            border-radius: 30px;
            background: linear-gradient(155deg, #fff, #f3f5ff);
            text-align: center;
        }

        .qr-panel img {
            display: block;
            width: min(100%, 360px);
            height: auto;
            border-radius: 22px;
        }

        .qr-panel strong {
            margin-top: 18px;
            font-size: 19px;
        }

        .qr-panel span {
            margin-top: 7px;
            color: var(--muted);
            line-height: 1.45;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .step {
            padding: 22px;
            border: 1px solid rgba(212, 218, 255, .85);
            border-radius: 24px;
            background: rgba(255, 255, 255, .76);
        }

        .step-number {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            margin-bottom: 14px;
            border-radius: 12px;
            background: #e8ebff;
            color: var(--primary-dark);
            font-weight: 900;
        }

        .step strong { display: block; margin-bottom: 6px; }
        .step p { margin: 0; color: var(--muted); line-height: 1.5; }

        footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 28px 4px 0;
            color: #777e99;
            font-size: 13px;
        }

        @media (max-width: 820px) {
            .download-page { padding-top: 24px; }
            .brand { align-items: flex-start; flex-direction: column; margin-bottom: 24px; }
            .download-card { grid-template-columns: 1fr; gap: 34px; border-radius: 30px; }
            .steps { grid-template-columns: 1fr; }
            .download-actions { flex-direction: column; }
            .download-button, .site-button { width: 100%; }
            footer { align-items: center; flex-direction: column; text-align: center; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition: none !important; }
        }
    </style>
</head>
<body>
<x-preloader />

<main class="download-page">
    <header class="brand">
        <a href="{{ url('/') }}" aria-label="Перейти на головну сторінку ОМЦ">
            <img src="{{ asset('storage/homepage/Лого ПОМЦ.png') }}" alt="Обласний молодіжний центр">
        </a>
        <span class="brand-badge">Офіційний застосунок</span>
    </header>

    <section class="download-card" aria-labelledby="download-title">
        <div>
            <p class="eyebrow">Android · версія {{ $androidVersion }}</p>
            <h1 id="download-title">ОМЦ CRM завжди поруч</h1>
            <p class="lead">
                Мобільне адміністрування подій, реєстрацій, контенту та команди Обласного молодіжного центру.
            </p>

            <div class="download-actions">
                <a class="download-button" href="{{ $androidDownloadUrl }}" download>
                    <span aria-hidden="true">↓</span>
                    Завантажити для Android
                </a>
                <a class="site-button" href="{{ url('/') }}">Перейти на сайт</a>
            </div>

            <p class="direct-link">
                Пряме посилання на APK:<br>
                <a href="{{ $androidDownloadUrl }}">{{ $androidDownloadUrl }}</a>
            </p>
        </div>

        <div class="qr-panel">
            <a href="{{ $androidDownloadUrl }}" aria-label="Завантажити застосунок для Android">
                <img src="{{ asset('images/qr/omc-android-download-qr-square.png') }}" alt="QR-код для завантаження ОМЦ CRM">
            </a>
            <strong>Відскануйте QR-код</strong>
            <span>Наведіть камеру телефона або натисніть на код.</span>
        </div>
    </section>

    <section class="steps" aria-label="Інструкція зі встановлення">
        <article class="step">
            <span class="step-number">1</span>
            <strong>Завантажте APK</strong>
            <p>Натисніть кнопку або відскануйте QR-код.</p>
        </article>
        <article class="step">
            <span class="step-number">2</span>
            <strong>Підтвердьте встановлення</strong>
            <p>Android може попросити дозвіл для встановлення з браузера.</p>
        </article>
        <article class="step">
            <span class="step-number">3</span>
            <strong>Увійдіть у CRM</strong>
            <p>Використайте свій чинний логін і пароль адміністратора.</p>
        </article>
    </section>

    <footer>
        <span>developed by Roman Koshovyi</span>
        <span>© {{ now()->year }} Обласний молодіжний центр</span>
    </footer>
</main>
</body>
</html>
