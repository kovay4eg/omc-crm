@php
    $settings = \App\Models\HomepageSetting::first();
@endphp

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
/* === БАЗОВІ СТИЛІ === */
.header{ width:100%; position:fixed; top:0; left:0; z-index:1000; transition:0.3s ease; background: #fff; }
.header.scrolled{ background:rgba(255,255,255,0.8); backdrop-filter:blur(12px); box-shadow:0 5px 20px rgba(0,0,0,0.05); }

.container-1200{ max-width:1200px; margin:0 auto; padding:0 15px; }
.header-inner{ display:flex; align-items:center; justify-content:space-between; padding:18px 0; }

/* ФІКС ЛОГО (Фото 4) */
.logo a { display: block; line-height: 0; }
.logo img { 
    width: 120px; 
    height: auto; 
    display: block; 
    image-rendering: -webkit-optimize-contrast; /* Для чіткості SVG */
}

/* Навігація */
.nav{ display:flex; gap:32px; }
.nav a{ position:relative; text-decoration:none; font-family:'Inter', sans-serif; font-weight:600; font-size:16px; color:#111; transition:0.3s; }
.nav a::after{ content:''; position:absolute; left:0; bottom:-5px; width:0%; height:2px; background:#2b3cff; transition:0.3s; }
.nav a:hover::after, .nav a.active::after{ width:100%; }
.nav a.active{ color:#2b3cff; }

/* Соцмережі та Око */
.socials{ display:flex; gap:14px; align-items: center; }
.socials a{ display: flex; align-items: center; }
.socials img{ width:20px; transition:0.3s ease; }
.header-right-side { display: flex; align-items: center; gap: 20px; }

.eye-btn { background: none; border: none; cursor: pointer; color: #2b3cff; padding: 0; display: flex; align-items: center; }
.eye-btn svg { width: 24px; height: 24px; fill: currentColor; }

.burger{ display:none; cursor:pointer; flex-direction:column; gap:6px; }
.burger span{ width:28px; height:3px; background:#111; transition:0.3s; border-radius:2px; }

/* === ФІКС ТЕКСТУ В ОДНУ ЛІНІЮ (Фото 1) === */
.event-info-item, .info-row, .card-meta-item { 
    display: flex !important; 
    align-items: center !important; 
    gap: 8px !important; 
    flex-wrap: nowrap !important; /* Заборона переносу */
}
.event-info-item span, .info-row span, .card-meta-item span { 
    white-space: nowrap !important; /* Текст не розривається */
}

/* === ФІКС КАРТОК НА МОБІЛЦІ (Фото 2-3) === */
@media (max-width: 768px) {
    /* Приклад селекторів для твоїх карток, якщо вони мають такі класи */
    .events-grid, .results-grid { 
        display: flex; 
        flex-direction: column; 
        gap: 20px; 
    }
    .event-card, .result-card { 
        width: 100% !important; 
        margin: 0 0 20px 0 !important; 
    }
}

/* === ПАНЕЛЬ ДОСТУПНОСТІ === */
.a11y-panel { display: none; width: 100%; background: #fff; border-bottom: 2px solid #000; padding: 15px 0; }
.a11y-panel.active { display: block; }
.a11y-inner { max-width: 1200px; margin: 0 auto; padding: 0 15px; display: flex; align-items: center; justify-content: center; gap: 30px; }

/* === МОБІЛЬНА ВЕРСІЯ ТА БУРГЕР (Фото 5) === */
@media (max-width: 992px) {
    .container-1200 { padding: 0 20px; } /* Відступ від країв екрана */
    
    .burger { display: flex; order: 3; }
    .logo { order: 1; }
    .header-right-side { order: 2; margin-left: auto; }

    .socials.desktop-only { display: none; }

    .nav { 
        position: fixed; top: 0; right: -100%; width: 300px; height: 100vh; 
        background: #fff; flex-direction: column; padding: 100px 30px; 
        transition: 0.4s ease; z-index: 999; box-shadow: -10px 0 30px rgba(0,0,0,0.1); 
    }
    .nav.active { right: 0; }
    
    .mobile-socials { display: flex; gap: 20px; margin-top: auto; padding-top: 20px; border-top: 1px solid #eee; }
    .mobile-socials img { width: 28px; }
}
</style>

<header class="header" id="header">
    <div class="a11y-panel" id="a11yPanel">
        <div class="a11y-inner">
            <button class="a11y-reset" id="a11yReset">Звичайна версія</button>
        </div>
    </div>

    <div class="container-1200 header-inner">
        <div class="logo">
            <a href="/">
                <img src="{{ !empty($settings?->logo) ? asset('storage/' . $settings->logo) : asset('images/logo.png') }}" alt="Logo">
            </a>
        </div>

        <nav class="nav" id="navMenu">
            <a href="/">Головна</a>
            <a href="/#about-section">Про нас</a>
            <a href="/meet">Де ми можемо зустрітися?</a>
            <a href="/events">Анонси заходів</a>
            <a href="/results">Підсумки заходів</a>
            <a href="/contacts">Контакти</a>

            <div class="mobile-socials">
                @if($settings?->facebook_enabled)<a href="{{ $settings->facebook_url }}"><img src="{{ asset('icons/facebook.svg') }}"></a>@endif
                @if($settings?->instagram_enabled)<a href="{{ $settings->instagram_url }}"><img src="{{ asset('icons/instagram.svg') }}"></a>@endif
                @if($settings?->telegram_enabled)<a href="{{ $settings->telegram_url }}"><img src="{{ asset('icons/telegram.svg') }}"></a>@endif
            </div>
        </nav>

        <div class="header-right-side">
            <div class="socials desktop-only">
                @if($settings?->facebook_enabled)<a href="{{ $settings->facebook_url }}"><img src="{{ asset('icons/facebook.svg') }}"></a>@endif
                @if($settings?->instagram_enabled)<a href="{{ $settings->instagram_url }}"><img src="{{ asset('icons/instagram.svg') }}"></a>@endif
                @if($settings?->telegram_enabled)<a href="{{ $settings->telegram_url }}"><img src="{{ asset('icons/telegram.svg') }}"></a>@endif
            </div>

            <button class="eye-btn" id="mainEye">
                <svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
            </button>

            <div class="burger" id="burger">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>
</header>

<script>
const header = document.getElementById('header'), burger = document.getElementById('burger'), nav = document.getElementById('navMenu');

window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 20);
});

burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    nav.classList.toggle('active');
});

// Плавний скрол
document.querySelectorAll('.nav a[href^="/#"]').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').replace('/#', '');
        const target = document.getElementById(targetId);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
            nav.classList.remove('active');
            burger.classList.remove('active');
        }
    });
});
</script>