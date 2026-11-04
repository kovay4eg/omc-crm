@once
    <style>
        :root {
            --omc-winter-reserve: 135px;
        }

        .omc-winter-garland {
            position: absolute;
            z-index: 1001;
            top: var(--omc-header-height, 96px);
            left: 0;
            width: 100%;
            height: clamp(92px, calc(5.67vw + 14px), 123px);
            overflow: visible;
            pointer-events: none;
            transition: top .2s ease;
        }

        .omc-winter-garland__foliage {
            position: absolute;
            top: 7px;
            left: 0;
            width: 100%;
            height: calc(100% - 14px);
            overflow: hidden;
            filter: drop-shadow(0 4px 4px rgba(12, 45, 29, .18));
        }
        .omc-winter-garland__foliage picture,
        .omc-winter-garland__foliage img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .omc-winter-garland__wire { position: absolute; inset: 0; display: block; width: 100%; height: 100%; overflow: visible; }
        .omc-winter-garland__lights { position: absolute; inset: 0; }
        .omc-winter-light {
            position: absolute;
            top: var(--omc-light-y);
            left: var(--omc-light-x);
            width: 16px;
            height: 30px;
            transform: translateX(-50%);
        }
        .omc-winter-light__stem {
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 9px;
            border-radius: 2px;
            background: #193729;
            transform: translateX(-50%);
        }
        .omc-winter-bulb {
            position: absolute;
            top: 10px;
            left: 1px;
            width: 14px;
            height: 17px;
            border: 1px solid rgba(255, 255, 255, .78);
            border-radius: 50% 50% 56% 56%;
            background: var(--omc-bulb-color);
            box-shadow: inset 0 2px 2px rgba(255, 255, 255, .45), 0 1px 2px rgba(17, 43, 31, .18);
            animation: omc-winter-twinkle var(--omc-bulb-duration, 1.65s) ease-in-out var(--omc-bulb-delay, 0s) infinite;
            will-change: opacity, filter;
        }
        .omc-winter-bulb::before {
            content: '';
            position: absolute;
            top: -4px;
            left: 50%;
            width: 7px;
            height: 4px;
            border-radius: 2px 2px 1px 1px;
            background: #294c3b;
            transform: translateX(-50%);
        }
        .omc-winter-bulb::after {
            content: '';
            position: absolute;
            inset: -6px;
            z-index: -1;
            border-radius: 50%;
            background: var(--omc-bulb-color);
            filter: blur(5px);
            animation: omc-winter-glow-pulse var(--omc-bulb-duration, 1.65s) ease-in-out var(--omc-bulb-delay, 0s) infinite;
        }

        .omc-snowfall {
            position: fixed;
            z-index: 997;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: .82;
            pointer-events: none;
        }

        @keyframes omc-winter-twinkle {
            0%, 100% { opacity: .58; filter: brightness(.88); }
            42% { opacity: .78; filter: brightness(1.08); }
            56% { opacity: 1; filter: brightness(1.65); }
            72% { opacity: .84; filter: brightness(1.2); }
        }

        @keyframes omc-winter-glow-pulse {
            0%, 100% { opacity: .18; }
            52%, 60% { opacity: .92; }
            78% { opacity: .34; }
        }

        @media (max-width: 1200px) {
            :root { --omc-winter-reserve: 125px; }
            .omc-winter-garland { height: clamp(76px, calc(8.28vw + 14px), 113px); }
        }

        @media (max-width: 640px) {
            :root { --omc-winter-reserve: 118px; }
            .omc-winter-garland { height: clamp(66px, calc(15.34vw + 8px), 106px); }
            .omc-winter-garland__foliage { top: 4px; height: calc(100% - 8px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .omc-snowfall { display: none; }
        }
    </style>

    <div class="omc-winter-garland" aria-hidden="true">
        <div class="omc-winter-garland__foliage">
            <picture>
                <source media="(max-width: 640px)" srcset="{{ asset('images/holiday/winter-garland-v2.png') }}" />
                <source media="(max-width: 1200px)" srcset="{{ asset('images/holiday/winter-garland-tablet.png') }}" />
                <img
                    src="{{ asset('images/holiday/winter-garland-wide.png') }}"
                    alt=""
                    decoding="async"
                />
            </picture>
        </div>
        <svg class="omc-winter-garland__wire" viewBox="0 0 1200 100" preserveAspectRatio="none" focusable="false">
            <path d="M-20 25 C120 44 230 45 340 26 S550 8 665 29 S880 49 995 26 S1140 9 1220 25" fill="none" stroke="#173a29" stroke-width="2.2" stroke-linecap="round" opacity=".82" />
        </svg>
        <div class="omc-winter-garland__lights">
            @foreach ([
                [52, 30, '#ffd95a'], [126, 38, '#4d7cff'], [202, 40, '#ff6580'],
                [278, 34, '#ffd95a'], [354, 24, '#4d7cff'], [430, 17, '#ff6580'],
                [506, 17, '#ffd95a'], [582, 22, '#4d7cff'], [658, 29, '#ff6580'],
                [734, 36, '#ffd95a'], [810, 41, '#4d7cff'], [886, 39, '#ff6580'],
                [962, 30, '#ffd95a'], [1038, 20, '#4d7cff'], [1114, 17, '#ff6580'],
                [1186, 23, '#ffd95a'],
            ] as [$x, $y, $color])
                <span
                    class="omc-winter-light"
                    style="--omc-light-x: {{ $x / 12 }}%; --omc-light-y: {{ $y }}%; --omc-bulb-color: {{ $color }}; --omc-bulb-duration: {{ 1.4 + (($loop->index % 5) * .19) }}s; --omc-bulb-delay: -{{ .15 + (($loop->index % 7) * .23) }}s;"
                >
                    <i class="omc-winter-light__stem"></i>
                    <i class="omc-winter-bulb"></i>
                </span>
            @endforeach
        </div>
    </div>

    <canvas id="omcSnowfall" class="omc-snowfall" aria-hidden="true"></canvas>

    <script>
        (() => {
            const garland = document.querySelector('.omc-winter-garland');
            let garlandObserver = null;

            const initializeGarlandClearance = () => {
                if (!garland) return;

                const syncGarlandClearance = () => {
                    const garlandHeight = Math.ceil(garland.getBoundingClientRect().height);
                    document.documentElement.style.setProperty(
                        '--omc-winter-reserve',
                        `${garlandHeight + 12}px`,
                    );
                };

                syncGarlandClearance();
                window.addEventListener('resize', syncGarlandClearance, { passive: true });

                if ('ResizeObserver' in window) {
                    garlandObserver = new ResizeObserver(syncGarlandClearance);
                    garlandObserver.observe(garland);
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeGarlandClearance, { once: true });
            } else {
                initializeGarlandClearance();
            }

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
            window.addEventListener('pagehide', () => {
                cancelAnimationFrame(animationFrame);
                garlandObserver?.disconnect();
            }, { once: true });
        })();
    </script>
@endonce
