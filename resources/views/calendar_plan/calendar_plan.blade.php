@if (request()->routeIs('calendar-plan'))
    <x-accessibility-controls />
@endif

<section id="calendar-plan-section" class="calendar-plan-section">

<style>

    :root {
        --calendar-primary-blue: #131DA4;
        --calendar-bg-light-blue: #E8EAFB;
    }

    .calendar-plan-section {
        font-family: 'Commissioner', sans-serif;
        background: #ffffff;
        padding: 120px 0 80px;
        overflow: hidden;
        position: relative;
    }

    /*
    |--------------------------------------------------------------------------
    | HERO
    |--------------------------------------------------------------------------
    */

    .calendar-hero {
        position: relative;
        min-height: 250px;
        display: flex;
        align-items: center;
        margin-bottom: 60px;
    }

    .calendar-bg-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        font-size: clamp(64px, 11vw, 160px);
        font-weight: 800;

        color: #eef0ff;

        opacity: 1;

        letter-spacing: -.05em;

        white-space: nowrap;

        pointer-events: none;

        z-index: 0;

        will-change: transform;

        width: max-content;
    }

    .calendar-title {
        position: relative;
        z-index: 2;

        font-size: clamp(32px, 5vw, 64px);
        font-weight: 900;

        color: var(--calendar-primary-blue);

        text-transform: uppercase;

        margin: 0;

        opacity: 0;
        transform: translateY(30px);

        animation: revealCalendarTitle 0.8s cubic-bezier(0.2, 1, 0.3, 1) forwards;
    }

    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */

    .calendar-card-link {
        text-decoration: none;
        display: block;
        perspective: 1000px;
    }

    .calendar-card {
        background: #F0F2FF;

        border-radius: 24px;

        height: 180px;

        display: flex;
        align-items: center;
        justify-content: center;

        position: relative;
        overflow: hidden;

        border: 1px solid rgba(19, 29, 164, 0.06);

        transition:
            transform 0.45s ease,
            background 0.45s ease,
            box-shadow 0.45s ease;

        opacity: 0;
        transform: scale(0.92);

        animation: revealCalendarCard 0.6s forwards;
    }

    .calendar-card::before {
        content: '';

        position: absolute;
        inset: 0;

        background:
            linear-gradient(
                135deg,
                rgba(19, 29, 164, 0.04),
                rgba(19, 29, 164, 0)
            );

        z-index: 1;
    }

    .calendar-card::after {
        content: '';

        position: absolute;

        top: -50%;
        left: -50%;

        width: 200%;
        height: 200%;

        background:
            radial-gradient(
                circle,
                rgba(255,255,255,0.25) 0%,
                transparent 70%
            );

        transform: scale(0);

        transition: transform 0.6s ease;
    }

    .calendar-card:hover {
        transform:
            translateY(-10px)
            scale(1.03);

        background: var(--calendar-primary-blue);

        box-shadow:
            0 20px 40px rgba(19, 29, 164, 0.22);
    }

    .calendar-card:hover::after {
        transform: scale(1);
    }

    .calendar-card-content {
        position: relative;
        z-index: 5;

        text-align: center;
    }

    .calendar-year {
        display: block;

        font-size: 38px;
        font-weight: 800;

        color: #1c1c1c;

        transition: 0.35s ease;
    }

    .calendar-label {
        margin-top: 10px;

        display: block;

        font-size: 15px;
        font-weight: 500;

        color: #6B6B6B;

        transition: 0.35s ease;
    }

    .calendar-card:hover .calendar-year,
    .calendar-card:hover .calendar-label {
        color: #ffffff;
    }

    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */

    .calendar-empty {
        background: #F5F6FF;

        border-radius: 28px;

        padding: 70px 30px;

        text-align: center;
    }

    .calendar-empty svg {
        width: 70px;
        height: 70px;

        color: var(--calendar-primary-blue);

        opacity: 0.5;

        margin-bottom: 20px;
    }

    .calendar-empty-title {
        font-size: 32px;
        font-weight: 800;

        color: var(--calendar-primary-blue);

        margin-bottom: 12px;
    }

    .calendar-empty-text {
        font-size: 18px;
        color: #808080;
    }

    /*
    |--------------------------------------------------------------------------
    | ANIMATIONS
    |--------------------------------------------------------------------------
    */

    @keyframes revealCalendarTitle {

        to {
            opacity: 1;
            transform: translateY(0);
        }

    }

    @keyframes revealCalendarCard {

        to {
            opacity: 1;
            transform: scale(1);
        }

    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 768px) {

        .calendar-plan-section {
            padding: 80px 0 40px;
        }

        .calendar-hero {
            min-height: 160px;
            margin-bottom: 30px;
        }

        .calendar-card {
            height: 130px;
            border-radius: 18px;
        }

        .calendar-year {
            font-size: 28px;
        }

        .calendar-label {
            font-size: 13px;
        }

    }

</style>

<div class="calendar-hero">

    <div class="container">

        <div class="calendar-bg-text" id="calendarBg">
            КАЛЕНДАРНИЙ ПЛАН КАЛЕНДАРНИЙ ПЛАН КАЛЕНДАРНИЙ ПЛАН КАЛЕНДАРНИЙ ПЛАН
        </div>

        <h1 class="calendar-title">
            КАЛЕНДАРНИЙ ПЛАН НА РІК
        </h1>

    </div>

</div>

<div class="container">

    @if($calendarPlans->count())

        <div class="row g-4">

            @foreach($calendarPlans as $index => $plan)

                <div class="col-md-4 col-6">

                    <a
                        href="{{ asset('storage/' . $plan->file) }}"
                        target="_blank"
                        class="calendar-card-link"
                    >

                        <div
                            class="calendar-card"
                            style="animation-delay: {{ 0.15 * $index }}s"
                        >

                            <div class="calendar-card-content">

                                <span class="calendar-year">
                                    {{ $plan->year }} рік
                                </span>

                                <span class="calendar-label">
                                    Переглянути
                                </span>

                            </div>

                        </div>

                    </a>

                </div>

            @endforeach

        </div>

    @else

        <div class="calendar-empty">

            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375H14.25V5.625A2.625 2.625 0 0011.625 3h-4.5A2.625 2.625 0 004.5 5.625v12.75A2.625 2.625 0 007.125 21h9.75A2.625 2.625 0 0019.5 18.375V14.25z"
                />
            </svg>

            <div class="calendar-empty-title">
                Календарний план ще не додано
            </div>

            <div class="calendar-empty-text">
                Адміністратор сайту незабаром завантажить календарні плани.
            </div>

        </div>

    @endif

</div>

<script>

    document.addEventListener('DOMContentLoaded', () => {

        const bgText = document.getElementById('calendarBg');

        window.addEventListener('scroll', () => {

            const scrollPos = window.scrollY;

            if (bgText) {

                bgText.style.transform =
                    `translateY(-50%) translateX(calc(-50% - ${scrollPos * 0.3}px))`;

            }

        });

        document.querySelectorAll('.calendar-card').forEach(card => {

            card.addEventListener('mousemove', (e) => {

                if (window.innerWidth < 992) return;

                const rect = card.getBoundingClientRect();

                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const xc = rect.width / 2;
                const yc = rect.height / 2;

                const dx = x - xc;
                const dy = y - yc;

                card.style.transform =
                    `
                        translateY(-10px)
                        scale(1.03)
                        rotateX(${-dy / 18}deg)
                        rotateY(${dx / 18}deg)
                    `;

            });

            card.addEventListener('mouseleave', () => {

                card.style.transform = '';

            });

        });

    });

</script>

</section>
