<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $success ? 'Google підключено' : 'Помилка підключення Google' }}</title>
    <style>
        :root { color-scheme: light dark; font-family: Inter, system-ui, sans-serif; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; background: #f3f5ff; color: #101633; }
        .card { width: min(88vw, 430px); padding: 34px; border-radius: 28px; background: white; box-shadow: 0 20px 60px rgba(28, 47, 130, .16); text-align: center; }
        .icon { width: 68px; height: 68px; margin: 0 auto 18px; border-radius: 22px; display: grid; place-items: center; font-size: 34px; background: {{ $success ? '#e8f7ee' : '#fff0f0' }}; }
        h1 { margin: 0 0 12px; font-size: 25px; }
        p { margin: 0; line-height: 1.55; color: #596077; }
        .hint { margin-top: 22px; font-size: 14px; }
        a { display: inline-block; margin-top: 22px; padding: 12px 18px; border-radius: 14px; background: #3458f5; color: white; text-decoration: none; font-weight: 700; }
        @media (prefers-color-scheme: dark) {
            body { background: #0b0f17; color: #f7f8ff; }
            .card { background: #151a25; }
            p { color: #bcc2d2; }
        }
    </style>
</head>
<body>
<main class="card">
    <div class="icon" aria-hidden="true">{{ $success ? '✓' : '!' }}</div>
    <h1>{{ $success ? 'Готово' : 'Не вдалося підключити' }}</h1>
    <p>{{ $message }}</p>
    @if ($mobile)
        <p class="hint">Поверніться до застосунку ОМЦ CRM — статус оновиться автоматично.</p>
        <a href="omccrm://google-connected">Повернутися в ОМЦ CRM</a>
    @else
        <a href="{{ url('/admin') }}">Повернутися в адмінку</a>
    @endif
</main>
</body>
</html>
