@php

use App\Models\Statute;

$statute = Statute::latest()->first();

$pdfUrl = null;

if ($statute && !empty($statute->file)) {

    $pdfUrl = asset('storage/' . $statute->file);

}

@endphp

<section id="statut-section" class="statut-section">

<style>

.statut-section{
    position:relative;
    padding:120px 0 80px;
    overflow:hidden;
    background:transparent;
    font-family:'Commissioner',sans-serif;
}

/* =========================
   TITLE BLOCK
========================= */

.statut-title-block{
    position:relative;
    min-height:220px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    margin-bottom:60px;
}

.statut-bg-text{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%);
    font-size:clamp(64px, 11vw, 160px);
    font-weight:800;
    line-height:1;
    color:#eef0ff;
    opacity:1;
    letter-spacing:-.05em;
    white-space:nowrap;
    pointer-events:none;
    z-index:0;
    width:max-content;
    will-change:transform;
}

.statut-title{
    position:relative;
    z-index:2;
    font-size:64px;
    font-weight:900;
    color:#131DA4;
    margin:0 0 35px;
    text-transform:uppercase;
    padding-top:80px;
}

.statut-desc{
    position:relative;
    z-index:2;
    text-align:center;
    font-size:20px;
    color:#303030;
    margin:0;
}

/* =========================
   PDF WRAPPER
========================= */

.statut-pdf-wrapper{
    position:relative;
    width:100%;
    min-height:900px;
    background:#f8f9fc;
    border-radius:32px;
    overflow:hidden;
    box-shadow:0 25px 50px rgba(19,29,164,.08);
    border:1px solid rgba(19,29,164,.05);
}

/* =========================
   DESKTOP PREVIEW
========================= */

.statut-desktop-preview{
    display:block;
}

.statut-preview-container{
    position:relative;
    width:100%;
    height:900px;
    overflow:hidden;
}

.statut-pdf-frame{
    width:100%;
    height:100%;
    border:none;
    display:block;
    transition:all .35s ease;
}

.statut-pdf-frame.blurred{
    filter:blur(6px);
    opacity:.65;
    pointer-events:none;
    transform:scale(1.01);
}

/* =========================
   OVERLAY
========================= */

.statut-overlay{
    position:absolute;
    inset:0;
    z-index:20;

    display:flex;
    align-items:center;
    justify-content:center;

    background:rgba(255,255,255,.04);

    transition:all .3s ease;
}

.statut-overlay.active{
    opacity:0;
    pointer-events:none;
}

.statut-open-btn{
    border:none;

    background:#131DA4;
    color:white;

    font-size:18px;
    font-weight:700;

    padding:18px 34px;

    border-radius:18px;

    cursor:pointer;

    transition:all .25s ease;

    opacity:0;
    transform:translateY(15px);

    box-shadow:0 10px 25px rgba(19,29,164,.2);
}

.statut-overlay:hover .statut-open-btn{
    opacity:1;
    transform:translateY(0);
}

.statut-open-btn:hover{
    background:#0d1688;
    transform:scale(1.04);
}

/* =========================
   MOBILE PREVIEW
========================= */

.statut-mobile-preview{
    display:none;
}

.statut-mobile-card{
    min-height:420px;

    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;

    text-align:center;

    padding:40px 24px;
}

.statut-mobile-card svg{
    color:#131DA4;
    margin-bottom:24px;
}

.statut-mobile-card h4{
    font-size:28px;
    font-weight:800;
    margin-bottom:14px;
    color:#131DA4;
}

.statut-mobile-card p{
    color:#666;
    margin-bottom:30px;
    font-size:16px;
}

.statut-mobile-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;

    padding:16px 28px;

    border-radius:18px;

    background:#131DA4;
    color:white;

    text-decoration:none;

    font-weight:700;

    transition:.25s ease;
}

.statut-mobile-btn:hover{
    background:#0d1688;
    color:white;
}

/* =========================
   EMPTY STATE
========================= */

.statut-empty{
    min-height:900px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    color:#131DA4;
    opacity:.65;
    padding:40px;
}

.statut-empty svg{
    margin-bottom:24px;
}

.statut-empty h5{
    font-size:28px;
    font-weight:800;
    margin-bottom:12px;
}

.statut-empty p{
    font-size:17px;
    color:#666;
    margin:0;
}

/* =========================
   TABLET
========================= */

@media(max-width:991px){

    .statut-title{
        font-size:46px;
    }

    .statut-pdf-wrapper{
        border-radius:24px;
    }

}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .statut-section{
        padding-top:80px;
    }

    /* Декоративний фон має залишатися видимим і в мобільній версії. */
    .statut-bg-text{
        display:block;
        visibility:visible;
        opacity:1;
        font-size:clamp(64px, 19vw, 94px);
    }

    .statut-title{
        font-size:34px;
        margin-bottom:20px;
    }

    .statut-desc{
        font-size:16px;
    }

    .statut-pdf-wrapper{
        border-radius:20px;
        min-height:600px;
    }

    .statut-desktop-preview{
        display:none;
    }

    .statut-mobile-preview{
        display:block;
    }

    .statut-empty{
        min-height:600px;
    }

    .statut-empty h5{
        font-size:22px;
    }

    .statut-empty p{
        font-size:15px;
    }

}

</style>

<div class="container-1200">

    <div class="statut-title-block">

        <div class="statut-bg-text" id="statutBgText">
            СТАТУТ СТАТУТ СТАТУТ СТАТУТ СТАТУТ
        </div>

        <h2 class="statut-title">
            СТАТУТ
        </h2>

        <div class="statut-desc">
            Офіційний статут Полтавського обласного молодіжного центру
        </div>

    </div>

    <div class="statut-pdf-wrapper">

        @if(!empty($pdfUrl))

            {{-- DESKTOP PREVIEW --}}

            <div class="statut-desktop-preview">

                <div class="statut-preview-container">

                    <div
                        class="statut-overlay"
                        id="statutOverlay"
                    >
                        <button
                            class="statut-open-btn"
                            id="openStatuteBtn"
                        >
                            Переглянути документ
                        </button>
                    </div>

                    <iframe
                        id="statutFrame"
                        class="statut-pdf-frame blurred"
                        src="{{ $pdfUrl }}#view=FitH&toolbar=1&navpanes=0"
                        title="Статут"
                    ></iframe>

                </div>

            </div>

            {{-- MOBILE PREVIEW --}}

            <div class="statut-mobile-preview">

                <div class="statut-mobile-card">

                    <svg xmlns="http://www.w3.org/2000/svg" width="70" height="70" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2z"/>
                    </svg>

                    <h4>
                        Статут установи
                    </h4>

                    <p>
                        Відкрити або завантажити офіційний документ
                    </p>

                    <a
                        href="{{ $pdfUrl }}"
                        target="_blank"
                        class="statut-mobile-btn"
                    >
                        Відкрити PDF
                    </a>

                </div>

            </div>

        @else

            <div class="statut-empty">

                <svg xmlns="http://www.w3.org/2000/svg" width="70" height="70" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2z"/>
                    <path d="M4.603 14.087a.81.81 0 0 1-.438-.42"/>
                </svg>

                <h5>
                    Документ ще не додано
                </h5>

                <p>
                    Адміністратор сайту незабаром завантажить актуальний статут установи.
                </p>

            </div>

        @endif

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const bg = document.getElementById('statutBgText');

    window.addEventListener('scroll', () => {

        const scrollPos = window.scrollY;

        if(bg){

            bg.style.transform =
                `translate(-50%, -50%) translateX(-${scrollPos * 0.3}px)`;

        }

    });

});

/* =========================
   PDF ACTIVATION
========================= */

document.addEventListener('DOMContentLoaded', function () {

    const button = document.getElementById('openStatuteBtn');
    const overlay = document.getElementById('statutOverlay');
    const frame = document.getElementById('statutFrame');

    if(button && overlay && frame){

        button.addEventListener('click', function () {

            overlay.classList.add('active');

            frame.classList.remove('blurred');

        });

    }

});

</script>

</section>
