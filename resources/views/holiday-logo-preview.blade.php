@php
    $themes = [
        ['key' => 'new-year', 'name' => 'Новий рік'],
        ['key' => 'christmas', 'name' => 'Різдво'],
        ['key' => 'easter', 'name' => 'Великдень'],
        ['key' => 'vyshyvanka', 'name' => 'День вишиванки'],
        ['key' => 'independence', 'name' => 'День Незалежності'],
        ['key' => 'youth', 'name' => 'День молоді'],
    ];
@endphp
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Святкові логотипи ОМЦ — локальний перегляд</title>
    <style>
        :root { color-scheme: light; font-family: Inter, Arial, sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f7ff; color: #111827; }
        main { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 40px 0 64px; }
        .topbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; margin-bottom: 30px; }
        h1 { margin: 0 0 10px; color: #1727b7; font-size: clamp(30px, 5vw, 52px); line-height: 1; }
        .lead { max-width: 760px; margin: 0; color: #586174; font-size: 16px; line-height: 1.55; }
        .back { flex: 0 0 auto; padding: 12px 18px; border: 2px solid #2938ef; border-radius: 999px; color: #1727b7; font-weight: 800; text-decoration: none; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
        .card { overflow: hidden; border: 1px solid #dfe3f5; border-radius: 24px; background: #fff; box-shadow: 0 16px 40px rgba(38, 54, 130, .09); }
        .preview { display: grid; min-height: 280px; place-items: center; padding: 24px; background-color: #fff; background-image: linear-gradient(45deg, #f2f4fa 25%, transparent 25%), linear-gradient(-45deg, #f2f4fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f2f4fa 75%), linear-gradient(-45deg, transparent 75%, #f2f4fa 75%); background-position: 0 0, 0 10px, 10px -10px, -10px 0; background-size: 20px 20px; }
        .preview img { display: block; width: min(100%, 360px); height: auto; filter: drop-shadow(0 10px 14px rgba(25, 35, 95, .08)); }
        .meta { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px 20px; }
        h2 { margin: 0; font-size: 19px; }
        .open { flex: 0 0 auto; padding: 10px 13px; border-radius: 12px; background: #2938ef; color: #fff; font-size: 13px; font-weight: 800; text-decoration: none; }
        @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 620px) { main { width: min(100% - 22px, 1180px); padding-top: 24px; } .topbar { display: block; } .back { display: inline-block; margin-top: 18px; } .grid { grid-template-columns: 1fr; } .preview { min-height: 250px; } }
    </style>
</head>
<body>
<main>
    <div class="topbar">
        <div>
            <h1>Святкові логотипи ОМЦ</h1>
            <p class="lead">Локальна галерея всіх векторних варіантів. Шаховий фон показує прозорі ділянки. Кнопка відкриває відповідний логотип у реальному хедері сайту.</p>
        </div>
        <a class="back" href="/">На головну</a>
    </div>

    <section class="grid" aria-label="Святкові логотипи">
        @foreach ($themes as $theme)
            @php
                $logoPath = 'images/holiday/logos/'.$theme['key'].'.svg';
                $logoVersion = substr(hash_file('sha256', public_path($logoPath)), 0, 12);
                $logoUrl = asset($logoPath).'?v='.$logoVersion;
                $themePreviewUrl = url('/').'?'.http_build_query([
                    'holiday-preview' => $theme['key'],
                    'logo-version' => $logoVersion,
                ]);
            @endphp
            <article class="card">
                <div class="preview">
                    <img src="{{ $logoUrl }}" alt="{{ $theme['name'] }} — святковий логотип ОМЦ">
                </div>
                <div class="meta">
                    <h2>{{ $theme['name'] }}</h2>
                    <a class="open" href="{{ $themePreviewUrl }}">У хедері</a>
                </div>
            </article>
        @endforeach
    </section>
</main>
</body>
</html>
