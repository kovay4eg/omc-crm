<section id="team-section" class="team-section">

<style>
:root{
    --blue:#131DA4;
}

.team-section{
    font-family:'Commissioner',sans-serif;
    background: transparent;
    position:relative;
    overflow:hidden;
    padding:120px 0 80px;
}

/* =========================
   HERO
========================= */

.team-hero{
    position:relative;
    overflow:hidden;
    padding:50px 0 40px;
    margin-bottom:40px;
}

.team-title-block{
    position:relative;
    min-height:220px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.team-hero-bg{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%);
    font-size:clamp(80px, 15vw, 220px);
    font-weight:800;
    line-height:1;
    color:#E8EAFB;
    opacity:.45;
    white-space:nowrap;
    pointer-events:none;
    z-index:0;
    width:max-content;
    will-change:transform;
}

.team-title{
    position:relative;
    z-index:2;
    font-size:64px;
    font-weight:900;
    color:var(--blue);
    margin-bottom:35px;
    text-transform:uppercase;
    padding-top: 80px;

}

.team-desc{
    position:relative;
    z-index:2;
    text-align:center;
    font-size:20px;
    color:#303030;
    margin:0;
}

/* =========================
   TEAM PHOTO
========================= */

.team-banner{
    width:100%;
    border-radius:32px;
    overflow:hidden;
    background:#e5e7f2;
    margin-bottom:80px;
    position:relative;
}

.team-banner-image{
    width:100%;
    height:auto;
    display:block;
    object-fit:contain;
}

.team-banner-placeholder{
    width:100%;
    min-height:500px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#9ea5d1;
    font-size:42px;
    font-weight:700;
}

/* =========================
   DEPARTMENT
========================= */

.dept-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:26px 0;
    border-bottom:1px solid #d9d9d9;
    cursor:pointer;
}

.dept-left{
    display:flex;
    align-items:center;
    gap:18px;
}

.dept-icon img{
    width:34px;
    transition:.3s;
}

.dept-title{
    color:var(--blue);
    font-weight:800;
    font-size:20px;
    text-transform:uppercase;
}

/* ===== СТРІЛКА ===== */

.arrow{
    display:flex;
    align-items:center;
    justify-content:center;
    transition:all .4s ease;
}

.arrow .icon-arrow-custom{
    width:32px;
    height:32px;
    content:url("/images/icons/arrow.svg");
    display:block;
    transition:all .4s ease;
}

.dept-row.active .icon-arrow-custom{
    transform:rotate(90deg);
    filter:invert(18%) sepia(51%) saturate(5436%) hue-rotate(229deg) brightness(91%) contrast(92%);
}

/* =========================
   EMPLOYEES
========================= */

.employees{
    max-height:0;
    overflow:hidden;
    opacity:0;
    transition:all .5s ease;
}

.employees.open{
    max-height:5000px;
    opacity:1;
    padding:45px 0 20px;
}

.employee-card{
    background:#fff;
    border-radius:28px;
    padding:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
    transition:.35s ease;
    height:100%;
    position:relative;
    overflow:hidden;
    transform:translateY(20px);
    opacity:0;
}

.employees.open .employee-card{
    transform:translateY(0);
    opacity:1;
}

.employee-card:hover{
    transform:translateY(-12px) scale(1.02);
    box-shadow:0 20px 50px rgba(19,29,164,.12);
}

.employee-card::before{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(
        135deg,
        rgba(19,29,164,.05),
        transparent
    );
    opacity:0;
    transition:.35s;
}

.employee-card:hover::before{
    opacity:1;
}

.employee-photo{
    width:100%;
    height:340px;
    border-radius:22px;
    overflow:hidden;
    margin-bottom:18px;
    background:#efefef;
}

.employee-photo img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:.4s;
}

.employee-card:hover .employee-photo img{
    transform:scale(1.06);
}

.employee-placeholder{
    width:100%;
    height:100%;
    object-fit:contain !important;
    opacity:.45;
}

.employee-name{
    font-size:20px;
    font-weight:900;
    color:#1c1c1c;
    text-transform:uppercase;
    margin-bottom:8px;
}

.employee-position{
    color:#666;
    font-size:16px;
    line-height:1.4;
}

/* =========================
   MOBILE
========================= */

@media(max-width:991px){

    .team-title{
        font-size:46px;
    }

    .employee-photo{
        height:300px;
    }

}

@media(max-width:768px){

    .team-section{
        padding-top:80px;
    }

    .team-title{
        font-size:34px;
        margin-bottom:20px;
    }

    .team-desc{
        font-size:16px;
    }

    .team-banner{
        border-radius:24px;
        margin-bottom:50px;
    }

    .team-banner-placeholder{
        min-height:240px;
        font-size:28px;
    }

    .dept-title{
        font-size:15px;
    }

    .employee-photo{
        height:260px;
    }

    .employee-name{
        font-size:17px;
    }

    .employee-position{
        font-size:14px;
    }

}
</style>

<div class="team-hero">

    <div class="container">

        <div class="team-title-block">

            <div class="team-hero-bg" id="heroBg">
                КОМАНДА КОМАНДА КОМАНДА КОМАНДА КОМАНДА
            </div>

            <h1 class="team-title">
                КОМАНДА
            </h1>

            <div class="team-desc">
                Команда ПОМЦ – люди, які перетворюють твої сміливі ідеї на реальні можливості 💙
            </div>

        </div>

    </div>

</div>

<div class="container">

    <!-- TEAM PHOTO -->

    <div class="team-banner">

       @if(isset($settings) && !empty($settings->team_banner))

            <img
                src="{{ asset('storage/' . $settings->team_banner) }}"
                class="team-banner-image"
                alt="Team Photo"
            >

        @else

            <div class="team-banner-placeholder">
                Team Photo
            </div>

        @endif

    </div>

    <!-- DEPARTMENTS -->

    @foreach($departments as $department)

        <div
            class="dept-row"
            onclick="toggleDept({{ $department->id }}, this)"
        >

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
                        ВІДДІЛ {{ mb_strtoupper($clean) }}
                    @endif

                </div>

            </div>

            <div class="arrow">
                <i class="icon-arrow-custom"></i>
            </div>

        </div>

        <div
            class="employees"
            id="dept-{{ $department->id }}"
        >

            <div class="row">

                @forelse($department->employees as $employee)

                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4 d-flex">

                        <div class="employee-card w-100">

                            <div class="employee-photo">

                                @if($employee->photo)

                                    <img
                                        src="{{ asset('storage/' . $employee->photo) }}"
                                        alt="{{ $employee->first_name }}"
                                    >

                                @else

                                    <img
                                        src="{{ asset('images/default-avatar.png') }}"
                                        class="employee-placeholder"
                                    >

                                @endif

                            </div>

                            <div class="employee-name">
                                {{ $employee->last_name }}
                                {{ $employee->first_name }}
                            </div>

                            <div class="employee-position">
                                {{ $employee->position->name ?? '' }}
                            </div>

                        </div>

                    </div>

                @empty

                    <div class="col-12">

                        <div class="text-center text-muted py-5">
                            Немає працівників
                        </div>

                    </div>

                @endforelse

            </div>

        </div>

    @endforeach

</div>

<script>

function toggleDept(id, el){

    const block = document.getElementById('dept-' + id);

    el.classList.toggle('active');

    block.classList.toggle('open');
}

document.addEventListener('DOMContentLoaded', () => {

    const bg = document.getElementById('heroBg');

    window.addEventListener('scroll', () => {

        const scrollPos = window.scrollY;

        if(bg){
            bg.style.transform = `translateY(-50%) translateX(-${scrollPos * 0.3}px)`;
        }

        document.querySelectorAll('.dept-icon img').forEach(el => {

            el.style.transform = `rotate(${scrollPos * 0.15}deg)`;

        });

    });

});

</script>

</section>