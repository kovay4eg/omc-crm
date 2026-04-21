<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Команда</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;800&display=swap" rel="stylesheet">

<style>
:root {
    --blue: #131DA4;
}

body {
    font-family: 'Commissioner', sans-serif;
    background: #f5f6fb;
}

.team-hero {
    position: relative;
    padding-top: 120px;
    overflow: hidden;
}

.team-hero-bg {
    position: absolute;
    top: -40px;
    left: 0;
    font-size: 220px;
    font-weight: 800;
    color: var(--blue);
    opacity: 0.05;
    white-space: nowrap;
    pointer-events: none;
    will-change: transform;
}

.team-title {
    font-weight: 800;
    font-size: 42px;
    color: var(--blue);
    margin-bottom: 70px;
    position: relative;
    z-index: 2;
}

.team-desc {
    text-align: center;
    font-size: 16px;
    max-width: 700px;
    margin: 0 auto 40px auto;
    position: relative;
    z-index: 2;
}

.team-banner {
    width: 100%;
    height: 320px;
    border-radius: 24px;
    background: #ccc;
    overflow: hidden;
    margin-bottom: 80px;
}

.team-banner-placeholder{
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e3e5f1;
    color: #9aa0c3;
    font-size: 22px;
    font-weight: 600;
    border-radius: 24px;
}

.dept-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 0;
    border-bottom: 1px solid #dcdcdc;
    cursor: pointer;
}

.dept-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.dept-icon img {
    width: 32px;
    transition: transform 0.2s linear;
}

.dept-title {
    color: var(--blue);
    font-weight: 700;
    font-size: 16px;
}

.arrow svg {
    width: 34px;
    height: 34px;
    transition: 0.25s ease;
}

.arrow svg path {
    stroke: var(--blue);
    fill: transparent;
}

.dept-row.active .arrow svg {
    transform: rotate(90deg);
}

.dept-row.active .arrow svg path {
    fill: var(--blue);
}

/* ==== АНИМАЦІЯ БЛОКУ ==== */

.employees {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: all 0.4s ease;
}

.employees.open {
    max-height: 2000px;
    opacity: 1;
    padding: 40px 0 20px;
}

/* ==== КАРТОЧКИ ==== */

.employee-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 16px;
    text-align: center;
    height: 100%;
    box-shadow: 0 6px 20px rgba(0,0,0,0.05);

    opacity: 0;
    transform: translateY(20px);
    transition: all 0.4s ease;
}

.employees.open .employee-card {
    opacity: 1;
    transform: translateY(0);
}

/* затримка по черзі */
.employees.open .employee-card:nth-child(1){transition-delay:0.05s;}
.employees.open .employee-card:nth-child(2){transition-delay:0.1s;}
.employees.open .employee-card:nth-child(3){transition-delay:0.15s;}
.employees.open .employee-card:nth-child(4){transition-delay:0.2s;}
.employees.open .employee-card:nth-child(5){transition-delay:0.25s;}
.employees.open .employee-card:nth-child(6){transition-delay:0.3s;}

.employee-photo {
    width: 100%;
    height: 320px;
    border-radius: 18px;
    overflow: hidden;
    background: #ddd;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.employee-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.employee-placeholder {
    width: 120px;
    opacity: 0.4;
}

.employee-name {
    font-weight: 800;
    font-size: 15px;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.employee-position {
    font-size: 14px;
    color: #666;
}

.mt-5 {
    margin-top: 60px !important;
}
</style>

</head>
<body>

<div class="team-hero">

    <div class="team-hero-bg" id="heroBg">
        КОМАНДА КОМАНДА КОМАНДА КОМАНДА КОМАНДА КОМАНДА
    </div>

    <div class="container">

        <h1 class="team-title">КОМАНДА</h1>

        <div class="team-desc">
            Команда ПОМЦ – люди, які перетворюють твої сміливі ідеї на реальні можливості 💙
        </div>

    </div>

</div>

<div class="container">

    <div class="team-banner">
        @if(isset($settings) && $settings->team_banner)
            <img src="{{ Storage::url($settings->team_banner) }}" style="width:100%; height:100%; object-fit:cover;">
        @else
            <div class="team-banner-placeholder">
                Team Photo
            </div>
        @endif
    </div>

    <div class="mt-5">

        @foreach($departments as $department)

        <div class="dept-row" onclick="toggleDept({{ $department->id }}, this)">

            <div class="dept-left">
                <div class="dept-icon">
                    <img src="{{ asset('icons/flower.png') }}">
                </div>

                <div class="dept-title">

                    @php
                        $name = mb_strtolower($department->name);
                        $clean = trim(str_replace('відділ ', '', $name));
                    @endphp

                    @if($clean === 'адміністрація установи')
                        {{ mb_strtoupper($clean) }}
                    @else
                        {{ 'ВІДДІЛ ' . mb_strtoupper($clean) }}
                    @endif

                </div>
            </div>

            <div class="arrow">
                <svg viewBox="0 0 48 48">
                    <path d="M14 10 L34 24 L14 38 Z"/>
                </svg>
            </div>

        </div>

        <div class="employees" id="dept-{{ $department->id }}">

            <div class="row">

                @forelse($department->employees as $employee)

                    <div class="col-md-4 col-lg-3 mb-4 d-flex">

                        <div class="employee-card w-100">

                            <div class="employee-photo">
                                @if($employee->photo)
                                    <img src="{{ Storage::url($employee->photo) }}">
                                @else
                                    <img src="{{ asset('images/default-avatar.png') }}" class="employee-placeholder">
                                @endif
                            </div>

                            <div class="employee-name">
                                {{ $employee->last_name }} {{ $employee->first_name }}
                            </div>

                            <div class="employee-position">
                                {{ $employee->position->name ?? '' }}
                            </div>

                        </div>

                    </div>

                @empty

                    <div class="col-12 text-center text-muted">
                        Немає працівників
                    </div>

                @endforelse

            </div>

        </div>

        @endforeach

    </div>

</div>

<script>
function toggleDept(id, el) {
    let block = document.getElementById('dept-' + id);

    el.classList.toggle('active');
    block.classList.toggle('open');
}

const bg = document.getElementById('heroBg');

window.addEventListener('scroll', () => {
    const y = window.scrollY;

    bg.style.transform = `translateX(${y * 0.2}px)`;

    document.querySelectorAll('.dept-icon img').forEach(el => {
        el.style.transform = `rotate(${y * 0.2}deg)`;
    });
});
</script>

</body>
</html>