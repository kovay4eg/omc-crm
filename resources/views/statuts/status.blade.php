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
   PDF BLOCK
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

.statut-pdf-wrapper iframe{
    width:100%;
    height:900px;
    border:none;
    display:block;
}

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
   MOBILE
========================= */

@media(max-width:991px){

    .statut-title{
        font-size:46px;
    }

    .statut-pdf-wrapper{
        border-radius:24px;
    }

}

@media(max-width:768px){

    .statut-section{
        padding-top:80px;
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

    .statut-pdf-wrapper iframe{
        height:600px;
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

            <iframe
                src="{{ $pdfUrl }}#view=FitH&toolbar=0&navpanes=0"
                title="Статут"
            ></iframe>

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

</script>

</section>