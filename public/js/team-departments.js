(() => {
    'use strict';
    const root = document.getElementById('team-section');
    if (!root || root.dataset.teamInitialized) return;
    root.dataset.teamInitialized = 'true';
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const carousels = new Map();

    root.querySelectorAll('[data-team-carousel]').forEach(element => {
        const slides = [...element.querySelectorAll('[data-slide]')];
        const dots = [...element.querySelectorAll('[data-slide-dot]')];
        const pause = element.querySelector('[data-carousel-pause]');
        let index = 0, timer = null, hover = false, focused = false, intersecting = false;
        let manualPause = motion.matches;

        const stop = () => { if (timer !== null) window.clearInterval(timer); timer = null; };
        const sync = () => {
            stop();
            const running = slides.length > 1 && !manualPause && !hover && !focused && intersecting
                && !document.hidden && !element.closest('[hidden]');
            element.setAttribute('aria-live', running ? 'off' : 'polite');
            if (pause) {
                pause.textContent = manualPause ? 'Відтворити' : 'Зупинити';
                pause.setAttribute('aria-pressed', String(manualPause));
            }
            if (running) timer = window.setInterval(() => show(index + 1), 5000);
        };
        const show = next => {
            if (!slides.length) return;
            index = (next + slides.length) % slides.length;
            element.dataset.currentIndex = String(index);
            slides.forEach((slide, position) => {
                let offset = (position - index + slides.length) % slides.length;
                if (offset > slides.length / 2) offset -= slides.length;
                const distance = Math.abs(offset);
                slide.style.setProperty('--slide-offset', offset);
                slide.style.setProperty('--slide-scale', distance ? Math.max(.64, 1 - distance * .15) : 1);
                slide.style.setProperty('--slide-opacity', distance > 2 ? 0 : distance ? .6 / distance : 1);
                slide.style.setProperty('--slide-z', 5 - distance);
                slide.style.visibility = distance > 2 ? 'hidden' : 'visible';
                slide.setAttribute('aria-current', String(position === index));
                slide.setAttribute('aria-hidden', String(position !== index));
            });
            dots.forEach((dot, position) => dot.setAttribute('aria-current', String(position === index)));
        };
        element.querySelector('[data-carousel-previous]')?.addEventListener('click', () => { show(index - 1); sync(); });
        element.querySelector('[data-carousel-next]')?.addEventListener('click', () => { show(index + 1); sync(); });
        dots.forEach((dot, position) => dot.addEventListener('click', () => { show(position); sync(); }));
        pause?.addEventListener('click', () => { manualPause = !manualPause; sync(); });
        element.addEventListener('mouseenter', () => { hover = true; sync(); });
        element.addEventListener('mouseleave', () => { hover = false; sync(); });
        element.addEventListener('focusin', () => { focused = true; sync(); });
        element.addEventListener('focusout', event => { focused = element.contains(event.relatedTarget); sync(); });
        document.addEventListener('visibilitychange', sync);
        const motionChanged = event => { manualPause = event.matches; sync(); };
        if (motion.addEventListener) motion.addEventListener('change', motionChanged);
        else motion.addListener?.(motionChanged);
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(entries => { intersecting = entries[0].isIntersecting; sync(); }, {threshold:.15}).observe(element);
        } else intersecting = true;
        let touch = null;
        element.querySelector('.department-carousel-stage')?.addEventListener('pointerdown', event => { touch = [event.clientX, event.clientY]; });
        element.querySelector('.department-carousel-stage')?.addEventListener('pointerup', event => {
            if (touch && Math.abs(event.clientX - touch[0]) > 45 && Math.abs(event.clientY - touch[1]) < 60) {
                show(index + (event.clientX < touch[0] ? 1 : -1)); sync();
            }
            touch = null;
        });
        element.querySelector('.department-carousel-stage')?.addEventListener('pointercancel', () => { touch = null; });
        carousels.set(element, {sync, reset: () => { stop(); manualPause = motion.matches; show(0); sync(); }});
        show(0); sync();
    });

    root.addEventListener('click', event => {
        const department = event.target.closest('[data-department-toggle]');
        if (department && root.contains(department)) {
            if (department.getAttribute('aria-expanded') === 'true') return;
            root.querySelectorAll('[data-department-toggle]').forEach(button => {
                button.setAttribute('aria-expanded', 'false');
                button.classList.remove('active');
            });
            root.querySelectorAll('[data-department-panel]').forEach(panel => { panel.hidden = true; });
            root.querySelectorAll('[data-department-section]').forEach(button => { button.setAttribute('aria-expanded', 'false'); });
            root.querySelectorAll('[data-department-content]').forEach(panel => { panel.hidden = true; });
            department.setAttribute('aria-expanded', 'true');
            department.classList.add('active');
            document.getElementById(department.getAttribute('aria-controls')).hidden = false;
            carousels.forEach(carousel => carousel.reset());
            return;
        }
        const section = event.target.closest('[data-department-section]');
        if (section && root.contains(section) && section.getAttribute('aria-expanded') !== 'true') {
            section.setAttribute('aria-expanded', 'true');
            document.getElementById(section.getAttribute('aria-controls')).hidden = false;
            carousels.forEach(carousel => carousel.sync());
        }
    });

    const background = root.querySelector('#heroBg');
    let pending = false;
    const decorate = () => {
        pending = false;
        if (motion.matches) return;
        if (background) background.style.transform = `translate(-50%, -50%) translateX(-${window.scrollY * .3}px)`;
        root.querySelectorAll('.dept-icon img').forEach(image => { image.style.transform = `rotate(${window.scrollY * .15}deg)`; });
    };
    window.addEventListener('scroll', () => { if (!pending) { pending = true; window.requestAnimationFrame(decorate); } }, {passive:true});
})();
