@php
    $maintenanceLogo = $settings?->logo
        ? asset('storage/' . $settings->logo)
        : asset('storage/homepage/Лого ПОМЦ.png');

    $maintenanceSocialNetworks = [
        ['enabled' => $settings?->facebook_enabled, 'url' => $settings?->facebook_url, 'name' => 'Facebook', 'icon' => 'facebook.svg'],
        ['enabled' => $settings?->instagram_enabled, 'url' => $settings?->instagram_url, 'name' => 'Instagram', 'icon' => 'instagram.svg'],
        ['enabled' => $settings?->telegram_enabled, 'url' => $settings?->telegram_url, 'name' => 'Telegram', 'icon' => 'telegram.svg'],
        ['enabled' => $settings?->youtube_enabled, 'url' => $settings?->youtube_url, 'name' => 'YouTube', 'icon' => 'youtube.svg'],
        ['enabled' => $settings?->tiktok_enabled, 'url' => $settings?->tiktok_url, 'name' => 'TikTok', 'icon' => 'tiktok.svg'],
    ];

    $maintenanceShareLinks = [
        'site' => url('/'),
        'instagram' => $settings?->instagram_enabled ? $settings?->instagram_url : null,
        'tiktok' => $settings?->tiktok_enabled ? $settings?->tiktok_url : null,
    ];
@endphp

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Технічні роботи — ОМЦ</title>
    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body { overflow-x: hidden; background: #fbfbff; color: #171717; font-family: 'Commissioner', sans-serif; }
        .maintenance-page { position: relative; display: grid; min-height: 100vh; place-items: center; overflow: hidden; padding: 28px; }
        .maintenance-page::before, .maintenance-page::after { position: absolute; width: min(62vw, 800px); aspect-ratio: 1; border-radius: 50%; content: ''; filter: blur(1px); }
        .maintenance-page::before { top: -35%; left: -20%; background: rgba(52, 48, 210, .13); }
        .maintenance-page::after { right: -22%; bottom: -42%; background: rgba(111, 116, 255, .15); }
        .maintenance-card { position: relative; z-index: 1; width: min(650px, 100%); padding: clamp(34px, 7vw, 70px); border: 1px solid rgba(52, 48, 210, .12); border-radius: 30px; background: rgba(255, 255, 255, .9); box-shadow: 0 26px 80px rgba(39, 43, 158, .15); text-align: center; }
        .maintenance-logo { display: block; width: min(310px, 72vw); max-height: 88px; margin: 0 auto 38px; object-fit: contain; }
        .maintenance-badge { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 18px; padding: 9px 14px; border-radius: 999px; background: #eeecff; color: #3430d2; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .maintenance-badge::before { width: 8px; height: 8px; border-radius: 50%; background: #3430d2; content: ''; animation: maintenancePulse 1.5s ease-in-out infinite; }
        .maintenance-card h1 { margin: 0; color: #2d35c8; font-size: clamp(30px, 5vw, 48px); font-weight: 800; line-height: 1.08; text-transform: uppercase; }
        .maintenance-card p { max-width: 500px; margin: 22px auto 0; color: #484854; font-size: clamp(16px, 2vw, 19px); line-height: 1.6; }
        .maintenance-socials { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 34px; }
        .maintenance-social-link { display: grid; width: 46px; height: 46px; place-items: center; border: 1px solid #cfd2ff; border-radius: 50%; background: #fff; transition: background .2s ease, transform .2s ease; }
        .maintenance-social-link:hover { background: #e5e6ff; transform: translateY(-4px); }
        .maintenance-social-link img { width: 19px; height: 19px; object-fit: contain; }
        .maintenance-social-caption { margin-top: 28px !important; color: #696a82 !important; font-size: 14px !important; font-weight: 600; }
        .maintenance-game-invite { margin-top: 34px !important; color: #696a82 !important; font-size: 14px !important; font-weight: 600; }
        .maintenance-game-toggle { margin-left: 7px; padding: 0; border: 0; background: transparent; color: #3430d2; cursor: pointer; font: inherit; font-weight: 800; text-decoration: underline; text-underline-offset: 3px; }
        .maintenance-game-toggle:hover { color: #201fb1; }
        .maintenance-game { display: none; max-width: 420px; margin: 22px auto 0; padding: 16px; border: 1px solid #d9d7ff; border-radius: 18px; background: #f4f4ff; text-align: left; }
        .maintenance-game.is-open { display: block; animation: maintenanceGameOpen .3s ease both; }
        .maintenance-game-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; color: #2d35c8; font-size: 13px; font-weight: 800; text-transform: uppercase; }
        .maintenance-game-score, .maintenance-game-timer { padding: 6px 9px; border-radius: 999px; background: #3430d2; color: #fff; font-size: 12px; white-space: nowrap; }
        .maintenance-game-timer { background: #696a82; }
        .maintenance-game-field { position: relative; height: 180px; overflow: hidden; border-radius: 12px; background: linear-gradient(135deg, #e8e8ff, #fff); }
        .maintenance-game-star { position: absolute; top: 42%; left: 45%; width: 48px; height: 48px; border: 0; border-radius: 50%; background: #3430d2; color: #fff; cursor: pointer; font-size: 29px; line-height: 1; box-shadow: 0 8px 18px rgba(52, 48, 210, .28); transition: left .22s ease, top .22s ease, transform .15s ease; }
        .maintenance-game-star:hover { transform: scale(1.12) rotate(16deg); }
        .maintenance-game-star:focus-visible { outline: 3px solid #fbbf24; outline-offset: 3px; }
        .maintenance-game-help { margin: 12px 0 0 !important; color: #686a87 !important; font-size: 12px !important; line-height: 1.4 !important; text-align: center; }
        .maintenance-game-result { display: none; margin-top: 16px; padding-top: 16px; border-top: 1px solid #d9d7ff; }
        .maintenance-game-result.is-visible { display: block; }
        .maintenance-game-result-title { margin: 0 !important; color: #2d35c8 !important; font-size: 15px !important; font-weight: 800; text-align: center; }
        .maintenance-game-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; margin-top: 12px; }
        .maintenance-game-input { width: 100%; min-width: 0; padding: 11px 12px; border: 1px solid #cfd0ff; border-radius: 9px; background: #fff; color: #171717; font: inherit; font-size: 13px; }
        .maintenance-game-input:focus { border-color: #3430d2; outline: 2px solid rgba(52, 48, 210, .14); }
        .maintenance-game-button { min-height: 40px; padding: 10px 13px; border: 0; border-radius: 9px; background: #3430d2; color: #fff; cursor: pointer; font: inherit; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .maintenance-game-button:hover { background: #201fb1; }
        .maintenance-game-button:disabled { cursor: wait; opacity: .65; }
        .maintenance-game-share { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 12px; }
        .maintenance-game-share button { min-height: 34px; padding: 7px 10px; border: 1px solid #cfd0ff; border-radius: 999px; background: #fff; color: #3430d2; cursor: pointer; font: inherit; font-size: 11px; font-weight: 800; }
        .maintenance-game-share button:hover { background: #e8e8ff; }
        .maintenance-game-share-help { margin: 10px 0 0 !important; color: #686a87 !important; font-size: 11px !important; line-height: 1.45 !important; text-align: center; }
        .maintenance-game-status { min-height: 18px; margin: 10px 0 0 !important; color: #686a87 !important; font-size: 12px !important; line-height: 1.4 !important; text-align: center; }
        .maintenance-game-leaderboard { margin: 18px 0 0; padding-top: 16px; border-top: 1px solid #d9d7ff; }
        .maintenance-game-leaderboard h3 { margin: 0 0 9px; color: #2d35c8; font-size: 12px; font-weight: 800; text-align: center; text-transform: uppercase; }
        .maintenance-game-leaderboard ol { display: grid; gap: 5px; margin: 0; padding: 0; list-style: none; }
        .maintenance-game-leaderboard li { display: flex; justify-content: space-between; gap: 12px; padding: 7px 9px; border-radius: 7px; background: #fff; color: #424255; font-size: 12px; font-weight: 700; }
        .maintenance-game-leaderboard li span:last-child { color: #3430d2; }
        @keyframes maintenanceGameOpen { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes maintenancePulse { 50% { opacity: .35; transform: scale(.7); } }
        @media (max-width: 575px) { .maintenance-page { padding: 18px; } .maintenance-card { border-radius: 24px; } .maintenance-logo { margin-bottom: 30px; } }
    </style>
</head>
<body>
    <main class="maintenance-page">
        <section class="maintenance-card" aria-labelledby="maintenanceTitle">
            <img class="maintenance-logo" src="{{ $maintenanceLogo }}" alt="Обласний молодіжний центр">

            <span class="maintenance-badge">Тимчасово недоступно</span>
            <h1 id="maintenanceTitle">Проводимо технічні роботи</h1>
            <p>Перепрошуємо за тимчасові незручності. Ми оновлюємо сайт, щоб він працював ще краще.</p>

            @if (collect($maintenanceSocialNetworks)->contains(fn ($network) => $network['enabled'] && $network['url']))
                <p class="maintenance-social-caption">Слідкуйте за нашими новинами у соціальних мережах</p>

                <nav class="maintenance-socials" aria-label="Соціальні мережі">
                    @foreach ($maintenanceSocialNetworks as $network)
                        @if ($network['enabled'] && $network['url'])
                            <a class="maintenance-social-link" href="{{ $network['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $network['name'] }}">
                                <img src="{{ asset('icons/' . $network['icon']) }}" alt="">
                            </a>
                        @endif
                    @endforeach
                </nav>
            @endif

            <p class="maintenance-game-invite">
                Якщо не хочеться чекати — можеш зіграти в мінігру 🙂
                <button id="maintenanceGameToggle" class="maintenance-game-toggle" type="button">Зіграти</button>
            </p>

            <section id="maintenanceGame" class="maintenance-game" aria-label="Мінігра Спіймай зірку" hidden>
                <div class="maintenance-game-head">
                    <span>Спіймай зірку</span>
                    <span class="maintenance-game-timer">Час: <span id="maintenanceGameTimer">20</span> c</span>
                    <span class="maintenance-game-score">Бали: <span id="maintenanceGameScore">0</span></span>
                </div>

                <div class="maintenance-game-field">
                    <button id="maintenanceGameStar" class="maintenance-game-star" type="button" aria-label="Спіймати зірку">✦</button>
                </div>

                <p id="maintenanceGameHelp" class="maintenance-game-help">Натискай на зірку — після кожного влучання вона переміщується. Маєш 20 секунд.</p>

                <div id="maintenanceGameResult" class="maintenance-game-result" hidden>
                    <p class="maintenance-game-result-title">Твій результат: <span id="maintenanceGameFinalScore">0</span></p>

                    <div class="maintenance-game-form">
                        <input id="maintenanceGameNickname" class="maintenance-game-input" type="text" maxlength="30" autocomplete="nickname" placeholder="Твій нікнейм">
                        <button id="maintenanceGameSave" class="maintenance-game-button" type="button">Зберегти</button>
                    </div>

                    <div class="maintenance-game-share" aria-label="Поділитися результатом">
                        <button id="maintenanceGameShare" type="button">Поширити картку</button>
                        <button id="maintenanceGameInstagramTikTok" type="button">Instagram / TikTok</button>
                        <button id="maintenanceGameDownload" type="button">Завантажити</button>
                        <button id="maintenanceGameTelegram" type="button">Telegram</button>
                        <button id="maintenanceGameFacebook" type="button">Facebook</button>
                        <button id="maintenanceGameReplay" type="button">Ще раз</button>
                    </div>

                    <p class="maintenance-game-share-help">Для сторіс обери Instagram або TikTok у меню поширення. В Instagram посилання на сайт додається через стікер «Посилання».</p>

                    <p id="maintenanceGameStatus" class="maintenance-game-status" aria-live="polite"></p>
                </div>

                <section class="maintenance-game-leaderboard" aria-labelledby="maintenanceGameLeaderboardTitle">
                    <h3 id="maintenanceGameLeaderboardTitle">Топ‑10 гравців</h3>
                    <ol id="maintenanceGameLeaderboard"><li><span>Завантаження рекордів…</span></li></ol>
                </section>
            </section>
        </section>
    </main>

    <script>
        (() => {
            const toggle = document.getElementById('maintenanceGameToggle');
            const game = document.getElementById('maintenanceGame');
            const star = document.getElementById('maintenanceGameStar');
            const score = document.getElementById('maintenanceGameScore');
            const timer = document.getElementById('maintenanceGameTimer');
            const help = document.getElementById('maintenanceGameHelp');
            const result = document.getElementById('maintenanceGameResult');
            const finalScore = document.getElementById('maintenanceGameFinalScore');
            const nickname = document.getElementById('maintenanceGameNickname');
            const save = document.getElementById('maintenanceGameSave');
            const status = document.getElementById('maintenanceGameStatus');
            const leaderboard = document.getElementById('maintenanceGameLeaderboard');
            const share = document.getElementById('maintenanceGameShare');
            const instagramTikTok = document.getElementById('maintenanceGameInstagramTikTok');
            const download = document.getElementById('maintenanceGameDownload');
            const telegram = document.getElementById('maintenanceGameTelegram');
            const facebook = document.getElementById('maintenanceGameFacebook');
            const replay = document.getElementById('maintenanceGameReplay');

            if (!toggle || !game || !star || !score || !timer || !help || !result || !finalScore || !nickname || !save || !status || !leaderboard || !share || !instagramTikTok || !download || !telegram || !facebook || !replay) return;

            let points = 0;
            let timeLeft = 20;
            let countdown = null;
            let gameFinished = false;
            const shareLinks = @json($maintenanceShareLinks);

            function moveStar() {
                const left = 5 + Math.random() * 80;
                const top = 5 + Math.random() * 65;

                star.style.left = `${left}%`;
                star.style.top = `${top}%`;
            }

            function renderLeaderboard(scores) {
                leaderboard.innerHTML = '';

                if (!scores.length) {
                    const item = document.createElement('li');
                    item.textContent = 'Рекордів ще немає. Стань першим!';
                    leaderboard.append(item);
                    return;
                }

                scores.forEach((entry, index) => {
                    const item = document.createElement('li');
                    const player = document.createElement('span');
                    const result = document.createElement('span');

                    player.textContent = `${index + 1}. ${entry.nickname}`;
                    result.textContent = `${entry.score} б.`;
                    item.append(player, result);
                    leaderboard.append(item);
                });
            }

            async function loadLeaderboard() {
                try {
                    const response = await fetch('/api/maintenance-game/leaderboard');

                    if (!response.ok) throw new Error('Leaderboard unavailable');

                    const data = await response.json();
                    renderLeaderboard(data.scores || []);
                } catch (error) {
                    renderLeaderboard([]);
                }
            }

            function updateScore() {
                score.textContent = points;
                timer.textContent = timeLeft;
            }

            function startGame() {
                window.clearInterval(countdown);
                points = 0;
                timeLeft = 20;
                gameFinished = false;
                result.hidden = true;
                result.classList.remove('is-visible');
                nickname.value = '';
                status.textContent = '';
                star.disabled = false;
                help.textContent = 'Натискай на зірку — після кожного влучання вона переміщується. Маєш 20 секунд.';
                updateScore();
                moveStar();

                countdown = window.setInterval(() => {
                    timeLeft -= 1;
                    updateScore();

                    if (timeLeft <= 0) finishGame();
                }, 1000);
            }

            function finishGame() {
                if (gameFinished) return;

                gameFinished = true;
                window.clearInterval(countdown);
                star.disabled = true;
                finalScore.textContent = points;
                result.hidden = false;
                result.classList.add('is-visible');
                help.textContent = 'Час вийшов! Збережи результат і поділися ним.';
                nickname.focus();
            }

            function shareText() {
                return `Я набрав(ла) ${points} балів у мінігрі ОМЦ «Спіймай зірку»!`;
            }

            function shortUrl(url) {
                if (!url) return null;

                try {
                    return new URL(url).hostname.replace('www.', '') + new URL(url).pathname.replace(/\/$/, '');
                } catch (error) {
                    return url.replace(/^https?:\/\//, '').replace(/\/$/, '');
                }
            }

            function loadImage(source) {
                return new Promise((resolve, reject) => {
                    const image = new Image();
                    image.onload = () => resolve(image);
                    image.onerror = reject;
                    image.src = source;
                });
            }

            function drawCenteredText(context, text, x, y, maxWidth, lineHeight) {
                const words = text.split(' ');
                let line = '';
                let currentY = y;

                words.forEach(word => {
                    const candidate = line ? `${line} ${word}` : word;

                    if (context.measureText(candidate).width > maxWidth && line) {
                        context.fillText(line, x, currentY);
                        line = word;
                        currentY += lineHeight;
                        return;
                    }

                    line = candidate;
                });

                if (line) context.fillText(line, x, currentY);

                return currentY;
            }

            async function createShareCard() {
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                const width = 1080;
                const height = 1920;
                const center = width / 2;

                canvas.width = width;
                canvas.height = height;

                const gradient = context.createLinearGradient(0, 0, width, height);
                gradient.addColorStop(0, '#f8f8ff');
                gradient.addColorStop(1, '#e6e7ff');
                context.fillStyle = gradient;
                context.fillRect(0, 0, width, height);

                context.fillStyle = 'rgba(52, 48, 210, .11)';
                context.beginPath();
                context.arc(-110, -110, 510, 0, Math.PI * 2);
                context.fill();
                context.beginPath();
                context.arc(1190, 1840, 460, 0, Math.PI * 2);
                context.fill();

                context.fillStyle = '#ffffff';
                context.shadowColor = 'rgba(42, 45, 155, .16)';
                context.shadowBlur = 42;
                context.roundRect(80, 150, 920, 1500, 48);
                context.fill();
                context.shadowBlur = 0;

                try {
                    const logo = await loadImage(@json($maintenanceLogo));
                    const ratio = Math.min(520 / logo.width, 150 / logo.height);
                    const logoWidth = logo.width * ratio;
                    const logoHeight = logo.height * ratio;
                    context.drawImage(logo, center - logoWidth / 2, 250, logoWidth, logoHeight);
                } catch (error) {
                    context.fillStyle = '#3430d2';
                    context.font = '800 50px Commissioner, Arial';
                    context.textAlign = 'center';
                    context.fillText('ОБЛАСНИЙ МОЛОДІЖНИЙ ЦЕНТР', center, 340);
                }

                context.fillStyle = '#3430d2';
                context.font = '800 38px Commissioner, Arial';
                context.textAlign = 'center';
                context.fillText('МІНІГРА «СПІЙМАЙ ЗІРКУ»', center, 540);

                context.fillStyle = '#eeecff';
                context.beginPath();
                context.arc(center, 840, 210, 0, Math.PI * 2);
                context.fill();

                context.fillStyle = '#3430d2';
                context.font = '800 180px Commissioner, Arial';
                context.fillText(points, center, 900);
                context.font = '800 42px Commissioner, Arial';
                context.fillText('БАЛІВ', center, 975);

                context.fillStyle = '#171717';
                context.font = '700 42px Commissioner, Arial';
                drawCenteredText(context, shareText(), center, 1170, 740, 58);

                const cardLinks = [
                    `Сайт: ${shortUrl(shareLinks.site)}`,
                    shareLinks.instagram ? `Instagram: ${shortUrl(shareLinks.instagram)}` : null,
                    shareLinks.tiktok ? `TikTok: ${shortUrl(shareLinks.tiktok)}` : null,
                ].filter(Boolean);

                context.strokeStyle = '#d9d7ff';
                context.lineWidth = 3;
                context.beginPath();
                context.moveTo(180, 1370);
                context.lineTo(900, 1370);
                context.stroke();

                context.fillStyle = '#5e5f76';
                context.font = '600 28px Commissioner, Arial';
                cardLinks.forEach((link, index) => context.fillText(link, center, 1450 + index * 48));

                context.fillStyle = '#3430d2';
                context.font = '800 30px Commissioner, Arial';
                context.fillText('ПОЛТАВСЬКИЙ ОБЛАСНИЙ МОЛОДІЖНИЙ ЦЕНТР', center, 1580);

                return new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
            }

            function downloadShareCard(blob) {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = url;
                link.download = `omc-rekord-${points}.png`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            }

            async function shareResult(preferSocialApps = false) {
                status.textContent = 'Створюємо картку рекорду…';
                const blob = await createShareCard();

                if (!blob) throw new Error('Card unavailable');

                const file = new File([blob], `omc-rekord-${points}.png`, { type: 'image/png' });
                const shareData = { title: 'Мінігра ОМЦ', text: shareText(), files: [file] };

                if (navigator.share && (!navigator.canShare || navigator.canShare(shareData))) {
                    if (preferSocialApps) status.textContent = 'У меню поширення обери Instagram або TikTok.';

                    try {
                        await navigator.share(shareData);
                        status.textContent = 'Дякуємо за поширення!';
                    } catch (error) {
                        if (error.name !== 'AbortError') status.textContent = 'Не вдалося відкрити меню поширення.';
                    }

                    return;
                }

                downloadShareCard(blob);
                status.textContent = 'Картку завантажено — додай її в сторіс Instagram або TikTok.';
            }

            toggle.addEventListener('click', () => {
                game.hidden = false;
                game.classList.add('is-open');
                toggle.textContent = 'Граємо!';
                toggle.disabled = true;
                loadLeaderboard();
                startGame();
                star.focus();
            });

            star.addEventListener('click', () => {
                if (gameFinished) return;

                points += 1;
                updateScore();
                moveStar();
            });

            save.addEventListener('click', async () => {
                const playerName = nickname.value.trim();

                if (!playerName) {
                    status.textContent = 'Введи нікнейм, щоб зберегти рекорд.';
                    nickname.focus();
                    return;
                }

                save.disabled = true;
                status.textContent = 'Зберігаємо результат…';

                try {
                    const response = await fetch('/api/maintenance-game/scores', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ nickname: playerName, score: points }),
                    });

                    const data = await response.json();

                    if (!response.ok) throw new Error(data.message || 'Save failed');

                    status.textContent = 'Рекорд збережено в таблиці гравців!';
                    renderLeaderboard(data.scores || []);
                } catch (error) {
                    status.textContent = 'Не вдалося зберегти рекорд. Спробуй ще раз.';
                    save.disabled = false;
                }
            });

            share.addEventListener('click', async () => {
                try {
                    await shareResult();
                } catch (error) {
                    status.textContent = 'Не вдалося створити картку рекорду.';
                }
            });

            instagramTikTok.addEventListener('click', async () => {
                try {
                    await shareResult(true);
                } catch (error) {
                    status.textContent = 'Не вдалося створити картку для сторіс.';
                }
            });

            download.addEventListener('click', async () => {
                try {
                    status.textContent = 'Створюємо картку рекорду…';
                    const blob = await createShareCard();

                    if (!blob) throw new Error('Card unavailable');

                    downloadShareCard(blob);
                    status.textContent = 'Картку завантажено.';
                } catch (error) {
                    status.textContent = 'Не вдалося створити картку рекорду.';
                }
            });

            telegram.addEventListener('click', () => {
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent(shareText());
                window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank', 'noopener,noreferrer');
            });

            facebook.addEventListener('click', () => {
                const url = encodeURIComponent(window.location.href);
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'noopener,noreferrer');
            });

            replay.addEventListener('click', startGame);
        })();
    </script>
</body>
</html>
