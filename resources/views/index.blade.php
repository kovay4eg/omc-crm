<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Головна</title>

    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body { overflow-x: hidden; width: 100%; margin: 0; padding: 0; }
        body { background: #f5f5f5; font-family: 'Commissioner', sans-serif; }

        .banner-section { width: 100%; line-height: 0; }
        .banner-section img { width: 100%; height: auto; display: block; }

        /* ЗАГОЛОВОК ТА ПАРАЛАКС ТЕКСТ */
        .title-block { 
            position: relative; 
            margin-bottom: clamp(60px, 10vw, 120px); 
            padding: 40px 15px 0 15px;
            z-index: 1;
        }
        #bgText {
            position: absolute; top: 50%; left: 0; transform: translateY(-50%);
            font-size: clamp(80px, 15vw, 180px); font-weight: 800; color: #dbe3ff; opacity: 0.25;
            white-space: nowrap; pointer-events: none; z-index: 0;
        }
        .title-block h2 { position: relative; z-index: 2; color:#2e3aa1; font-size: clamp(24px, 4vw, 36px); font-weight:800; text-transform: uppercase; }

        /* КОНТЕЙНЕР КАРТОК */
        .cards-wrapper { 
            position: relative; 
            max-width: 1250px; 
            margin: 0 auto; 
        }

        /* БАЗОВА СТИЛІСТИКА КАРТКИ */
        .card {
            padding: 28px 24px; 
            border-radius: 28px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.04);
            display: flex; 
            flex-direction: column; 
            justify-content: flex-end;
            transition: 
                opacity 1s ease, 
                box-shadow 0.6s ease, 
                transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            opacity: 0; 
            border: none;
            position: relative;
            cursor: pointer;
            transform: rotate(var(--r)); /* Початковий нахил */
        }

        /* Анімація погойдування, що враховує нахил --r */
        @keyframes cardFloat {
            0% { transform: translateY(0) rotate(var(--r)); }
            100% { transform: translateY(-15px) rotate(var(--r)); }
        }

        .card.loaded {
            opacity: 1;
            animation: cardFloat 3s ease-in-out infinite alternate;
        }

        .card img { position: absolute; top: 65px; left: 24px; width: 80px; z-index: 1; pointer-events: none; }
        .card p { position: relative; z-index: 2; font-weight: 800; line-height: 1.3; color: #000; margin: 0; text-align: left; }

        /* КОЛЬОРИ */
        .c1 { background: #C7CBF3; --r: 6deg; --d: 0s; }
        .c2 { background: #9FA8F0; --r: -5deg; --d: 0.3s; }
        .c3 { background: #D6D9F7; --r: 4deg; --d: 0.1s; }
        .card-main { background: linear-gradient(135deg,#5B63E6,#4A54D1); --r: -4deg; --d: 0.4s; }
        .card-main p { color: #fff !important; }
        .c5 { background: #9FA8F0; --r: 5deg; --d: 0.2s; }

        /* --- 1. DESKTOP (ВІД 1200px) --- */
        @media (min-width: 1200px) {
            .cards-wrapper { height: 500px; }
            .card { position: absolute; width: 275px; height: 320px; }
            .card:hover {
                z-index: 100 !important;
                box-shadow: 0 40px 80px rgba(46, 58, 161, 0.25) !important;
                transform: scale(1.1) rotate(0deg) translateY(-25px) !important;
                animation: none !important;
            }
            .card.c1 { left:0; top:40px; }
            .card.c2 { left:240px; top:130px; }
            .card.c3 { left:480px; top:30px; }
            .card.card-main { left:720px; top:110px; }
            .card.c5 { left:960px; top:60px; }
        }

        /* --- 2. TABLETS (768px - 1199px) --- */
        @media (min-width: 768px) and (max-width: 1199px) {
            .cards-wrapper { 
                display: flex; flex-wrap: wrap; 
                justify-content: center; gap: 30px; padding: 0 20px;
            }
            .card { 
                position: relative !important; width: 300px; height: 300px; 
            }
        }

        /* --- 3. MOBILE (ДО 767px) --- */
        @media (max-width: 767px) {
            .cards-wrapper { 
                display: flex; flex-direction: column; 
                align-items: center; padding-bottom: 50px; 
            }
            .card {
                position: relative !important; width: 270px; height: 270px;
                margin-bottom: -35px; /* Накладання */
            }
            .card img { top: 45px; width: 70px; }
            .card p { font-size: 16px; }

            /* Каскад (ліво-право) */
            .card:nth-child(1) { z-index: 5; margin-left: -35px; }
            .card:nth-child(2) { z-index: 4; margin-left: 35px; }
            .card:nth-child(3) { z-index: 3; margin-left: -25px; }
            .card:nth-child(4) { z-index: 2; margin-left: 25px; }
            .card:nth-child(5) { z-index: 1; margin-left: -15px; }
        }
    </style>
</head>
<body>

    <section class="banner-section">
        @if(isset($settings) && $settings->banner_image)
            <img src="{{ asset('storage/' . $settings->banner_image) }}" alt="Banner">
        @endif
    </section>

    <section style="overflow:hidden; padding: 60px 0;">
        <div class="container-fluid p-0">
            <div class="title-block container">
                <div id="bgText">НАПРЯМИ ДІЯЛЬНОСТІ &nbsp; НАПРЯМИ ДІЯЛЬНОСТІ</div>
                <h2>ГОЛОВНІ НАПРЯМИ ДІЯЛЬНОСТІ:</h2>
            </div>

            <div class="cards-wrapper">
                <div class="card c1">
                    <img src="/images/cards/education.png">
                    <p>НЕФОРМАЛЬНА ОСВІТА, ЯКОЇ БРАКУЄ В ПІДРУЧНИКАХ</p>
                </div>
                <div class="card c2">
                    <img src="/images/cards/career.png">
                    <p>ВПЕВНЕНИЙ КАР'ЄРНИЙ СТАРТ ТА ПРОФОРІЄНТАЦІЯ</p>
                </div>
                <div class="card c3">
                    <img src="/images/cards/community.png">
                    <p>РОЗВИТОК МОЛОДІЖНИХ РАД ТА ПІДТРИМКА ІНІЦІАТИВ</p>
                </div>
                <div class="card card-main">
                    <img src="/images/cards/health.png">
                    <p>ЗДОРОВИЙ СПОСІБ ЖИТТЯ ТА МЕНТАЛЬНА СТІЙКІСТЬ</p>
                </div>
                <div class="card c5">
                    <img src="/images/cards/patriotic.png">
                    <p>ПАТРІОТИЧНЕ ВИХОВАННЯ ТА ЗМІСТОВНЕ ДОЗВІЛЛЯ</p>
                </div>
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
                        
                        setTimeout(() => {
                            card.classList.add('loaded');
                        }, delay);
                        
                        observer.unobserve(card);
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => observer.observe(card));
        });

        // Паралакс фонового тексту
        window.addEventListener('scroll', () => {
            const text = document.getElementById('bgText');
            if (text) {
                let speed = window.innerWidth < 992 ? 0.2 : 0.4;
                text.style.left = `-${window.scrollY * speed}px`;
            }
        });
    </script>
</body>
</html>