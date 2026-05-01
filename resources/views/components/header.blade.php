@php
    $settings = \App\Models\HomepageSetting::first();
@endphp

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>

/* header */
.header{
    width:100%;
    position:fixed;
    top:0;
    left:0;
    z-index:1000;
    transition:0.3s ease;
}

/* glass effect on scroll */
.header.scrolled{
    background:rgba(255,255,255,0.8);
    backdrop-filter:blur(12px);
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

/* container */
.container-1200{
    max-width:1200px;
    margin:0 auto;
    padding:0 15px;
}

/* layout */
.header-inner{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:18px 0;
}

/* logo */
.logo img{
    width:120px;
}

/* nav */
.nav{
    display:flex;
    gap:32px;
}

/* nav links */
.nav a{
    position:relative;
    text-decoration:none;
    font-family:'Inter', sans-serif;
    font-weight:600;
    font-size:16px;
    color:#111;
    transition:0.3s;
}

/* underline */
.nav a::after{
    content:'';
    position:absolute;
    left:0;
    bottom:-5px;
    width:0%;
    height:2px;
    background:#2b3cff;
    transition:0.3s;
}

/* hover */
.nav a:hover{
    color:#2b3cff;
}

.nav a:hover::after{
    width:100%;
}

/* active link */
.nav a.active{
    color:#2b3cff;
}

.nav a.active::after{
    width:100%;
}

/* socials */
.socials{
    display:flex;
    gap:14px;
}

.socials img{
    width:20px;
    transition:0.25s;
}

.socials img:hover{
    transform:translateY(-4px) scale(1.15);
}

/* mobile socials hidden by default */
.mobile-socials{
    display:none;
}

/* burger */
.burger{
    display:none;
    width:28px;
    height:20px;
    position:relative;
    cursor:pointer;
    z-index:1100; /* ✅ FIX */
}

.burger span{
    position:absolute;
    width:100%;
    height:3px;
    background:#000;
    left:0;
    transition:0.3s;
}

.burger span:nth-child(1){ top:0; }
.burger span:nth-child(2){ top:8px; }
.burger span:nth-child(3){ top:16px; }

/* burger active */
.burger.active span:nth-child(1){
    transform:rotate(45deg);
    top:8px;
}

.burger.active span:nth-child(2){
    opacity:0;
}

.burger.active span:nth-child(3){
    transform:rotate(-45deg);
    top:8px;
}

/* mobile */
@media (max-width:768px){

    .burger{
        display:block;
    }

    .nav{
        position:absolute;
        top:70px;
        left:0;
        width:100%;
        background:#fff;
        flex-direction:column;
        align-items:center;
        gap:20px;
        padding:30px 0;

        opacity:0;
        transform:translateY(-20px);
        pointer-events:none;

        transition:0.35s ease;
    }

    .nav.active{
        opacity:1;
        transform:translateY(0);
        pointer-events:auto;
    }

    .socials{
        display:none;
    }

    /* mobile socials */
    .mobile-socials{
        display:flex;
        gap:20px;
        margin-top:15px;
    }

    .mobile-socials img{
        width:24px;
        transition:0.25s;
    }

    .mobile-socials img:hover{
        transform:scale(1.2);
    }
}

</style>

<header class="header" id="header">

    <div class="container-1200 header-inner">

        <!-- logo -->
        <div class="logo">
            <a href="/">
                @if(!empty($settings?->logo))
                    <img src="{{ asset('storage/' . $settings->logo) }}">
                @else
                    <img src="/images/logo.png">
                @endif
            </a>
        </div>

        <!-- burger -->
        <div class="burger" id="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <!-- nav -->
        <nav class="nav" id="navMenu">

            <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Головна</a>
            <a href="/#about-section">Про нас</a>
            <a href="/meet" class="{{ request()->is('meet') ? 'active' : '' }}">Де ми можемо зустрітися?</a>
            <a href="/events" class="{{ request()->is('events') ? 'active' : '' }}">Анонси заходів</a>
            <a href="/results" class="{{ request()->is('results') ? 'active' : '' }}">Підсумки заходів</a>
            <a href="/contacts" class="{{ request()->is('contacts') ? 'active' : '' }}">Контакти</a>

            <div class="mobile-socials">

                @if($settings?->facebook_enabled && $settings?->facebook_url)
                    <a href="{{ $settings->facebook_url }}" target="_blank">
                        <img src="{{ asset('icons/facebook.svg') }}">
                    </a>
                @endif

                @if($settings?->instagram_enabled && $settings?->instagram_url)
                    <a href="{{ $settings->instagram_url }}" target="_blank">
                        <img src="{{ asset('icons/instagram.svg') }}">
                    </a>
                @endif

                @if($settings?->telegram_enabled && $settings?->telegram_url)
                    <a href="{{ $settings->telegram_url }}" target="_blank">
                        <img src="{{ asset('icons/telegram.svg') }}">
                    </a>
                @endif

                @if($settings?->youtube_enabled && $settings?->youtube_url)
                    <a href="{{ $settings->youtube_url }}" target="_blank">
                        <img src="{{ asset('icons/youtube.svg') }}">
                    </a>
                @endif

                @if($settings?->tiktok_enabled && $settings?->tiktok_url)
                    <a href="{{ $settings->tiktok_url }}" target="_blank">
                        <img src="{{ asset('icons/tiktok.svg') }}">
                    </a>
                @endif

            </div>

        </nav>

        <!-- socials desktop -->
        <div class="socials">

            @if($settings?->facebook_enabled && $settings?->facebook_url)
                <a href="{{ $settings->facebook_url }}" target="_blank">
                    <img src="{{ asset('icons/facebook.svg') }}">
                </a>
            @endif

            @if($settings?->instagram_enabled && $settings?->instagram_url)
                <a href="{{ $settings->instagram_url }}" target="_blank">
                    <img src="{{ asset('icons/instagram.svg') }}">
                </a>
            @endif

            @if($settings?->telegram_enabled && $settings?->telegram_url)
                <a href="{{ $settings->telegram_url }}" target="_blank">
                    <img src="{{ asset('icons/telegram.svg') }}">
                </a>
            @endif

            @if($settings?->youtube_enabled && $settings?->youtube_url)
                <a href="{{ $settings->youtube_url }}" target="_blank">
                    <img src="{{ asset('icons/youtube.svg') }}">
                </a>
            @endif

            @if($settings?->tiktok_enabled && $settings?->tiktok_url)
                <a href="{{ $settings->tiktok_url }}" target="_blank">
                    <img src="{{ asset('icons/tiktok.svg') }}">
                </a>
            @endif

        </div>

    </div>

</header>

<script>
const burger = document.getElementById('burger');
const nav = document.getElementById('navMenu');
const header = document.getElementById('header');

burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    nav.classList.toggle('active');
});

window.addEventListener('scroll', () => {
    if(window.scrollY > 20){
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});
</script>