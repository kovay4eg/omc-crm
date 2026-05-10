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
    position:relative;
}

/* logo */
.logo{
    position:relative;
    z-index:1200;
    flex-shrink:0;
}

.logo img{
    width:auto !important;
    height:auto !important;
    max-width:120px;

    display:block;

    image-rendering:crisp-edges;
    image-rendering:-webkit-optimize-contrast;

    transform:none !important;
    filter:none !important;
    backface-visibility:hidden;
}

/* nav */
.nav{
    display:flex;
    gap:32px;
    align-items: center;
}

/* nav item container for dropdown */
.nav-item {
    position: relative;
    display: flex;
    align-items: center;
}

/* nav links & button */
.nav a, .nav-btn{
    position:relative;
    text-decoration:none;
    font-family:'Inter', sans-serif;
    font-weight:600;
    font-size:16px;
    color:#111;
    transition:0.3s;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* underline animation */
.nav a::after, .nav-btn::after{
    content:'';
    position:absolute;
    left:0;
    bottom:-5px;
    width:0%;
    height:2px;
    background:#2b3cff;
    transition:0.3s;
}

/* hover effects */
.nav a:hover, .nav-btn:hover, .nav-item:hover .nav-btn{
    color:#2b3cff;
}

.nav a:hover::after, .nav-btn:hover::after, .nav-item:hover .nav-btn::after{
    width:100%;
}

/* active link */
.nav a.active{
    color:#2b3cff;
}

.nav a.active::after{
    width:100%;
}

/* --- Dropdown Menu Styles --- */
.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(15px);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    min-width: 220px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-radius: 12px;
    padding: 12px 0;
    opacity: 0;
    visibility: hidden;
    transition: 0.3s ease;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255,255,255,0.4);
    z-index: 1100;
}

.nav-item:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(10px);
}

.dropdown-menu a {
    padding: 10px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #333;
    transition: 0.2s;
}

.dropdown-menu a::after {
    display: none;
}

.dropdown-menu a:hover {
    background: rgba(43, 60, 255, 0.05);
    color: #2b3cff;
    padding-left: 25px;
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
    z-index:1300;
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

    .header-inner{
        padding-left:20px;
        padding-right:20px;
    }

    .burger{
        display:block;
        position:absolute;
        right:25px;
        top:50%;
        transform:translateY(-50%);
    }

    .logo img{
        width:85px !important;
        max-width:85px !important;
        height:auto !important;
        object-fit:contain;
    }

    .nav{
        position:absolute;
        top:95px;
        left:0;
        width:100%;
        background:#fff;
        flex-direction:column;
        align-items:center;
        gap:20px;
        padding:35px 0 30px 0;

        opacity:0;
        transform:translateY(-20px);
        pointer-events:none;

        transition:0.35s ease;

        box-shadow:0 10px 30px rgba(0,0,0,0.08);
        max-height: 80vh;
        overflow-y: auto;
    }

    .nav.active{
        opacity:1;
        transform:translateY(0);
        pointer-events:auto;
    }

    /* Mobile Dropdown Accordion */
    .nav-item {
        flex-direction: column;
        width: 100%;
    }

    .dropdown-menu {
        position: static;
        transform: none;
        opacity: 1;
        visibility: visible;
        display: none;
        width: 100%;
        background: #f9f9f9;
        box-shadow: none;
        border: none;
        border-radius: 0;
        padding: 10px 0;
        margin-top: 10px;
        backdrop-filter: none;
        text-align: center;
    }

    .nav-item.open .dropdown-menu {
        display: flex;
    }

    .dropdown-menu a:hover {
        padding-left: 20px;
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

html{
    scroll-behavior:smooth;
}

</style>

<header class="header" id="header">

    <div class="container-1200 header-inner">

        <div class="logo">
            <a href="/">
                @if(!empty($settings?->logo))
                    <img
                        src="{{ asset('storage/' . $settings->logo) }}"
                        alt="logo"
                        draggable="false"
                    >
                @else
                    <img
                        src="/images/logo.png"
                        alt="logo"
                        draggable="false"
                    >
                @endif
            </a>
        </div>

        <div class="burger" id="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="nav" id="navMenu">

            <a href="/">Головна</a>

            <div class="nav-item" id="aboutDropdown">

                <button class="nav-btn">
                    Про нас

                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div class="dropdown-menu">

                    <a href="#about-section">Про нас</a>

                    <a href="#team-section">Команда</a>

                    <a href="#statut-section">Статут</a>

                    <a href="/reporting">Звітність</a>

                    <a href="/calendar">Календарний план</a>

                </div>

            </div>

            <a href="/meet">Де ми можемо зустрітися?</a>

            <a href="/events">Анонси заходів</a>

            <a href="/results">Підсумки заходів</a>

            <a href="/contacts">Контакти</a>

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
const aboutDropdown = document.getElementById('aboutDropdown');

burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    nav.classList.toggle('active');
});

// Mobile dropdown toggle
aboutDropdown.addEventListener('click', function(e) {

    if(window.innerWidth <= 768) {

        if(e.target.closest('.nav-btn')) {
            e.preventDefault();
            this.classList.toggle('open');
        }

    }

});

window.addEventListener('scroll', () => {

    if(window.scrollY > 20){
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }

});
</script>