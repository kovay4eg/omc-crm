@once
    <style>
        .omc-winter-garland {
            position: fixed;
            z-index: 998;
            top: 72px;
            left: 0;
            width: 100%;
            height: 70px;
            overflow: visible;
            pointer-events: none;
        }

        .omc-winter-garland svg { display: block; width: 100%; height: 100%; overflow: visible; }
        .omc-winter-bulb { transform-box: fill-box; transform-origin: center; animation: omc-winter-glow 2.6s ease-in-out infinite alternate; }
        .omc-winter-bulb:nth-of-type(3n) { animation-delay: -.8s; }
        .omc-winter-bulb:nth-of-type(4n) { animation-delay: -1.4s; }

        .omc-snowfall {
            position: fixed;
            z-index: 997;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: .82;
            pointer-events: none;
        }

        @keyframes omc-winter-glow {
            from { opacity: .72; transform: scale(.88); }
            to { opacity: 1; transform: scale(1.08); }
        }

        @media (max-width: 767px) {
            .omc-winter-garland { top: 66px; height: 54px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .omc-winter-bulb { animation: none; }
            .omc-snowfall { display: none; }
        }
    </style>

    <div class="omc-winter-garland" aria-hidden="true">
        <svg viewBox="0 0 1200 70" preserveAspectRatio="none" focusable="false">
            <defs>
                <filter id="omc-garland-glow" x="-80%" y="-80%" width="260%" height="260%">
                    <feGaussianBlur stdDeviation="4.5" result="blur" />
                    <feMerge><feMergeNode in="blur" /><feMergeNode in="SourceGraphic" /></feMerge>
                </filter>
                <filter id="omc-garland-shadow" x="-20%" y="-80%" width="140%" height="260%">
                    <feDropShadow dx="0" dy="4" stdDeviation="3" flood-color="#0f2c1d" flood-opacity=".22" />
                </filter>
                <g id="omc-pine-sprig" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M0 0 H96" stroke="#1f4b32" stroke-width="4" />
                    <g fill="none" stroke="#2f6a45" stroke-width="2.2">
                        <path d="M7 0 26-10 M13 1 31 11 M23 0 42-12 M29 1 48 12 M40 0 59-13 M46 1 65 13 M57 0 76-12 M63 1 82 11 M74 0 93-9 M80 1 96 8" />
                    </g>
                    <g fill="none" stroke="#56845f" stroke-width="1.2" opacity=".9">
                        <path d="M3-1 20-6 M18 2 35 7 M34-1 51-7 M49 2 67 8 M65-1 83-7 M79 2 95 6" />
                    </g>
                </g>
            </defs>
            <g filter="url(#omc-garland-shadow)">
                <path d="M-25 8 Q135 48 305 16 T610 16 T915 16 T1225 8" fill="none" stroke="#173b28" stroke-width="5" stroke-linecap="round" />
                <g>
                    <use href="#omc-pine-sprig" transform="translate(-18 7) rotate(10)" />
                    <use href="#omc-pine-sprig" transform="translate(68 20) rotate(7)" />
                    <use href="#omc-pine-sprig" transform="translate(154 29) rotate(1)" />
                    <use href="#omc-pine-sprig" transform="translate(240 24) rotate(-8)" />
                    <use href="#omc-pine-sprig" transform="translate(326 12) rotate(-5)" />
                    <use href="#omc-pine-sprig" transform="translate(412 14) rotate(7)" />
                    <use href="#omc-pine-sprig" transform="translate(498 25) rotate(4)" />
                    <use href="#omc-pine-sprig" transform="translate(584 17) rotate(-8)" />
                    <use href="#omc-pine-sprig" transform="translate(670 11) rotate(2)" />
                    <use href="#omc-pine-sprig" transform="translate(756 20) rotate(8)" />
                    <use href="#omc-pine-sprig" transform="translate(842 25) rotate(-3)" />
                    <use href="#omc-pine-sprig" transform="translate(928 14) rotate(-7)" />
                    <use href="#omc-pine-sprig" transform="translate(1014 12) rotate(5)" />
                    <use href="#omc-pine-sprig" transform="translate(1100 19) rotate(-5)" />
                    <use href="#omc-pine-sprig" transform="translate(1180 8) rotate(-8)" />
                </g>
            </g>
            <g filter="url(#omc-garland-glow)" stroke="#243e31" stroke-width="1.5">
                <g class="omc-winter-bulb"><path d="M32 15v8" /><circle cx="32" cy="28" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M88 27v7" /><circle cx="88" cy="39" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M145 33v7" /><circle cx="145" cy="45" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M205 34v7" /><circle cx="205" cy="46" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M270 26v7" /><circle cx="270" cy="38" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M335 16v7" /><circle cx="335" cy="28" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M400 19v7" /><circle cx="400" cy="31" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M465 28v7" /><circle cx="465" cy="40" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M535 25v7" /><circle cx="535" cy="37" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M600 16v7" /><circle cx="600" cy="28" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M665 17v7" /><circle cx="665" cy="29" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M730 25v7" /><circle cx="730" cy="37" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M800 28v7" /><circle cx="800" cy="40" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M865 21v7" /><circle cx="865" cy="33" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M930 15v7" /><circle cx="930" cy="27" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M995 18v7" /><circle cx="995" cy="30" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M1060 25v7" /><circle cx="1060" cy="37" r="5.5" fill="#fff0ae" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M1125 20v7" /><circle cx="1125" cy="32" r="5.5" fill="#3871ff" stroke="none" /></g>
                <g class="omc-winter-bulb"><path d="M1180 10v7" /><circle cx="1180" cy="22" r="5.5" fill="#fff0ae" stroke="none" /></g>
            </g>
            <g fill="#d7dde5" stroke="#76808d" stroke-width=".8">
                <path d="M172 35v13" fill="none" /><polygon points="172,47 175,54 183,54 177,59 179,67 172,62 165,67 167,59 161,54 169,54" />
                <path d="M605 17v17" fill="none" /><polygon points="605,33 608,40 616,40 610,45 612,53 605,48 598,53 600,45 594,40 602,40" />
                <path d="M1038 23v13" fill="none" /><polygon points="1038,35 1041,42 1049,42 1043,47 1045,55 1038,50 1031,55 1033,47 1027,42 1035,42" />
            </g>
        </svg>
    </div>

    <canvas id="omcSnowfall" class="omc-snowfall" aria-hidden="true"></canvas>

    <script>
        (() => {
            const canvas = document.getElementById('omcSnowfall');
            if (!canvas || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            const context = canvas.getContext('2d', { alpha: true });
            if (!context) return;

            const flakes = [];
            let width = 0;
            let height = 0;
            let animationFrame = 0;
            let lastFrame = 0;

            const resetFlake = (flake, randomY = false) => {
                flake.x = Math.random() * width;
                flake.y = randomY ? Math.random() * height : -12;
                flake.radius = 1.2 + Math.random() * 3.8;
                flake.speed = .28 + Math.random() * .82;
                flake.drift = -.3 + Math.random() * .6;
                flake.alpha = .2 + Math.random() * .48;
                flake.shape = Math.random() > .67 ? 'crystal' : 'dot';
                flake.rotation = Math.random() * Math.PI;
                flake.spin = -.004 + Math.random() * .008;
            };

            const resize = () => {
                const ratio = Math.min(window.devicePixelRatio || 1, 1.5);
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = Math.round(width * ratio);
                canvas.height = Math.round(height * ratio);
                canvas.style.width = width + 'px';
                canvas.style.height = height + 'px';
                context.setTransform(ratio, 0, 0, ratio, 0, 0);

                const targetCount = width < 768 ? 38 : 78;
                flakes.length = targetCount;
                for (let index = 0; index < targetCount; index += 1) {
                    flakes[index] ||= {};
                    resetFlake(flakes[index], true);
                }
            };

            const draw = timestamp => {
                animationFrame = requestAnimationFrame(draw);
                if (document.hidden || timestamp - lastFrame < 32) return;
                lastFrame = timestamp;
                context.clearRect(0, 0, width, height);

                flakes.forEach(flake => {
                    flake.y += flake.speed;
                    flake.x += flake.drift + Math.sin((flake.y + flake.radius) * .018) * .12;
                    flake.rotation += flake.spin;
                    if (flake.y > height + 10 || flake.x < -15 || flake.x > width + 15) resetFlake(flake);

                    context.save();
                    context.translate(flake.x, flake.y);
                    context.rotate(flake.rotation);
                    context.strokeStyle = `rgba(139, 181, 255, ${flake.alpha})`;
                    context.fillStyle = `rgba(151, 191, 255, ${flake.alpha})`;
                    context.shadowColor = 'rgba(255, 255, 255, .9)';
                    context.shadowBlur = 4;

                    if (flake.shape === 'crystal' && flake.radius > 2.4) {
                        context.lineWidth = Math.max(.65, flake.radius * .18);
                        context.beginPath();
                        for (let arm = 0; arm < 6; arm += 1) {
                            context.moveTo(0, 0);
                            context.lineTo(0, -flake.radius * 1.8);
                            context.rotate(Math.PI / 3);
                        }
                        context.stroke();
                    } else {
                        context.beginPath();
                        context.arc(0, 0, flake.radius, 0, Math.PI * 2);
                        context.fill();
                    }
                    context.restore();
                });
            };

            resize();
            animationFrame = requestAnimationFrame(draw);
            window.addEventListener('resize', resize, { passive: true });
            window.addEventListener('pagehide', () => cancelAnimationFrame(animationFrame), { once: true });
        })();
    </script>
@endonce
