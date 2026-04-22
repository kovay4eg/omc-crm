<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Головна</title>

    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: #f5f5f5;
            font-family: 'Commissioner', sans-serif;
        }

        /* ===== БЛОК ЗАГОЛОВОК + ФОН ===== */
        .title-block {
            position: relative;
            margin-bottom: 140px;
        }

        #bgText {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            font-size: 180px;
            font-weight: 800;
            color: #dbe3ff;
            opacity: 0.25;
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
        }

        .title-block h2 {
            position: relative;
            z-index: 2;
            color:#2e3aa1;
            font-size:36px;
            font-weight:800;
        }

        /* ===== КАРТКИ ===== */
        .card {
            position: absolute;
            width: 270px;
            height: 315px;
            padding: 28px 24px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);

            display: flex;
            flex-direction: column;
            justify-content: flex-end;

            opacity: 0;
            transform-origin: center;

            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05) translateY(-10px) !important;
            z-index: 10;
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }

        .card img {
            position: absolute;
            top: 45px;
            left: 24px;
            width: 90px;
        }

        .card p {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.35;
            color: #000;
        }

        .c1 { background: #C7CBF3; }
        .c2 { background: #9FA8F0; }
        .c3 { background: #D6D9F7; }
        .c5 { background: #9FA8F0; }

        .card-main {
            background: linear-gradient(135deg,#5B63E6,#4A54D1);
        }

        .c1 { z-index: 1; }
        .c2 { z-index: 2; }
        .c3 { z-index: 3; }
        .c5 { z-index: 4; }
        .card-main { z-index: 6; }

    </style>
</head>

<body>

@php
    $settings = $settings ?? null;
@endphp

<!-- HERO (ВИПРАВЛЕНО) -->
<section style="max-width:1100px;margin:0 auto;">
    @if($settings && $settings->banner_image)
        <img src="{{ asset('storage/' . $settings->banner_image) }}" style="width:100%;">
    @endif
</section>

<!-- НАПРЯМИ -->
<section style="position:relative;padding:160px 0;overflow:hidden;">

    <div style="max-width:1200px;margin:auto;padding:0 20px;position:relative;">

        <div class="title-block">

            <div id="bgText">
                НАПРЯМИ ДІЯЛЬНОСТІ &nbsp;&nbsp;&nbsp; НАПРЯМИ ДІЯЛЬНОСТІ
            </div>

            <h2>
                ГОЛОВНІ НАПРЯМИ ДІЯЛЬНОСТІ:
            </h2>

        </div>

        <div style="position:relative;height:360px;max-width:1150px;margin:auto;">

            <div class="card c1" style="left:0px; top:100px; transform:rotate(+10deg);">
                <img src="/images/cards/education.png">
                <p>НЕФОРМАЛЬНА ОСВІТА, ЯКОЇ БРАКУЄ В ПІДРУЧНИКАХ</p>
            </div>

            <div class="card c2" style="left:255px; top:155px; transform:rotate(-6deg);">
                <img src="/images/cards/career.png">
                <p>ВПЕВНЕНИЙ КАР'ЄРНИЙ СТАРТ ТА ПРОФОРІЄНТАЦІЯ</p>
            </div>

            <div class="card c3" style="left:520px; top:90px; transform:rotate(+5deg);">
                <img src="/images/cards/community.png">
                <p>РОЗВИТОК МОЛОДІЖНИХ РАД ТА ПІДТРИМКА ІНІЦІАТИВ</p>
            </div>

            <div class="card card-main" style="left:780px; top:140px; transform:rotate(-4deg);">
                <img src="/images/cards/health.png">
                <p>ЗДОРОВИЙ СПОСІБ ЖИТТЯ ТА МЕНТАЛЬНА СТІЙКІСТЬ</p>
            </div>

            <div class="card c5" style="left:1060px; top:170px; transform:rotate(10deg);">
                <img src="/images/cards/patriotic.png">
                <p>ПАТРІОТИЧНЕ ВИХОВАННЯ ТА ЗМІСТОВНЕ ДОЗВІЛЛЯ</p>
            </div>

        </div>

    </div>

</section>

<script>
window.addEventListener('scroll', () => {
    const text = document.getElementById('bgText');
    let offset = window.scrollY * 0.4;
    text.style.transform = `translateX(-${offset}px) translateY(-50%)`;
});

const cards = document.querySelectorAll('.card');

cards.forEach((card, i) => {

    let base = card.style.transform;

    card.style.transform = base + ' translateX(300px)';
    card.style.opacity = "0";

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {

                setTimeout(() => {
                    card.style.transition = "all 1.4s cubic-bezier(0.22,1,0.36,1)";
                    card.style.opacity = "1";
                    card.style.transform = base + ' translateX(0)';
                }, i * 200);

            }
        });
    }, { threshold: 0.3 });

    observer.observe(card);
});
</script>

</body>
</html>