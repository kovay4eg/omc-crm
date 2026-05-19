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
        body { background: #ffffff; font-family: 'Commissioner', sans-serif; }

        .container-1200 { max-width: 1200px; margin: 0 auto; padding: 0 15px; position: relative; }

        /* Banner */
        .banner-section img { width: 100%; height: auto; display: block; border-radius: 20px; }

        /* Titles & Background Text */
        .content-section { position: relative; padding: 80px 0; }
        .title-block { position: relative; margin-bottom: 80px; display: flex; align-items: center; min-height: 200px; }

        #bgText, #bgTextSecondary {
            position: absolute; top: 50%; left: 0; transform: translateY(-50%);
            font-size: clamp(80px, 15vw, 180px); font-weight: 800; color: #dbe3ff; 
            opacity: 0.45; white-space: nowrap; pointer-events: none; z-index: 0; width: 250vw;
            will-change: transform;
        }

        .title-block h2 { position: relative; z-index: 2; color:#2e3aa1; font-size: clamp(24px, 4vw, 36px); font-weight:800; text-transform: uppercase; margin: 0; }

        /* Floating Cards */
        .cards-wrapper { position: relative; width: 100%; z-index: 2; }
        .card {
            padding: 28px 24px; border-radius: 28px; box-shadow: 0 10px 25px rgba(0,0,0,0.04);
            display: flex; flex-direction: column; justify-content: flex-end;
            transition: opacity 1s ease, transform 0.4s ease, box-shadow 0.4s ease;
            opacity: 0; border: none; position: relative; cursor: pointer;
            
        }
    
        @keyframes cardFloat {
            0% { transform: translateY(0) rotate(var(--r)); }
            100% { transform: translateY(-15px) rotate(var(--r)); }
        }

        .card.loaded { opacity: 1; animation: cardFloat 3s ease-in-out infinite alternate; }
        .card:hover { animation: none; transform: translateY(-20px) scale(1.07) rotate(0deg); z-index: 20; box-shadow: 0 30px 70px rgba(0,0,0,0.2); }
        .card img { position: absolute; top: 65px; left: 24px; width: 80px; }
        .card p { font-weight: 800; line-height: 1.2; margin: 0; }

        .c1 { background: #C7CBF3; --r: 6deg; --d: 0s; }
        .c2 { background: #9FA8F0; --r: -5deg; --d: 0.3s; }
        .c3 { background: #D6D9F7; --r: 4deg; --d: 0.1s; }
        .card-main { background: linear-gradient(135deg,#5B63E6,#4A54D1); --r: -4deg; --d: 0.4s; }
        .card-main p { color: #fff; }
        .c5 { background: #9FA8F0; --r: 5deg; --d: 0.2s; }

        @media (max-width: 1199px) {
            .cards-wrapper {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 30px;
                padding-top: 20px;
            }
            .card {
                width: 275px;
                height: 320px;
                position: relative;
            }
        }

        @media (min-width: 1200px) {
            .mobile-title {display:none;}
            .cards-wrapper { height: 480px; }
            .card { position: absolute; width: 275px; height: 320px; }
            .card.c1 { left:0; top:40px; }
            .card.c2 { left:230px; top:130px; }
            .card.c3 { left:460px; top:30px; }
            .card.card-main { left:690px; top:110px; }
            .card.c5 { left:925px; top:60px; }
            .desktop-title {display: none;}
        }

        /* Accordion General */
        .info-accordion-section { background-color: #fff; padding-bottom: 60px; }
        .custom-accordion .accordion-item { border: none; border-bottom: 1px solid #f3d9da; margin-bottom: 10px; background: transparent; }
        .custom-accordion .accordion-button { 
            padding: 25px 0; font-size: 1.25rem; font-weight: 800; color: #2e3aa1;
            background: none !important; box-shadow: none !important; text-transform: uppercase;
            display: flex; align-items: center; justify-content: space-between;
        }
        .custom-accordion .accordion-button::after { display: none; }

        .icon-flower { width: 32px; height: 32px; margin-right: 15px; content: url("/images/icons/flower.svg"); display: inline-block; transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); will-change: transform; }
        .flower-spin { transform: rotate(360deg) !important; }
        .icon-arrow-custom { width: 32px; height: 32px; content: url("/images/icons/arrow.svg"); transition: all 0.4s ease; }
        .accordion-button:not(.collapsed) .icon-arrow-custom { transform: rotate(90deg); filter: invert(18%) sepia(51%) saturate(5436%) hue-rotate(229deg) brightness(91%) contrast(92%); }

        /* Accordion 1 & 2 styles */
        .design-intro-text { font-size: 1.1rem; line-height: 1.5; color: #000; margin-bottom: 40px; }
        .design-cards-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .design-info-card { background: #E8EAFB; border-radius: 24px; padding: 30px; position: relative; min-height: 380px; display: flex; flex-direction: column; justify-content: flex-end; }
        .icon-mic-img { position: absolute; top: 30px; right: 30px; width: 40px; height: 40px; object-fit: contain; }
        .design-info-card h4 { font-weight: 800; color: #000; font-size: 1.2rem; margin-bottom: 15px; text-transform: uppercase; }
        .design-info-card p { font-size: 0.95rem; line-height: 1.4; color: #333; margin: 0; }
        .design-legal-block { background: #E8EAFB; border-radius: 24px; padding: 40px; margin-top: 20px; }
        .design-legal-block h4 { font-weight: 800; text-transform: uppercase; margin-bottom: 25px; }
        .design-legal-block ul { list-style: none; padding: 0; }
        .design-legal-block ul li { position: relative; padding-left: 25px; margin-bottom: 8px; font-size: 0.95rem; line-height: 1.4; }
        .design-legal-block ul li::before { content: "◆"; position: absolute; left: 0; color: #2e3aa1; font-size: 1rem; }

        .target-intro-text { text-align: center; font-weight: 700; font-size: 1.1rem; margin-bottom: 30px; text-transform: uppercase; line-height: 1.4; }
        .target-cards-container { display: flex; justify-content: center; gap: 15px; padding: 20px 0 40px 0; flex-wrap: wrap; perspective: 1000px; }
        .t-card { width: 220px; height: 280px; padding: 25px 20px; border-radius: 24px; display: flex; flex-direction: column; justify-content: flex-end; position: relative; transition: opacity 1s ease, transform 0.4s ease, box-shadow 0.4s ease; opacity: 0; cursor: pointer; border: none; box-sizing: border-box; }
        .t-card.loaded { opacity: 1; animation: cardFloat 3.5s ease-in-out infinite alternate; }
        .t-card img { position: absolute; top: 25px; left: 20px; width: 60px; height: auto; }
        .t-card p { font-weight: 800; font-size: 14px; line-height: 1.2; margin: 0; text-transform: uppercase; color: #000; }
        /* Додайте це для карток з файлу image_949d41.png */
        .t-card:hover {
        animation: none !important; /* Зупиняємо плавання */
        transform: translateY(-15px) scale(1.05) rotate(0deg) !important; /* Піднімаємо та вирівнюємо */
        z-index: 10; /* Виводимо на передній план */
        box-shadow: 0 20px 40px rgba(0,0,0,0.12); /* Додаємо м'яку тінь */
        transition: all 0.3s ease !important; /* Робимо рух плавним */
       }
        .t-card:hover img {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }
        .tc-1 { background: #b1b8e8; --r: -3deg; --d: 0.1s; }
        .tc-2 { background: #919ce1; --r: 2deg; --d: 0.2s; margin-top: 15px; }
        .tc-3 { background: #ffffff; --r: -2deg; --d: 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .tc-4 { background: #5a66d6; --r: 3deg; --d: 0.4s; margin-top: 15px; }
        .tc-4 p { color: #fff; }
        .tc-5 { background: #b1b8e8; --r: -2deg; --d: 0.5s; }

        /* Accordion 3 (Шаховий порядок як на фото 2) */
        .offers-wrapper { padding: 40px 0; text-align: center; }
        .offers-intro-text { font-size: 1.1rem; margin-bottom: 50px; text-transform: uppercase; }
        .offers-grid-container { border-top: 1px dashed #dbe3ff; border-bottom: 1px dashed #dbe3ff; padding: 60px 0; display: flex; flex-direction: column; gap: 60px; }
        .offers-row { display: flex; justify-content: center; flex-wrap: wrap; gap: 40px; }
        .offer-item { width: 300px; display: flex; flex-direction: column; align-items: center; }
        .offer-item img { width: 120px; height: auto; margin-bottom: 25px; }
        .offer-item h5 { font-weight: 800; font-size: 1rem; color: #000; margin-bottom: 15px; text-transform: uppercase; min-height: 40px; display: flex; align-items: center; justify-content: center; }
        .offer-item p { font-size: 0.95rem; line-height: 1.4; color: #333; margin: 0; }
        .offers-footer-note { margin-top: 50px; font-weight: 700; font-size: 1rem; }

        @media (max-width: 991px) {
            .design-cards-row { grid-template-columns: repeat(2, 1fr); }
            .offer-item { width: 40%; }
            .desktop-title {
        display: none;
    }
        }

        @media (max-width: 767px) {
              .t-card{
        width:50% !important;
        min-height:220px;
        height:auto !important;
        padding:10px;
        position:relative;
        
    }
    .desktop-title {
        display: none;
    }



    .t-card img{
        width:60px !important;
        left:20px !important;
        top:30px !important;
    }

    .t-card h5{
        margin-top:90px;
        font-size:15px;
        line-height:1.1;
    }

    .tc-1{
        transform:rotate(-7deg) !important;
    }

    .tc-2{
        transform:rotate(6deg) !important;
    }

    .tc-3{
        transform:rotate(-5deg) !important;
    }

    .tc-4{
        transform:rotate(7deg) !important;
    }

    .tc-5{
        transform:rotate(-6deg) !important;
    }
    }
    

    .mobile-title{
        display:flex;
        flex-direction:column;
        line-height:1.05;
    }

    .mobile-title span{
        display:block;
    }

    .acc-btn-left{
        display:flex;
        align-items:center;
        gap:12px;
    }

    .accordion-title{
        display:flex;
        flex-direction:column;
    }

        

    </style>
</head>
<body>

@php $settings = \App\Models\HomepageSetting::first(); @endphp
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

<section id="about-section" class="info-accordion-section">
    <div class="container-1200">
        <div class="title-block">
            <div id="bgTextSecondary">ПРО НАС &nbsp; ПРО НАС &nbsp; ПРО НАС &nbsp; ПРО НАС</div>
            <h2>ПРО НАС</h2>
        </div>

        <div class="accordion custom-accordion" id="mainAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        <span class="acc-btn-left"><i class="icon-flower"></i> ЩО ТАКЕ ПОМЦ?</span>
                        <i class="icon-arrow-custom"></i>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#mainAccordion">
                    <div class="accordion-body px-0">
                        <p class="design-intro-text"><strong>Полтавський обласний молодіжний центр (ПОМЦ)</strong> — це динамічна платформа для розвитку, творчості та професійного зростання молоді нашого регіону. Ми віримо, що молодь — це активне сьогодення Полтавщини.</p>
                        <div class="design-cards-row">
                            <div class="design-info-card"><img src="/icons/microphone.svg" class="icon-mic-img"><h4>НАША МІСІЯ</h4><p>Ми формуємо безпечне та відкрите середовище, де кожна молода людина має голос, підтримку та реальний шанс діяти.</p></div>
                            <div class="design-info-card"><img src="/icons/microphone.svg" class="icon-mic-img"><h4>ОФІЦІЙНА ІНФОРМАЦІЯ</h4><p>Установа забезпечує реалізацію молодіжної політики в регіоні через неформальну освіту та волонтерство.</p></div>
                            <div class="design-info-card"><img src="/icons/microphone.svg" class="icon-mic-img"><h4>УПРАВЛІННЯ</h4><p>Установа перебуває у підпорядкуванні Управління молоді та спорту Полтавської ОДА.</p></div>
                        </div>
                        <div class="design-legal-block">
                            <h4>НОРМАТИВНО-ПРАВОВА БАЗА</h4>
                            <ul>
                                <li>Конституцією України;</li>
                                <li>Законом України «Про основні засади молодіжної політики»;</li>
                                <li>Власним Статутом.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                        <span class="acc-btn-left"><i class="icon-flower"></i> ДЛЯ КОГО МИ?</span>
                        <i class="icon-arrow-custom"></i>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#mainAccordion">
                    <div class="accordion-body px-0">
                        <p class="target-intro-text">ПОМЦ – ДЛЯ ТЕБЕ, ЯКЩО ТИ:</p>
                        <div class="target-cards-container">
                            <div class="t-card tc-1"><img src="/images/cards/education.png"><p>Шукаєш місце, де цікаво вчитись новому</p></div>
                            <div class="t-card tc-2"><img src="/images/cards/lightbulb.png"><p>Маєш власні ідеї та хочеш їх реалізувати</p></div>
                            <div class="t-card tc-3"><img src="/images/cards/community.png"><p>Прагнеш нових знайомств чи шукаєш свою спільноту</p></div>
                            <div class="t-card tc-4"><img src="/images/cards/health.png"><p>Хочеш реально впливати на життя своєї громади</p></div>
                            <div class="t-card tc-5"><img src="/images/cards/patriotic.png"><p>Цікавишся можливостями навчання та проєктів</p></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">

            <span class="acc-btn-left">

                <i class="icon-flower"></i>

                <span class="accordion-title">

                    <span class="desktop-title">
                        ЩО МИ МОЖЕМО ТОБІ ДАТИ?
                    </span>

                    <span class="mobile-title">
                        <span>ЩО МИ МОЖЕМО ТОБІ</span>
                        <span>ДАТИ?</span>
                    </span>

                </span>

            </span>

            <i class="icon-arrow-custom"></i>

        </button>
    </h2>

    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#mainAccordion">
        <div class="accordion-body px-0">
            <div class="offers-wrapper">

                <p class="offers-intro-text">
                    ПОМЦ – це про можливості, які стають реальністю.
                    <br>
                    <strong>У НАС ТИ МОЖЕШ:</strong>
                </p>

                <div class="offers-grid-container">

                    <div class="offers-row top-row">

                        <div class="offer-item">
                            <img src="/images/cards/patriotic.png" alt="icon">
                            <h5>НАВЧАТИСЯ</h5>
                            <p>
                                Проводимо тренінги з лідерства,
                                проєктного менеджменту та комунікацій
                            </p>
                        </div>

                        <div class="offer-item">
                            <img src="/images/cards/patriotic.png" alt="icon">
                            <h5>РЕАЛІЗОВУВАТИ СВОЇ ІДЕЇ</h5>
                            <p>
                                Допомагаємо перетворити твої задуми
                                на реальні проєкти
                            </p>
                        </div>

                        <div class="offer-item">
                            <img src="/images/cards/patriotic.png" alt="icon">
                            <h5>ЗНАХОДИТИ НОВІ МОЖЛИВОСТІ</h5>
                            <p>
                                Участь у всеукраїнських форумах,
                                нові знайомства та корисні контакти.
                                Розвиток кар'єрних можливостей у форматі
                                зустрічей TEDx та Speed Friending
                                з успішними підприємцями
                            </p>
                        </div>

                    </div>

                    <div class="offers-row bottom-row">

                        <div class="offer-item">
                            <img src="/images/cards/patriotic.png" alt="icon">
                            <h5>РОЗВИВАТИСЯ ТВОРЧО, МЕНТАЛЬНО І ФІЗИЧНО</h5>
                            <p>
                                Організовуємо заходи з емоційного
                                розвантаження, психологічні консультації
                                та практики ментального здоров'я.
                                Проводимо заняття з йоги,
                                танців та спортивні івенти
                            </p>
                        </div>

                        <div class="offer-item">
                            <img src="/images/cards/patriotic.png" alt="icon">
                            <h5>БУТИ ЧАСТИНОЮ СПІЛЬНОТИ</h5>
                            <p>
                                Безпечний і відкритий простір
                                для навчання, роботи чи спілкування.
                                Ми не лише чекаємо на тебе
                                в Молодіжному хабі в Полтаві,
                                а й регулярно виїжджаємо
                                в громади Полтавської області,
                                щоб бути ближчими до молоді
                            </p>
                        </div>

                    </div>

                </div>

                <p class="offers-footer-note">
                    Команда працює для молоді віком від 14 до 35 років,
                    яка живе, навчається або працює
                    в Полтаві та області.
                </p>

            </div>
        </div>
    </div>
</div>
</section>

@include('team.index', [
    'departments' => $departments,
    'settings' => $teamSettings
])

@include('statuts.status')

@include('reporting.reporting')

@include('calendar_plan.calendar_plan')

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mainCards = document.querySelectorAll('.card');
    const targetCards = document.querySelectorAll('.t-card');
    const flowers = document.querySelectorAll('.icon-flower');
    const bgText1 = document.getElementById('bgText');
    const bgText2 = document.getElementById('bgTextSecondary');
    const accordionButtons = document.querySelectorAll('.accordion-button');

    accordionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const flower = this.querySelector('.icon-flower');
            flower.classList.add('flower-spin');
            setTimeout(() => { if(this.classList.contains('collapsed')) flower.classList.remove('flower-spin'); }, 600);
        });
    });

    window.addEventListener('scroll', () => {
        const scrollPos = window.scrollY;
        flowers.forEach(flower => { if (flower.closest('.accordion-button').classList.contains('collapsed')) flower.style.transform = `rotate(${scrollPos * 0.2}deg)`; });
        if (bgText1) bgText1.style.transform = `translateY(-50%) translateX(-${scrollPos * 0.3}px)`;
        if (bgText2) bgText2.style.transform = `translateY(-50%) translateX(-${scrollPos * 0.3}px)`;
    });

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const card = entry.target;
                const delay = parseFloat(getComputedStyle(card).getPropertyValue('--d')) * 1000 || 0;
                setTimeout(() => card.classList.add('loaded'), delay);
            }
        });
    }, { threshold: 0.1 });

    mainCards.forEach(card => observer.observe(card));
    targetCards.forEach(card => observer.observe(card));

    
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. ФІКС СКРОЛУ: примусово повертаємо на початок при завантаженні
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);

    // 2. СЕЛЕКТОРИ: шукаємо посилання всюди в хедері
    // Якщо у вас посилання мають інший клас, додайте його сюди
    const navLinks = document.querySelectorAll('header a, .nav-link, .nav a');
    const aboutSection = document.getElementById('about-section');

    function setActive(targetHref) {
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            // Перевіряємо точний збіг або закінчення рядка
            if (href === targetHref || (href && href.endsWith(targetHref))) {
                link.classList.add('active');
                // Додаємо стиль безпосередньо, якщо CSS клас не спрацьовує
                link.style.color = '#2e3aa1'; 
                link.style.borderBottom = '2px solid #2e3aa1';
            } else {
                link.classList.remove('active');
                link.style.color = ''; 
                link.style.borderBottom = '';
            }
        });
    }

    function handleScroll() {
        const scrollY = window.scrollY;

        // Якщо ми вгорі сторінки
        if (scrollY < 200) {
            setActive('/');
            return;
        }

        // Якщо докрутили до секції "Про нас"
        if (aboutSection) {
            const rect = aboutSection.getBoundingClientRect();
            // Активуємо, коли заголовок секції піднімається вище середини екрана
            if (rect.top <= 300) {
                setActive('#about-section');
            } else {
                setActive('/');
            }
        }
    }

    // Слухаємо скрол
    window.addEventListener('scroll', handleScroll);
    
    // Викликаємо відразу для ініціалізації
    handleScroll();
});
</script>
</script>
</script>
</body>
</html>