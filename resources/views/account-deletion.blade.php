<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2438da">
    <meta name="robots" content="index,follow">
    <title>Видалення даних ОМЦ CRM</title>
    <meta name="description" content="Як надіслати запит на видалення облікового запису та даних у застосунку ОМЦ CRM.">
    <style>
        :root {
            color-scheme: light;
            --blue: #2438da;
            --ink: #171925;
            --muted: #606982;
            --page: #f4f6ff;
            --surface: rgba(255, 255, 255, .94);
            --line: rgba(46, 57, 139, .14);
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
            width: min(880px, calc(100% - 32px));
            margin: 0 auto;
            padding: 48px 0 64px;
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
            margin-top: 28px;
            padding: clamp(24px, 5vw, 56px);
            border: 1px solid var(--line);
            border-radius: 32px;
            background: var(--surface);
            box-shadow: 0 24px 70px rgba(31, 43, 126, .11);
        }

        h1 {
            margin: 0 0 14px;
            font-size: clamp(34px, 6vw, 56px);
            line-height: 1.05;
            letter-spacing: -.045em;
        }

        h2 {
            margin: 34px 0 12px;
            font-size: 23px;
            letter-spacing: -.02em;
        }

        p, li {
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        ol, ul { padding-left: 22px; }
        strong { color: var(--ink); }
        a { color: var(--blue); }
        .updated { margin: 0 0 32px; font-size: 14px; }
        .contact {
            padding: 18px 20px;
            border-radius: 18px;
            background: #eef0ff;
        }
    </style>
</head>
<body>
<main>
    <a class="brand" href="{{ url('/') }}">
        <img src="{{ asset('images/omc-logo.png') }}" alt="Логотип Обласного молодіжного центру">
        Обласний молодіжний центр
    </a>

    <article>
        <h1>Видалення даних ОМЦ CRM</h1>
        <p class="updated">Останнє оновлення: 14 вересня 2026 року</p>

        <p><strong>ОМЦ CRM</strong> — службовий застосунок Обласного молодіжного центру. Облікові записи створює організація, але кожен користувач може надіслати запит на видалення свого облікового запису та пов’язаних із ним персональних даних.</p>

        <h2>Як надіслати запит</h2>
        <ol>
            <li>Надішліть лист із адреси, пов’язаної з вашим обліковим записом ОМЦ CRM, на <a href="mailto:post@omc.pl.ua?subject=Запит%20на%20видалення%20даних%20ОМЦ%20CRM">post@omc.pl.ua</a>.</li>
            <li>У темі листа напишіть: <strong>«Запит на видалення даних ОМЦ CRM»</strong>.</li>
            <li>У листі вкажіть ім’я та електронну адресу облікового запису. Пароль надсилати не потрібно.</li>
            <li>Після перевірки особи адміністратор підтвердить отримання запиту та повідомить про завершення видалення.</li>
        </ol>

        <h2>Що буде видалено</h2>
        <ul>
            <li>профіль користувача, ім’я, електронна адреса, роль і активні сеанси;</li>
            <li>технічні токени входу та push-сповіщень, прив’язані до облікового запису;</li>
            <li>персональні налаштування та інші дані, які можна безпечно відокремити від службових записів організації.</li>
        </ul>

        <h2>Що може зберігатися</h2>
        <p>Службові матеріали, журнали безпеки, записи про події, реєстрації, документи або інший робочий контент можуть зберігатися, якщо цього вимагають законні обов’язки Центру, цілісність офіційної документації, аудит чи захист інформаційної системи. У таких записах персональні дані видаляються або знеособлюються, коли це можливо. Строк зберігання визначається законодавством і внутрішніми правилами документообігу.</p>

        <h2>Строк розгляду</h2>
        <p>Запит буде опрацьовано без невиправданої затримки. Якщо для перевірки особи або законності видалення потрібна додаткова інформація, ми зв’яжемося з вами за адресою облікового запису.</p>

        <p class="contact">Контакт із питань даних: <a href="mailto:post@omc.pl.ua">post@omc.pl.ua</a>. Докладніше — у <a href="{{ route('privacy-policy') }}">політиці конфіденційності ОМЦ CRM</a>.</p>
    </article>
</main>
</body>
</html>
