<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Головна</title>

<link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

html, body {
    overflow-x: hidden;
}

body {
    background: #f5f5f5;
    font-family: 'Commissioner', sans-serif;
}

/* title block */
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

/* card block */
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

.card:hover,
.card.active {
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
    font-size: 20px;
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

/* ================= ADAPTIV ================= */
@media (max-width: 992px) {

    section {
        padding: 80px 0 !important;
    }

    .container {
        padding-left: 20px;
        padding-right: 20px;
    }

    /* banner */
    section.container img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* title */
    .title-block {
        margin-bottom: 40px;
        text-align: center;
    }

    .title-block h2 {
        font-size: 22px;
    }

    #bgText {
        font-size: 60px;
        top: 60%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    /* cards */
    .cards-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 35px;
        height: auto !important;
    }

    .card {
        position: relative !important;
        left: auto !important;
        top: auto !important;

        width: 270px;
        height: 287px;

        opacity: 0;
    }

    .card:nth-child(1) { transform: rotate(-6deg); }
    .card:nth-child(2) { transform: rotate(6deg); }
    .card:nth-child(3) { transform: rotate(-3deg); }
    .card:nth-child(4) { transform: rotate(5deg); }
    .card:nth-child(5) { transform: rotate(-4deg); }

    .card img {
        width: 70px;
        top: 30px;
    }
}

</style>
</head>

<body>

<section class="container">
    @if($settings && $settings->banner_image)
        <img src="{{ asset('storage/' . $settings->banner_image) }}">
    @endif
</section>

<section style="position:relative;padding:160px 0;overflow:hidden;">

    <div class="container" style="max-width:1200px;position:relative;">

        <div class="title-block">

            <div id="bgText">
                НАПРЯМИ ДІЯЛЬНОСТІ &nbsp;&nbsp;&nbsp; НАПРЯМИ ДІЯЛЬНОСТІ
            </div>

            <h2>
                ГОЛОВНІ НАПРЯМИ ДІЯЛЬНОСТІ:
            </h2>

        </div>

        <div class="cards-wrapper" style="position:relative;height:360px;max-width:1150px;margin:auto;">

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

    card.style.transform = base + ' translateY(60px) scale(0.9)';
    card.style.opacity = "0";

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {

                setTimeout(() => {
                    card.style.transition = "all 0.8s cubic-bezier(0.22,1,0.36,1)";
                    card.style.opacity = "1";
                    card.style.transform = base;
                }, i * 150);

            }
        });
    }, { threshold: 0.3 });

    observer.observe(card);

    const centerObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (window.innerWidth < 992) {
                if (entry.intersectionRatio > 0.6) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            }
        });
    }, { threshold: [0.6] });

    centerObserver.observe(card);

});

</script>

</body>
</html>