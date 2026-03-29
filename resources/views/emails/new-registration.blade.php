<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Нова реєстрація</title>
</head>
<body style="font-family: Arial; background:#f5f5f5; padding:20px;">

<div style="max-width:600px;margin:auto;background:#fff;padding:20px;border-radius:10px;">

    <h2>Нова реєстрація 🎉</h2>

    <p><strong>Захід:</strong> {{ $event->title }}</p>
    <p><strong>Дата:</strong> {{ $event->event_date->format('d.m.Y H:i') }}</p>

    <hr>

    <p><strong>Імʼя:</strong> {{ $registration->name }}</p>
    <p><strong>Email:</strong> {{ $registration->email }}</p>
    <p><strong>Телефон:</strong> {{ $registration->phone }}</p>

    <p><strong>Дата реєстрації:</strong> {{ $registration->created_at->format('d.m.Y H:i') }}</p>

    <hr>

    <p><strong>Кількість учасників:</strong> {{ $count }}</p>

</div>

</body>
</html>