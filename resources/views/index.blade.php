<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $homepageSmmTitle = $settings?->smm_title ?: 'Обласний молодіжний центр Полтавської обласної ради';
        $homepageSmmDescription = $settings?->smm_description ?: 'Молодіжні можливості, події та ініціативи Полтавщини.';
        $homepageSmmImage = $settings?->smm_image ?: $settings?->banner_image ?: $settings?->logo;
    @endphp
    <x-social-meta
        :title="$homepageSmmTitle"
        :description="$homepageSmmDescription"
        :image="$homepageSmmImage"
        :url="url('/')"
    />

    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body {
            width: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        body {
            background: #ffffff;
            font-family: 'Commissioner', sans-serif;
        }

        .container-1200 {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .banner-section img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 20px;
        }

        .content-section {
            position: relative;
            padding: 80px 0;
        }

        .title-block {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 200px;
            margin-bottom: 80px;
        }

        #bgText,
        #bgTextSecondary {
            position: absolute;
            top: 50%;
            left: 0;
            z-index: 0;
            width: 250vw;
            color: #dbe3ff;
            font-size: clamp(80px, 15vw, 180px);
            font-weight: 800;
            opacity: .45;
            white-space: nowrap;
            pointer-events: none;
            transform: translateY(-50%);
            will-change: transform;
        }

        .title-block h2 {
            position: relative;
            z-index: 2;
            margin: 0;
            color: #2e3aa1;
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 800;
            text-transform: uppercase;
        }

        .cards-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 28px 24px;
            border: none;
            border-radius: 28px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .04);
            cursor: pointer;
            opacity: 0;
            transition: opacity 1s ease, transform .4s ease, box-shadow .4s ease;
        }

        @keyframes cardFloat {
            0% {
                transform: translateY(0) rotate(var(--r));
            }

            100% {
                transform: translateY(-15px) rotate(var(--r));
            }
        }

        .card.loaded {
            opacity: 1;
            animation: cardFloat 3s ease-in-out infinite alternate;
        }

        .card:hover {
            z-index: 20;
            animation: none;
            transform: translateY(-20px) scale(1.07) rotate(0deg);
            box-shadow: 0 30px 70px rgba(0, 0, 0, .2);
        }

        .card img {
            position: absolute;
            top: 65px;
            left: 24px;
            width: 80px;
        }

        .card p {
            margin: 0;
            font-weight: 800;
            line-height: 1.2;
        }

        .c1 {
            --r: 6deg;
            --d: 0s;
            background: #c7cbf3;
        }

        .c2 {
            --r: -5deg;
            --d: .3s;
            background: #9fa8f0;
        }

        .c3 {
            --r: 4deg;
            --d: .1s;
            background: #d6d9f7;
        }

        .card-main {
            --r: -4deg;
            --d: .4s;
            background: linear-gradient(135deg, #5b63e6, #4a54d1);
        }

        .card-main p {
            color: #ffffff;
        }

        .c5 {
            --r: 5deg;
            --d: .2s;
            background: #9fa8f0;
        }

        .info-accordion-section {
            padding-bottom: 60px;
            background-color: #ffffff;
        }

        .custom-accordion .accordion-item {
            margin-bottom: 10px;
            border: none;
            border-bottom: 1px solid #f3d9da;
            background: transparent;
        }

        .custom-accordion .accordion-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 25px 0;
            background: none !important;
            box-shadow: none !important;
            color: #2e3aa1;
            font-size: 1.25rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .custom-accordion .accordion-button::after {
            display: none;
        }

        .icon-flower {
            display: inline-block;
            width: 32px;
            height: 32px;
            margin-right: 15px;
            content: url("/images/icons/flower.svg");
            transition: transform .6s cubic-bezier(.34, 1.56, .64, 1);
            will-change: transform;
        }

        .flower-spin {
            transform: rotate(360deg) !important;
        }

        .icon-arrow-custom {
            width: 32px;
            height: 32px;
            content: url("/images/icons/arrow.svg");
            transition: all .4s ease;
        }

        .accordion-button:not(.collapsed) .icon-arrow-custom {
            transform: rotate(90deg);
            filter: invert(18%) sepia(51%) saturate(5436%) hue-rotate(229deg) brightness(91%) contrast(92%);
        }

        .design-intro-text {
            margin-bottom: 40px;
            color: #000000;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        .design-cards-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .design-info-card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 380px;
            padding: 30px;
            border-radius: 24px;
            background: #e8eafb;
        }

        .icon-mic-img {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .design-info-card h4 {
            margin-bottom: 15px;
            color: #000000;
            font-size: 1.2rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .design-info-card p {
            margin: 0;
            color: #333333;
            font-size: .95rem;
            line-height: 1.4;
        }

        .design-legal-block {
            margin-top: 20px;
            padding: 40px;
            border-radius: 24px;
            background: #e8eafb;
        }

        .design-legal-block h4 {
            margin-bottom: 25px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .design-legal-block ul {
            padding: 0;
            list-style: none;
        }

        .design-legal-block ul li {
            position: relative;
            margin-bottom: 8px;
            padding-left: 25px;
            font-size: .95rem;
            line-height: 1.4;
        }

        .design-legal-block ul li::before {
            position: absolute;
            left: 0;
            color: #2e3aa1;
            content: "◆";
            font-size: 1rem;
        }

        .target-intro-text {
            margin-bottom: 30px;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.4;
            text-align: center;
            text-transform: uppercase;
        }

        .target-cards-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            padding: 20px 0 40px;
            perspective: 1000px;
        }

        .t-card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            box-sizing: border-box;
            width: 220px;
            height: 280px;
            padding: 25px 20px;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 1s ease, transform .4s ease, box-shadow .4s ease;
        }

        .t-card.loaded {
            opacity: 1;
            animation: cardFloat 3.5s ease-in-out infinite alternate;
        }

        .t-card img {
            position: absolute;
            top: 25px;
            left: 20px;
            width: 60px;
            height: auto;
        }

        .t-card p {
            margin: 0;
            color: #000000;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .t-card:hover {
            z-index: 10;
            animation: none !important;
            transform: translateY(-15px) scale(1.05) rotate(0deg) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, .12);
            transition: all .3s ease !important;
        }

        .t-card:hover img {
            transform: scale(1.1);
            transition: transform .3s ease;
        }

        .tc-1 {
            --r: -3deg;
            --d: .1s;
            background: #b1b8e8;
        }

        .tc-2 {
            --r: 2deg;
            --d: .2s;
            margin-top: 15px;
            background: #919ce1;
        }

        .tc-3 {
            --r: -2deg;
            --d: .3s;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        }

        .tc-4 {
            --r: 3deg;
            --d: .4s;
            margin-top: 15px;
            background: #5a66d6;
        }

        .tc-4 p {
            color: #ffffff;
        }

        .tc-5 {
            --r: -2deg;
            --d: .5s;
            background: #b1b8e8;
        }

        .offers-wrapper {
            padding: 40px 0;
            text-align: center;
        }

        .offers-intro-text {
            margin-bottom: 50px;
            font-size: 1.1rem;
            text-transform: uppercase;
        }

        .offers-grid-container {
            display: flex;
            flex-direction: column;
            gap: 60px;
            padding: 60px 0;
            border-top: 1px dashed #dbe3ff;
            border-bottom: 1px dashed #dbe3ff;
        }

        .offers-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
        }

        .offer-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 300px;
        }

        .offer-item img {
            width: 120px;
            height: auto;
            margin-bottom: 25px;
        }

        .offer-item h5 {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            margin-bottom: 15px;
            color: #000000;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .offer-item p {
            margin: 0;
            color: #333333;
            font-size: .95rem;
            line-height: 1.4;
        }

        .offers-footer-note {
            margin-top: 50px;
            font-size: 1rem;
            font-weight: 700;
        }

        .desktop-title {
            display: inline;
        }

        .mobile-title {
            display: none;
            flex-direction: column;
            line-height: 1.05;
        }

        .mobile-title span {
            display: block;
        }

        .acc-btn-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .accordion-title {
            display: flex;
            flex-direction: column;
        }

        .about-toggle-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 50px;
            margin-bottom: 80px;
        }

        .about-toggle-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px 40px;
            border: none;
            border-radius: 60px;
            background: #2e3aa1;
            color: #ffffff;
            cursor: pointer;
            font-size: 18px;
            font-weight: 800;
            transition: all .3s ease;
        }

        .about-toggle-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(46, 58, 161, .25);
        }

        .about-toggle-arrow {
            font-size: 22px;
            transition: transform .3s ease;
        }

        .about-toggle-btn.active .about-toggle-arrow {
            transform: rotate(180deg);
        }

        .about-more-wrapper {
            max-height: 0;
            overflow: hidden;
            transition: max-height .8s ease;
        }

        .about-more-wrapper.open {
            max-height: 30000px;
            overflow: visible;
        }

        @media (max-width: 1199px) {
            .cards-wrapper {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 30px;
                padding-top: 20px;
            }

            .card {
                position: relative;
                width: 275px;
                height: 320px;
            }
        }

        @media (min-width: 1200px) {
            .cards-wrapper {
                height: 480px;
            }

            .card {
                position: absolute;
                width: 275px;
                height: 320px;
            }

            .card.c1 {
                top: 40px;
                left: 0;
            }

            .card.c2 {
                top: 130px;
                left: 230px;
            }

            .card.c3 {
                top: 30px;
                left: 460px;
            }

            .card.card-main {
                top: 110px;
                left: 690px;
            }

            .card.c5 {
                top: 60px;
                left: 925px;
            }
        }

        @media (max-width: 991px) {
            .design-cards-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .offer-item {
                width: 40%;
            }
        }

        @media (max-width: 767px) {
            .desktop-title {
                display: none;
            }

            .mobile-title {
                display: flex;
            }

            .t-card {
                position: relative;
                width: 50% !important;
                min-height: 220px;
                height: auto !important;
                padding: 10px;
            }

            .t-card img {
                top: 30px !important;
                left: 20px !important;
                width: 60px !important;
            }

            .t-card h5 {
                margin-top: 90px;
                font-size: 15px;
                line-height: 1.1;
            }

            .tc-1 {
                transform: rotate(-7deg) !important;
            }

            .tc-2 {
                transform: rotate(6deg) !important;
            }

            .tc-3 {
                transform: rotate(-5deg) !important;
            }

            .tc-4 {
                transform: rotate(7deg) !important;
            }

            .tc-5 {
                transform: rotate(-6deg) !important;
            }

            .about-toggle-btn {
                width: 100%;
                justify-content: center;
                padding: 16px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
@php
    $settings = \App\Models\HomepageSetting::first();
@endphp

<x-preloader />

<div style="height:195px;"></div>

<x-header />

<div class="container-1200">
    <section class="banner-section">
        <picture>
            @if ($settings && $settings->mobile_banner_image)
                <source media="(max-width: 768px)" srcset="{{ asset('storage/' . $settings->mobile_banner_image) }}">
            @endif

            @if ($settings && $settings->banner_image)
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
                        <span class="acc-btn-left">
                            <i class="icon-flower"></i>
                            ЩО ТАКЕ ПОМЦ?
                        </span>

                        <i class="icon-arrow-custom"></i>
                    </button>
                </h2>

                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#mainAccordion">
                    <div class="accordion-body px-0">
                        <p class="design-intro-text">
                            <strong>Полтавський обласний молодіжний центр (ПОМЦ)</strong> — це динамічна платформа для розвитку, творчості та професійного зростання молоді нашого регіону. Ми віримо, що молодь — це активне сьогодення Полтавщини.
                        </p>

                        <div class="design-cards-row">
                            <div class="design-info-card">
                                <img src="/icons/microphone.svg" class="icon-mic-img">
                                <h4>НАША МІСІЯ</h4>
                                <p>Ми формуємо безпечне та відкрите середовище, де кожна молода людина має голос, підтримку та реальний шанс діяти.</p>
                            </div>

                            <div class="design-info-card">
                                <img src="/icons/microphone.svg" class="icon-mic-img">
                                <h4>ОФІЦІЙНА ІНФОРМАЦІЯ</h4>
                                <p>Установа забезпечує реалізацію молодіжної політики в регіоні через неформальну освіту та волонтерство.</p>
                            </div>

                            <div class="design-info-card">
                                <img src="/icons/microphone.svg" class="icon-mic-img">
                                <h4>УПРАВЛІННЯ</h4>
                                <p>Установа перебуває у підпорядкуванні Управління молоді та спорту Полтавської ОДА.</p>
                            </div>
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
                        <span class="acc-btn-left">
                            <i class="icon-flower"></i>
                            ДЛЯ КОГО МИ?
                        </span>

                        <i class="icon-arrow-custom"></i>
                    </button>
                </h2>

                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#mainAccordion">
                    <div class="accordion-body px-0">
                        <p class="target-intro-text">ПОМЦ – ДЛЯ ТЕБЕ, ЯКЩО ТИ:</p>

                        <div class="target-cards-container">
                            <div class="t-card tc-1">
                                <img src="/images/cards/education.png">
                                <p>Шукаєш місце, де цікаво вчитись новому</p>
                            </div>

                            <div class="t-card tc-2">
                                <img src="/images/cards/lightbulb.png">
                                <p>Маєш власні ідеї та хочеш їх реалізувати</p>
                            </div>

                            <div class="t-card tc-3">
                                <img src="/images/cards/community.png">
                                <p>Прагнеш нових знайомств чи шукаєш свою спільноту</p>
                            </div>

                            <div class="t-card tc-4">
                                <img src="/images/cards/health.png">
                                <p>Хочеш реально впливати на життя своєї громади</p>
                            </div>

                            <div class="t-card tc-5">
                                <img src="/images/cards/patriotic.png">
                                <p>Цікавишся можливостями навчання та проєктів</p>
                            </div>
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
                                        <p>Проводимо тренінги з лідерства, проєктного менеджменту та комунікацій</p>
                                    </div>

                                    <div class="offer-item">
                                        <img src="/images/cards/patriotic.png" alt="icon">
                                        <h5>РЕАЛІЗОВУВАТИ СВОЇ ІДЕЇ</h5>
                                        <p>Допомагаємо перетворити твої задуми на реальні проєкти</p>
                                    </div>

                                    <div class="offer-item">
                                        <img src="/images/cards/patriotic.png" alt="icon">
                                        <h5>ЗНАХОДИТИ НОВІ МОЖЛИВОСТІ</h5>
                                        <p>
                                            Участь у всеукраїнських форумах, нові знайомства та корисні контакти.
                                            Розвиток кар'єрних можливостей у форматі зустрічей TEDx та Speed Friending
                                            з успішними підприємцями.
                                        </p>
                                    </div>
                                </div>

                                <div class="offers-row bottom-row">
                                    <div class="offer-item">
                                        <img src="/images/cards/patriotic.png" alt="icon">
                                        <h5>РОЗВИВАТИСЯ ТВОРЧО, МЕНТАЛЬНО І ФІЗИЧНО</h5>
                                        <p>
                                            Організовуємо заходи з емоційного розвантаження, психологічні консультації
                                            та практики ментального здоров'я. Проводимо заняття з йоги,
                                            танців та спортивні івенти.
                                        </p>
                                    </div>

                                    <div class="offer-item">
                                        <img src="/images/cards/patriotic.png" alt="icon">
                                        <h5>БУТИ ЧАСТИНОЮ СПІЛЬНОТИ</h5>
                                        <p>
                                            Безпечний і відкритий простір для навчання, роботи чи спілкування.
                                            Ми не лише чекаємо на тебе в Молодіжному хабі в Полтаві,
                                            а й регулярно виїжджаємо в громади Полтавської області,
                                            щоб бути ближчими до молоді.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <p class="offers-footer-note">
                                Команда працює для молоді віком від 14 до 35 років,
                                яка живе, навчається або працює в Полтаві та області.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="about-toggle-wrapper">
    <button id="toggleAboutMore" class="about-toggle-btn">
        <span class="about-toggle-text">ПОКАЗАТИ БІЛЬШЕ</span>
        <span class="about-toggle-arrow">↓</span>
    </button>
</div>

<div id="aboutMoreWrapper" class="about-more-wrapper">
    @include('team.index', [
        'departments' => $departments,
        'settings' => $teamSettings
    ])

    @include('statuts.status')
    @include('reporting.reporting')
    @include('calendar_plan.calendar_plan')
</div>

@include('structure.structure')

@include('events.index', ['events' => $events])

@include('event_summaries.index', ['eventSummaries' => $eventSummaries])

@include('contacts.index', ['settings' => $settings])

<x-footer />

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mainCards = document.querySelectorAll('.card');
    const targetCards = document.querySelectorAll('.t-card');
    const flowers = document.querySelectorAll('.icon-flower');
    const bgText1 = document.getElementById('bgText');
    const bgText2 = document.getElementById('bgTextSecondary');
    const accordionButtons = document.querySelectorAll('.accordion-button');
    const toggleBtn = document.getElementById('toggleAboutMore');
    const wrapper = document.getElementById('aboutMoreWrapper');
    const header = document.getElementById('header');

    const hiddenSections = [
        '#team-section',
        '#statut-section',
        '#reports-section',
        '#calendar-plan-section',
        '#structure-section'
    ];

    const navigationItems = {
        home: document.querySelector('#navMenu > a[href="/"]'),
        about: document.querySelector('#aboutDropdown .nav-btn'),
        structure: document.querySelector('#navMenu > a[href="#structure-section"]'),
        events: document.querySelector('#navMenu > a[href="#events-section"]'),
        results: document.querySelector('#navMenu > a[href="#event-summaries-section"]'),
        contacts: document.querySelector('#navMenu > a[href="#contacts-section"]')
    };

    const navigationSections = [
        { selector: '#about-section', menu: 'about' },
        { selector: '#team-section', menu: 'about' },
        { selector: '#statut-section', menu: 'about' },
        { selector: '#reports-section', menu: 'about' },
        { selector: '#calendar-plan-section', menu: 'about' },
        { selector: '#structure-section', menu: 'structure' },
        { selector: '#events-section', menu: 'events' },
        { selector: '#event-summaries-section', menu: 'results' },
        { selector: '#contacts-section', menu: 'contacts' }
    ];

    accordionButtons.forEach(button => {
        button.addEventListener('click', function () {
            const flower = this.querySelector('.icon-flower');

            if (!flower) {
                return;
            }

            flower.classList.add('flower-spin');

            setTimeout(() => {
                if (this.classList.contains('collapsed')) {
                    flower.classList.remove('flower-spin');
                }
            }, 600);
        });
    });

    window.addEventListener(
        'scroll',
        () => {
            const scrollPosition = window.scrollY;

            flowers.forEach(flower => {
                const parentButton = flower.closest('.accordion-button');

                if (
                    parentButton
                    && parentButton.classList.contains('collapsed')
                ) {
                    flower.style.transform =
                        `rotate(${scrollPosition * .2}deg)`;
                }
            });

            if (bgText1) {
                bgText1.style.transform =
                    `translateY(-50%) translateX(-${scrollPosition * .3}px)`;
            }

            if (bgText2) {
                bgText2.style.transform =
                    `translateY(-50%) translateX(-${scrollPosition * .3}px)`;
            }
        },
        { passive: true }
    );

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) {
                return;
            }

            const card = entry.target;

            const delay =
                parseFloat(
                    getComputedStyle(card)
                        .getPropertyValue('--d')
                ) * 1000 || 0;

            setTimeout(() => {
                card.classList.add('loaded');
            }, delay);
        });
    }, {
        threshold: .1
    });

    mainCards.forEach(card => observer.observe(card));
    targetCards.forEach(card => observer.observe(card));

    function openAboutSections() {
        if (!wrapper || !toggleBtn) {
            return;
        }

        wrapper.classList.add('open');
        toggleBtn.classList.add('active');

        const text = toggleBtn.querySelector('.about-toggle-text');

        if (text) {
            text.textContent = 'ПРИХОВАТИ';
        }
    }

    function closeAboutSections() {
        if (!wrapper || !toggleBtn) {
            return;
        }

        wrapper.classList.remove('open');
        toggleBtn.classList.remove('active');

        const text = toggleBtn.querySelector('.about-toggle-text');

        if (text) {
            text.textContent = 'ПОКАЗАТИ БІЛЬШЕ';
        }
    }

    if (toggleBtn && wrapper) {
        toggleBtn.addEventListener('click', () => {
            if (wrapper.classList.contains('open')) {
                closeAboutSections();
            } else {
                openAboutSections();
            }
        });
    }

    if (hiddenSections.includes(window.location.hash)) {
        openAboutSections();

        setTimeout(() => {
            const element = document.querySelector(window.location.hash);

            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 700);
    }

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    window.scrollTo(0, 0);

    function setActiveMenu(menuName) {
        Object.values(navigationItems).forEach(item => {
            if (item) {
                item.classList.remove('active');
            }
        });

        if (navigationItems[menuName]) {
            navigationItems[menuName].classList.add('active');
        }
    }

    function handleScroll() {
        const activationPoint = (header ? header.offsetHeight : 0) + 30;
        let activeMenu = null;

        navigationSections.forEach(section => {
            const element = document.querySelector(section.selector);

            if (!element) {
                return;
            }

            const position = element.getBoundingClientRect();

            if (
                position.top <= activationPoint
                && position.bottom > activationPoint
            ) {
                activeMenu = section.menu;
            }
        });

        if (activeMenu) {
            setActiveMenu(activeMenu);

            return;
        }

        if (window.scrollY < 200) {
            setActiveMenu('home');
        }
    }

    const hiddenMenuLinks = document.querySelectorAll(
        '.dropdown-menu a[href^="#"]'
    );

    hiddenMenuLinks.forEach(link => {
        link.addEventListener('click', event => {
            const target = link.getAttribute('href');

            if (!hiddenSections.includes(target)) {
                return;
            }

            event.preventDefault();

            openAboutSections();

            setTimeout(() => {
                const targetElement = document.querySelector(target);

                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    history.replaceState(
                        null,
                        null,
                        window.location.pathname
                    );
                }
            }, 180);
        });
    });

    window.addEventListener(
        'scroll',
        handleScroll,
        { passive: true }
    );

    window.addEventListener(
        'resize',
        handleScroll
    );

    handleScroll();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
