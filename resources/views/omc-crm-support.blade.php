<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2438da">
    <meta name="robots" content="index,follow">
    <title>Підтримка ОМЦ CRM</title>
    <meta name="description" content="Офіційна сторінка підтримки службового застосунку ОМЦ CRM.">
    <style>
        :root {
            color-scheme: light;
            --blue: #2438da;
            --blue-dark: #1727b9;
            --ink: #171925;
            --muted: #606982;
            --page: #f4f6ff;
            --surface: rgba(255, 255, 255, .94);
            --line: rgba(46, 57, 139, .14);
            --mint: #e5f8f4;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 8% 8%, rgba(57, 76, 255, .17), transparent 32rem),
                radial-gradient(circle at 92% 88%, rgba(56, 197, 176, .13), transparent 29rem),
                var(--page);
        }

        main {
            width: min(920px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 64px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--blue);
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
        }

        .brand img {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            object-fit: contain;
        }

        article {
            margin-top: 24px;
            padding: clamp(24px, 5vw, 56px);
            border: 1px solid var(--line);
            border-radius: 32px;
            background: var(--surface);
            box-shadow: 0 24px 70px rgba(31, 43, 126, .11);
        }

        .eyebrow {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            color: var(--blue);
            background: #eef0ff;
            font-size: 13px;
            font-weight: 800;
        }

        h1 {
            margin: 18px 0 14px;
            font-size: clamp(36px, 7vw, 62px);
            line-height: 1.02;
            letter-spacing: -.05em;
        }

        h2 {
            margin: 0 0 10px;
            font-size: 21px;
            letter-spacing: -.025em;
        }

        p, li {
            color: var(--muted);
            font-size: 16px;
            line-height: 1.65;
        }

        .lead {
            max-width: 720px;
            margin: 0;
            font-size: 18px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 34px;
        }

        .card {
            padding: 22px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fff;
        }

        .card p { margin: 0; }
        .card--wide { grid-column: 1 / -1; background: var(--mint); }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 16px;
            padding: 13px 18px;
            border-radius: 14px;
            color: #fff;
            background: var(--blue);
            font-weight: 800;
            text-decoration: none;
        }

        .button:hover { background: var(--blue-dark); }
        a { color: var(--blue); }

        .links {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .links a { font-weight: 700; }

        @media (max-width: 680px) {
            main { padding-top: 20px; }
            article { border-radius: 24px; }
            .grid { grid-template-columns: 1fr; }
            .card--wide { grid-column: auto; }
        }
    </style>
</head>
<body>
<main>
    <a class="brand" href="{{ url('/') }}">
        <img src="{{ asset('images/omc-logo.png') }}" alt="Логотип Полтавського обласного молодіжного центру">
        Полтавський обласний молодіжний центр
    </a>

    <article>
        <span class="eyebrow">Офіційна підтримка</span>
        <h1>Підтримка ОМЦ CRM</h1>
        <p class="lead"><strong>ОМЦ CRM</strong> — службовий застосунок для внутрішнього використання працівниками Полтавського обласного молодіжного центру. Доступ надається лише авторизованим працівникам установи.</p>

        <section class="grid" aria-label="Інформація про підтримку">
            <div class="card">
                <h2>Як отримати доступ</h2>
                <p>Самостійної реєстрації немає. Робочий обліковий запис і необхідну роль створює системний адміністратор ОМЦ.</p>
            </div>

            <div class="card">
                <h2>Проблеми зі входом</h2>
                <p>Перевірте підключення до інтернету й правильність робочої електронної адреси. Якщо вхід не відновився, зверніться до адміністратора.</p>
            </div>

            <div class="card">
                <h2>Сповіщення не надходять</h2>
                <p>Дозвольте сповіщення для ОМЦ CRM у налаштуваннях iOS або Android, відкрийте застосунок після оновлення та перевірте активний сеанс.</p>
            </div>

            <div class="card">
                <h2>Конфіденційність</h2>
                <p>Застосунок не показує рекламу та не продає персональні дані. Докладні умови описані в офіційній політиці конфіденційності.</p>
            </div>

            <div class="card card--wide">
                <h2>Зв’язатися з підтримкою</h2>
                <p>Опишіть проблему, укажіть модель пристрою, версію операційної системи та, за можливості, додайте знімок екрана без паролів і кодів підтвердження.</p>
                <a class="button" href="mailto:post@omc.pl.ua?subject=Підтримка%20ОМЦ%20CRM">Написати на post@omc.pl.ua</a>
            </div>
        </section>

        <nav class="links" aria-label="Корисні посилання">
            <a href="{{ route('privacy-policy') }}">Політика конфіденційності</a>
            <a href="{{ route('account-deletion') }}">Видалення облікового запису</a>
            <a href="{{ url('/') }}">Офіційний сайт ОМЦ</a>
        </nav>
    </article>
</main>
</body>
</html>
