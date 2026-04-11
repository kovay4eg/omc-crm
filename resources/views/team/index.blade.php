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

/* TITLE */
.team-title {
    font-weight: 800;
    font-size: 36px;
    color: var(--blue);
}

/* TEXT */
.team-desc {
    text-align: center;
    font-size: 20px;
    margin: 20px 0;
}

/* BANNER */
.team-banner {
    width: 100%;
    height: 300px;
    border-radius: 20px;
    background: #ccc;
}

/* DEPT */
.dept-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 0;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
}

.dept-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dept-icon img {
    width: 28px;
}

.dept-title {
    color: var(--blue);
    font-weight: 700;
}

/* СТРІЛКА — НЕ ЧІПАВ */
.arrow svg {
    width: 34px;
    height: 34px;
    transition: 0.25s ease;
}

.arrow svg path {
    stroke: var(--blue);
    fill: transparent;
    transition: 0.25s ease;
}

.dept-row:hover .arrow svg {
    transform: translateX(4px);
}

.dept-row.active .arrow svg {
    transform: rotate(90deg);
}

.dept-row.active .arrow svg path {
    fill: var(--blue);
}

/* EMPLOYEES */
.employees {
    display: none;
    padding: 20px 0;
}

.employee-card {
    background: #f1f1f1;
    border-radius: 12px;
    padding: 10px;
    text-align: center;
}
</style>

</head>
<body>

<div class="container mt-5">

    <h1 class="team-title">КОМАНДА</h1>

    <div class="team-desc">
        Команда ПОМЦ – люди, які перетворюють твої сміливі ідеї на реальні можливості 💙
    </div>

    <div class="team-banner">
        @if(isset($settings) && $settings->team_banner)
            <img src="{{ Storage::url($settings->team_banner) }}" style="width:100%; height:100%; object-fit:cover;">
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
                    {{ mb_strtoupper($department->name) }}
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

                    <div class="col-md-3 mb-3">

                        <div class="employee-card">

                            @if($employee->photo)
                                <img src="{{ Storage::url($employee->photo) }}"
                                     style="width:100%; border-radius:10px; margin-bottom:10px;">
                            @endif

                            <!-- ПРАВИЛЬНИЙ ВИВІД -->
                            <div style="font-weight:700; text-transform:uppercase;">
                                {{ $employee->last_name }} {{ $employee->first_name }}
                            </div>

                            <div style="font-size:14px;">
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

    block.style.display =
        block.style.display === 'block' ? 'none' : 'block';
}
</script>

</body>
</html>