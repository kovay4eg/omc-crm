@once
    <style>
        .omc-winter-garland {
            position: fixed;
            z-index: 998;
            top: 72px;
            left: 0;
            width: 100%;
            height: 58px;
            overflow: visible;
            pointer-events: none;
        }

        .omc-winter-garland svg { display: block; width: 100%; height: 100%; overflow: visible; }
        .omc-winter-bulb { transform-box: fill-box; transform-origin: center; animation: omc-winter-glow 2.4s ease-in-out infinite alternate; }
        .omc-winter-bulb:nth-of-type(3n) { animation-delay: -.8s; }
        .omc-winter-bulb:nth-of-type(4n) { animation-delay: -1.4s; }

        .omc-snowfall {
            position: fixed;
            z-index: 997;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: .72;
            pointer-events: none;
        }

        @keyframes omc-winter-glow {
            from { opacity: .72; transform: scale(.88); }
            to { opacity: 1; transform: scale(1.08); }
        }

        @media (max-width: 767px) {
            .omc-winter-garland { top: 66px; height: 48px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .omc-winter-bulb { animation: none; }
            .omc-snowfall { display: none; }
        }
    </style>

    <div class="omc-winter-garland" aria-hidden="true">
        <svg viewBox="0 0 1200 58" preserveAspectRatio="none" focusable="false">
            <defs>
                <filter id="omc-garland-glow" x="-80%" y="-80%" width="260%" height="260%">
                    <feGaussianBlur stdDeviation="3" result="blur" />
                    <feMerge><feMergeNode in="blur" /><feMergeNode in="SourceGraphic" /></feMerge>
                </filter>
            </defs>
            <path d="M-20 5 Q130 48 300 13 T610 14 T915 13 T1220 5" fill="none" stroke="#284d35" stroke-width="9" stroke-linecap="round" />
            <path d="M-20 5 Q130 48 300 13 T610 14 T915 13 T1220 5" fill="none" stroke="#4d7a57" stroke-width="3" stroke-dasharray="2 5" stroke-linecap="round" />
            <g filter="url(#omc-garland-glow)">
                <circle class="omc-winter-bulb" cx="38" cy="17" r="4" fill="#ffe8a3" />
                <circle class="omc-winter-bulb" cx="122" cy="32" r="4" fill="#4c74ff" />
                <circle class="omc-winter-bulb" cx="208" cy="30" r="4" fill="#fff2c6" />
                <circle class="omc-winter-bulb" cx="302" cy="13" r="4" fill="#4268ff" />
                <circle class="omc-winter-bulb" cx="397" cy="29" r="4" fill="#ffe8a3" />
                <circle class="omc-winter-bulb" cx="500" cy="29" r="4" fill="#4268ff" />
                <circle class="omc-winter-bulb" cx="607" cy="14" r="4" fill="#fff2c6" />
                <circle class="omc-winter-bulb" cx="710" cy="29" r="4" fill="#4268ff" />
                <circle class="omc-winter-bulb" cx="812" cy="29" r="4" fill="#ffe8a3" />
                <circle class="omc-winter-bulb" cx="912" cy="13" r="4" fill="#4268ff" />
                <circle class="omc-winter-bulb" cx="1006" cy="31" r="4" fill="#fff2c6" />
                <circle class="omc-winter-bulb" cx="1100" cy="29" r="4" fill="#4268ff" />
                <circle class="omc-winter-bulb" cx="1170" cy="14" r="4" fill="#ffe8a3" />
            </g>
            <g fill="#cbd5e1" stroke="#64748b" stroke-width=".7">
                <path d="M165 31 v13 m-6 5 h12 m-6-6 -5 9 m5-9 5 9" />
                <path d="M605 14 v20 m-7 5 h14 m-7-7 -6 10 m6-10 6 10" />
                <path d="M1045 29 v15 m-6 5 h12 m-6-6 -5 9 m5-9 5 9" />
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
                flake.radius = 1.1 + Math.random() * 2.4;
                flake.speed = .35 + Math.random() * .75;
                flake.drift = -.25 + Math.random() * .5;
                flake.alpha = .32 + Math.random() * .46;
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

                const targetCount = width < 768 ? 30 : 55;
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
                    if (flake.y > height + 10 || flake.x < -15 || flake.x > width + 15) resetFlake(flake);

                    context.beginPath();
                    context.arc(flake.x, flake.y, flake.radius, 0, Math.PI * 2);
                    context.fillStyle = `rgba(132, 174, 255, ${flake.alpha})`;
                    context.shadowColor = 'rgba(255, 255, 255, .8)';
                    context.shadowBlur = 3;
                    context.fill();
                });
            };

            resize();
            animationFrame = requestAnimationFrame(draw);
            window.addEventListener('resize', resize, { passive: true });
            window.addEventListener('pagehide', () => cancelAnimationFrame(animationFrame), { once: true });
        })();
    </script>
@endonce
