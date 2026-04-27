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

    /* ЗАГОЛОВОК ТА ФОН */
    .title-block { 
        position: relative; 
        margin-bottom: 120px; 
        padding-top: 40px;
        z-index: 1;
    }
    #bgText {
        position: absolute; 
        top: 50%; 
        left: 0; 
        transform: translateY(-50%);
        font-size: clamp(60px, 12vw, 160px); 
        font-weight: 800; 
        color: #dbe3ff; 
        opacity: 0.25;
        white-space: nowrap; 
        pointer-events: none; 
        z-index: 0;
    }
    .title-block h2 { position: relative; z-index: 2; color:#2e3aa1; font-size: clamp(24px, 5vw, 36px); font-weight:800; text-transform: uppercase; }

    /* КОНТЕЙНЕР КАРТОК */
    .cards-wrapper { 
        position: relative; 
        height: 520px; 
        max-width: 1150px; 
        margin: 0 auto; 
    }

    @keyframes cardFloat {
        from { transform: translateY(0) rotate(var(--r)); }
        to { transform: translateY(-10px) rotate(var(--r)); }
    }

    .card {
        position: absolute;
        width: 270px; height: 320px;
        padding: 28px 24px; border-radius: 28px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.04);
        display: flex; flex-direction: column; justify-content: flex-end;
        transition: 
            opacity 1s ease, 
            box-shadow 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94), 
            transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        opacity: 0;
        cursor: pointer;
    }

    .card.loaded {
        opacity: 1;
        animation: cardFloat 3.5s ease-in-out infinite alternate;
    }

    .card img { position: absolute; top: 75px; left: 24px; width: 80px; z-index: 1; pointer-events: none; }
    .card p { position: relative; z-index: 2; font-size: 19px; font-weight: 800; line-height: 1.3; color: #000; margin: 0; }

    /* ДЕКСКТОП ХОВЕР */
    @media (min-width: 993px) {
        .card:hover {
            z-index: 100 !important;
            box-shadow: 0 40px 80px rgba(46, 58, 161, 0.25) !important;
            transform: scale(1.1) rotate(0deg) translateY(-25px) !important;
            animation-play-state: paused;
        }
    }

    /* Кольори */
    .c1 { background: #C7CBF3; --r: 8deg; --d: 0s; }
    .c2 { background: #9FA8F0; --r: -6deg; --d: 0.4s; }
    .c3 { background: #D6D9F7; --r: 5deg; --d: 0.2s; }
    .card-main { background: linear-gradient(135deg,#5B63E6,#4A54D1); --r: -5deg; --d: 0.6s; }
    .card-main p { color: #fff; }
    .c5 { background: #9FA8F0; --r: 7deg; --d: 0.1s; }

    /* МОБІЛЬНА ВЕРСІЯ (ОПТИМІЗОВАНА) */
    @media (max-width: 992px) {
        .cards-wrapper {
            display: flex; flex-direction: column; align-items: center;
            height: auto !important; 
            padding: 10px 0 60px 0;
        }
        .card {
            position: relative !important; 
            left: auto !important; top: auto !important;
            width: 260px; height: 260px;
            margin-bottom: -20px; /* Повертаємо стильне накладання */
            transform: rotate(var(--r));
        }
        
        /* Порядок шарів для накладання зверху вниз */
        .card:nth-child(1) { z-index: 5; align-self: center; margin-left: -40px; }
        .card:nth-child(2) { z-index: 4; align-self: center; margin-left: 40px; }
        .card:nth-child(3) { z-index: 3; align-self: center; margin-left: -30px; }
        .card:nth-child(4) { z-index: 2; align-self: center; margin-left: 30px; }
        .card:nth-child(5) { z-index: 1; align-self: center; margin-left: -20px; }

        .card img { top: 50px; width: 70px; }
        /* Зменшуємо шрифт, щоб текст не ламав картку */
        .card p { 
            font-size: 16px; 
            letter-spacing: -0.3px;
        }
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
        <div class="container">
            <div class="title-block">
                <div id="bgText">НАПРЯМИ ДІЯЛЬНОСТІ &nbsp; НАПРЯМИ ДІЯЛЬНОСТІ</div>
                <h2>ГОЛОВНІ НАПРЯМИ ДІЯЛЬНОСТІ:</h2>
            </div>

            <div class="cards-wrapper">
                <div class="card c1" style="left:0; top:40px;">
                    <img src="/images/cards/education.png">
                    <p>НЕФОРМАЛЬНА ОСВІТА, ЯКОЇ БРАКУЄ В ПІДРУЧНИКАХ</p>
                </div>
                <div class="card c2" style="left:235px; top:130px;">
                    <img src="/images/cards/career.png">
                    <p>ВПЕВНЕНИЙ КАР'ЄРНИЙ СТАРТ ТА ПРОФОРІЄНТАЦІЯ</p>
                </div>
                <div class="card c3" style="left:475px; top:30px;">
                    <img src="/images/cards/community.png">
                    <p>РОЗВИТОК МОЛОДІЖНИХ РАД ТА ПІДТРИМКА ІНІЦІАТИВ</p>
                </div>
                <div class="card card-main" style="left:715px; top:110px;">
                    <img src="/images/cards/health.png">
                    <p>ЗДОРОВИЙ СПОСІБ ЖИТТЯ ТА МЕНТАЛЬНА СТІЙКІСТЬ</p>
                </div>
                <div class="card c5" style="left:950px; top:60px;">
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
                        const i = Array.from(cards).indexOf(card);
                        
                        setTimeout(() => {
                            card.classList.add('loaded');
                            card.style.animationDelay = card.style.getPropertyValue('--d');
                        }, i * 150);
                        
                        observer.unobserve(card);
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => observer.observe(card));
        });

        window.addEventListener('scroll', () => {
            const text = document.getElementById('bgText');
            if (text) {
                let speed = window.innerWidth < 992 ? 0.15 : 0.4;
                text.style.left = `-${window.scrollY * speed}px`;
            }
        });
    </script>
</body>
</html>