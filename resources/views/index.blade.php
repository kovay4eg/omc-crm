<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Напрями діяльності</title>

    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body { overflow-x: hidden; width: 100%; margin: 0; padding: 0; }
        body { background: #f5f5f5; font-family: 'Commissioner', sans-serif; }

        .container-1200 {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            position: relative;
        }

        .banner-section img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 20px;
        }

        .content-section {
            position: relative;
            padding: 80px 0;
        }

        .title-block { 
            position: relative; 
            margin-bottom: 80px;
            display: flex;
            align-items: center;
            min-height: 200px;
        }

        #bgText {
            position: absolute; 
            top: 50%; 
            left: 0; 
            transform: translateY(-50%);
            font-size: clamp(80px, 15vw, 180px); 
            font-weight: 800; 
            color: #dbe3ff; 
            opacity: 0.45;
            white-space: nowrap; 
            pointer-events: none; 
            z-index: 0;
            width: 200vw;
        }

        .title-block h2 { 
            position: relative;
            z-index: 2;
            color:#2e3aa1; 
            font-size: clamp(24px, 4vw, 36px); 
            font-weight:800; 
            text-transform: uppercase; 
            margin: 0;
            line-height: 1;
        }

        .cards-wrapper { position: relative; width: 100%; z-index: 2; }

        .card {
            padding: 28px 24px;
            border-radius: 28px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            transition: opacity 1s ease, transform 0.4s ease, box-shadow 0.4s ease;
            opacity: 0;
            border: none;
            position: relative;
            cursor: pointer;
            z-index: 1;
        }

        @keyframes cardFloat {
            0% { transform: translateY(0) rotate(var(--r)); }
            100% { transform: translateY(-15px) rotate(var(--r)); }
        }

        .card.loaded {
            opacity: 1;
            animation: cardFloat 3s ease-in-out infinite alternate;
        }

        /* ✅ FIXED HOVER */
        .card:hover {
            animation: none;
            transform: translateY(-20px) scale(1.07) rotate(0deg);
            z-index: 20;
            box-shadow: 0 30px 70px rgba(0,0,0,0.2);
        }

        .card img {
            position: absolute;
            top: 65px;
            left: 24px;
            width: 80px;
        }

        .card p {
            font-weight: 800;
            line-height: 1.2;
            margin: 0;
        }

        .c1 { background: #C7CBF3; --r: 6deg; --d: 0s; }
        .c2 { background: #9FA8F0; --r: -5deg; --d: 0.3s; }
        .c3 { background: #D6D9F7; --r: 4deg; --d: 0.1s; }
        .card-main { background: linear-gradient(135deg,#5B63E6,#4A54D1); --r: -4deg; --d: 0.4s; }
        .card-main p { color: #fff; }
        .c5 { background: #9FA8F0; --r: 5deg; --d: 0.2s; }

        @media (min-width: 1200px) {
            .cards-wrapper { height: 480px; }
            .card { position: absolute; width: 275px; height: 320px; }
            .card.c1 { left:0; top:40px; }
            .card.c2 { left:230px; top:130px; }
            .card.c3 { left:460px; top:30px; }
            .card.card-main { left:690px; top:110px; }
            .card.c5 { left:925px; top:60px; }
        }

        @media (max-width: 1199px) {
            .cards-wrapper {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 25px;
            }
            .card {
                position: relative !important;
                width: 280px;
                height: 280px;
            }
        }
    </style>
</head>

<body>

@php
    $settings = \App\Models\HomepageSetting::first();
@endphp

<div style="height:195px;"></div>

<x-header />

<div class="container-1200">
    <section class="banner-section">
        <picture>
            @if($settings && $settings->mobile_banner_image)
                <source media="(max-width: 768px)" srcset="{{ asset('storage/' . $settings->mobile_banner_image) }}">
            @endif

            @if($settings && $settings->banner_image)
                <img src="{{ asset('storage/' . $settings->banner_image) }}" alt="Banner">
            @endif
        </picture>
    </section>
</div>

<section class="content-section">
    <div class="container-1200">

        <div class="title-block">
            <div id="bgText">НАПРЯМИ ДІЯЛЬНОСТІ &nbsp; НАПРЯМИ ДІЯЛЬНОСТІ</div>
            <h2>ГОЛОВНІ НАПРЯМИ ДІЯЛЬНОСТІ:</h2>
        </div>

        <div class="cards-wrapper">
            <div class="card c1"><img src="/images/cards/education.png"><p>НЕФОРМАЛЬНА ОСВІТА, ЯКОЇ БРАКУЄ В ПІДРУЧНИКАХ</p></div>
            <div class="card c2"><img src="/images/cards/career.png"><p>ВПЕВНЕНИЙ КАР'ЄРНИЙ СТАРТ ТА ПРОФОРІЄНТАЦІЯ</p></div>
            <div class="card c3"><img src="/images/cards/community.png"><p>РОЗВИТОК МОЛОДІЖНИХ РАД ТА ПІДТРИМКА ІНІЦІАТИВ</p></div>
            <div class="card card-main"><img src="/images/cards/health.png"><p>ЗДОРОВИЙ СПОСІБ ЖИТТЯ ТА МЕНТАЛЬНА СТІЙКІСТЬ</p></div>
            <div class="card c5"><img src="/images/cards/patriotic.png"><p>ПАТРІОТИЧНЕ ВИХОВАННЯ ТА ЗМІСТОВНЕ ДОЗВІЛЛЯ</p></div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const card = entry.target;
                const delay = parseFloat(getComputedStyle(card).getPropertyValue('--d')) * 1000;
                setTimeout(() => card.classList.add('loaded'), delay);
                observer.unobserve(card);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => observer.observe(card));
});

window.addEventListener('scroll', () => {
    const text = document.getElementById('bgText');
    if (text) {
        let speed = 0.4;
        text.style.transform = `translateY(-50%) translateX(-${window.scrollY * speed}px)`;
    }
});
</script>

</body>
</html>