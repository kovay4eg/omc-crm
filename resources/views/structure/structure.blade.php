{{-- Головний контейнер секції "Де ми можемо зустрітися?" --}}
<section
    id="structure-section"
    class="meeting-section position-relative py-5 overflow-hidden"
>
    <div class="container position-relative z-3">
        {{-- Заголовок --}}
        <div class="title-container position-relative mb-4 py-2 d-flex justify-content-between align-items-center">
            {{-- Фоновий рухомий текст --}}
            <div class="bg-watermark-wrapper d-none d-lg-block" aria-hidden="true">
                <div id="bgWatermark" class="bg-watermark-track text-uppercase">
                    <span class="bg-watermark-text">ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ</span>
                    <span class="bg-watermark-text">ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ</span>
                    <span class="bg-watermark-text">ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ</span>
                </div>
            </div>

            <h2 class="main-title text-uppercase fw-extrabold m-0 position-relative z-2">
                Де ми можемо <span class="text-blue">зустрітися?</span>
            </h2>

            <div id="mainFlower" class="interactive-flower">
                <img src="/images/flower.svg" alt="Декоративна квітка" width="75" height="75">
            </div>
        </div>

        {{-- Сітка карток --}}
        <div id="mainGridView" class="row g-4 mb-5">
            {{-- Картка 01 --}}
            <div class="col-12 col-md-4">
                <div class="meeting-card h-100" onclick="switchView('hub-view', 'МОЛОДІЖНИЙ ХАБ', this)">
                    <div class="card-img-box">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Молодіжний хаб">
                    </div>

                    <div class="card-content d-flex justify-content-between align-items-end">
                        <div>
                            <div class="card-num">01</div>
                            <h4 class="card-title">МОЛОДІЖНИЙ ХАБ</h4>
                        </div>

                        <div class="arrow-container">
                            <div class="arrow-mask-icon"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Картка 02 --}}
            <div class="col-12 col-md-4">
                <div class="meeting-card h-100" onclick="switchView('studio-view', 'КОНТЕНТА', this)">
                    <div class="card-img-box">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Аудіовізуальна студія">
                    </div>

                    <div class="card-content d-flex justify-content-between align-items-end">
                        <div>
                            <div class="card-num">02</div>
                            <h4 class="card-title">АУДІОВІЗУАЛЬНА СТУДІЯ «КОНТЕНТА»</h4>
                        </div>

                        <div class="arrow-container">
                            <div class="arrow-mask-icon"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Картка 03 --}}
            <div class="col-12 col-md-4">
                <div class="meeting-card h-100" onclick="switchView('mobile-view', 'МОБІЛЬНА РОБОТА', this)">
                    <div class="card-img-box">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Мобільна молодіжна робота">
                    </div>

                    <div class="card-content d-flex justify-content-between align-items-end">
                        <div>
                            <div class="card-num">03</div>
                            <h4 class="card-title">МОБІЛЬНА МОЛОДІЖНА РОБОТА</h4>
                        </div>

                        <div class="arrow-container">
                            <div class="arrow-mask-icon"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Контент 01: Хаб --}}
        <div id="hub-view" class="view-panel d-none">
            <div class="panel-inner-content pt-2 pb-5">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="main-title text-uppercase fw-extrabold text-blue">Молодіжний хаб</h2>
                    <button class="btn-close-view" onclick="resetView()"></button>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-lg-10">
                        <p class="lead-text mb-3">
                            <strong>Молодіжний хаб</strong> – це сучасний мультифункціональний простір площею 549,2 кв.м, створений для всебічного розвитку, змістовного дозвілля та соціалізації молоді.
                        </p>

                        <p class="sub-text text-muted">
                            Приміщення Хабу відремонтовано та облаштовано в рамках проєкту ГО «NOVA UNITED» за фінансової підтримки Дитячого фонду ООН (ЮНІСЕФ) та уряду Японії.
                        </p>
                    </div>
                </div>

                <div class="photo-grid mb-5">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-8">
                            <div class="red-block" style="height: 420px;"></div>
                        </div>

                        <div class="col-12 col-md-4 d-flex flex-column gap-3">
                            <div class="red-block flex-grow-1" style="min-height: 200px;"></div>
                            <div class="red-block flex-grow-1" style="min-height: 200px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Контент 02: Контента --}}
        <div id="studio-view" class="view-panel d-none">
            <div class="panel-inner-content pt-2 pb-5">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="main-title text-uppercase fw-extrabold text-blue">Аудіовізуальна студія «КОНТЕНТА»</h2>
                    <button class="btn-close-view" onclick="resetView()"></button>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-lg-10">
                        <p class="lead-text mb-3">
                            <strong>Аудіовізуальна студія «Контента»</strong> – це сучасна медіа-лабораторія, розташована в самому серці нашого центру. Простір створений для креаторів, подкастерів та відеоконтенту.
                        </p>
                    </div>
                </div>

                <div class="photo-grid mb-5">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 300px;"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Контент 03: Мобільна робота --}}
        <div id="mobile-view" class="view-panel d-none">
            <div class="panel-inner-content pt-2 pb-5">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="main-title text-uppercase fw-extrabold text-blue">Мобільна молодіжна робота</h2>
                    <button class="btn-close-view" onclick="resetView()"></button>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-lg-10">
                        <p class="lead-text mb-3">
                            Команда Полтавського обласного молодіжного центру активно впроваджує напрямок мобільної молодіжної роботи – формат для роботи ОТГ та виїздів.
                        </p>
                    </div>
                </div>

                <div class="photo-grid mb-5">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 240px;"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="red-block" style="height: 240px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Нижня лінія бронювання --}}
        <div class="booking-line d-flex justify-content-between align-items-center py-4 border-top border-bottom mt-4">
            <div class="d-flex align-items-center gap-3">
                <div class="star-icon">✦</div>
                <span class="booking-text text-uppercase fw-bold">Можливість забронювати простір</span>
            </div>

            <a href="#" class="arrow-link">
                <div class="arrow-mask-icon static-arrow"></div>
            </a>
        </div>
    </div>
</section>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&display=swap');

    .meeting-section {
        font-family: 'Montserrat', sans-serif;
    }

    .text-blue {
        color: #2B24C1 !important;
    }

    .fw-extrabold {
        font-weight: 900;
    }

    .title-container {
        width: 100%;
        min-height: 58px;
        margin-bottom: 3.5rem !important;
    }

    .bg-watermark-wrapper {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100vw;
        overflow: hidden;
        pointer-events: none;
        transform: translate(-50%, -50%);
        z-index: 1;
    }

    .bg-watermark-track {
        display: flex;
        width: max-content;
        will-change: transform;
    }

    .bg-watermark-text {
        display: block;
        flex: 0 0 auto;
        padding-right: 2rem;
        font-size: 8vw;
        font-weight: 900;
        line-height: 1;
        color: #2B24C1;
        opacity: .06;
        white-space: nowrap;
        letter-spacing: 2px;
    }

    .title-container .main-title {
        color: #2B24C1;
    }

    .interactive-flower {
        display: none;
    }

    .interactive-flower img {
        will-change: transform;
        transition: transform .1s linear;
    }

    .arrow-mask-icon {
        width: 28px;
        height: 28px;
        background-color: #2B24C1;
        -webkit-mask: url('/images/arrow-right.svg') center / contain no-repeat;
        mask: url('/images/arrow-right.svg') center / contain no-repeat;
        transition: transform .3s cubic-bezier(.25, 1, .5, 1), background-color .3s ease;
        will-change: transform;
    }

    .arrow-mask-icon.static-arrow {
        width: 42px;
        height: 42px;
    }

    .meeting-card {
        overflow: hidden;
        background-color: #EAE5F3;
        border-radius: 18px;
        cursor: pointer;
        transition: transform .3s ease, background-color .3s ease, box-shadow .3s ease;
    }

    .meeting-card:hover:not(.card-active) {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(43, 36, 193, .08);
    }

    .meeting-card.card-active {
        background-color: #2B24C1 !important;
    }

    .meeting-card.card-active .card-title {
        color: #fff !important;
    }

    .meeting-card.card-active .card-num {
        -webkit-text-stroke: 1.5px #fff !important;
    }

    .meeting-card.card-active .arrow-mask-icon {
        transform: rotate(90deg) !important;
        background-color: #fff !important;
    }

    .meeting-card:hover:not(.card-active) .arrow-mask-icon {
        transform: translateX(6px);
    }

    .meeting-card .arrow-container {
        display: none;
    }

    .card-img-box img {
        display: block;
        width: 100%;
        height: 170px;
        object-fit: cover;
    }

    .card-content {
        min-height: 205px;
        padding: 1.5rem;
    }

    .card-num {
        margin-bottom: .3rem;
        font-size: 2.6rem;
        font-weight: 900;
        line-height: 1;
        color: transparent;
        -webkit-text-stroke: 1.5px #2B24C1;
    }

    .card-title {
        margin: 0;
        font-size: .82rem;
        font-weight: 800;
        color: #000;
    }

    .view-panel {
        opacity: 0;
        transform: translateY(15px);
        transition: opacity .4s ease, transform .4s ease;
    }

    .view-panel.active {
        margin-top: 40px;
        opacity: 1;
        transform: translateY(0);
    }

    .view-panel.d-none {
        display: none !important;
    }

    .red-block {
        width: 100%;
        background-color: #EAE5F3;
        border: 2px dashed rgba(43, 36, 193, .15);
        border-radius: 24px;
    }

    .btn-close-view {
        width: 28px;
        height: 28px;
        border: none;
        background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232B24C1'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 1 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center / contain no-repeat;
        cursor: pointer;
        transition: transform .3s ease;
    }

    .btn-close-view:hover {
        transform: rotate(90deg);
    }

    .booking-line {
        margin-top: 30px !important;
        border-top: 1px solid rgba(43, 36, 193, .12) !important;
        border-bottom: 1px solid rgba(43, 36, 193, .12) !important;
    }

    .booking-line:hover .static-arrow {
        transform: translateX(12px);
    }

    .star-icon {
        color: #2B24C1;
        font-size: 1.5rem;
    }

    .booking-text {
        color: #2B24C1;
    }

    .main-title {
        font-size: 34px;
        line-height: 1.05;
    }

    @media (max-width: 991.98px) {
        .title-container {
            margin-bottom: 2.5rem !important;
        }

        .main-title {
            font-size: 28px;
        }

        .card-content {
            min-height: auto;
        }
    }
</style>

<script>
    function updateWatermarkPosition() {
        const watermark = document.getElementById('bgWatermark');
        const firstText = watermark?.querySelector('.bg-watermark-text');

        if (!watermark || !firstText) {
            return;
        }

        const textWidth = firstText.offsetWidth;

        if (!textWidth) {
            return;
        }

        const scrollOffset = (window.scrollY * 0.25) % textWidth;

        watermark.style.transform = `translateX(${-textWidth - scrollOffset}px)`;
    }

    function setWatermarkText(text) {
        const watermarkTexts = document.querySelectorAll('.bg-watermark-text');

        watermarkTexts.forEach(watermarkText => {
            watermarkText.innerText = text;
        });

        requestAnimationFrame(updateWatermarkPosition);
    }

    function switchView(targetId, watermarkText, cardElement) {
        const targetView = document.getElementById(targetId);
        const cards = document.querySelectorAll('.meeting-card');
        const panels = document.querySelectorAll('.view-panel');

        if (!targetView) {
            return;
        }

        if (cardElement.classList.contains('card-active')) {
            resetView();

            return;
        }

        cards.forEach(card => card.classList.remove('card-active'));

        panels.forEach(panel => {
            panel.classList.remove('active');
            panel.classList.add('d-none');
        });

        cardElement.classList.add('card-active');
        targetView.classList.remove('d-none');

        requestAnimationFrame(() => {
            targetView.classList.add('active');
        });

        if (watermarkText) {
            setWatermarkText(watermarkText);
        }
    }

    function resetView() {
        const panels = document.querySelectorAll('.view-panel');
        const cards = document.querySelectorAll('.meeting-card');

        panels.forEach(panel => {
            panel.classList.remove('active');
            panel.classList.add('d-none');
        });

        cards.forEach(card => card.classList.remove('card-active'));

        setWatermarkText('ДЕ МИ МОЖЕМО ЗУСТРІТИСЯ');
    }

    window.addEventListener(
        'scroll',
        updateWatermarkPosition,
        { passive: true }
    );

    window.addEventListener(
        'resize',
        updateWatermarkPosition
    );

    document.addEventListener(
        'DOMContentLoaded',
        () => {
            updateWatermarkPosition();
        }
    );
</script>