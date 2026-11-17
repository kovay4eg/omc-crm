(() => {
    'use strict';
    const root = document.getElementById('team-section');
    if (!root || root.dataset.teamInitialized) return;
    root.dataset.teamInitialized = 'true';
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const carousels = new Map();
    const photoLoads = new WeakMap();

    const preserveViewportAnchor = (element, mutate) => {
        const initialTop = element.getBoundingClientRect().top;
        const scrollingElement = document.scrollingElement || document.documentElement;

        mutate();
        element.focus({preventScroll: true});

        const restore = () => {
            const delta = element.getBoundingClientRect().top - initialTop;
            if (Math.abs(delta) < 1) return;

            const previousScrollBehavior = scrollingElement.style.scrollBehavior;
            scrollingElement.style.scrollBehavior = 'auto';
            window.scrollBy(0, delta);
            scrollingElement.style.scrollBehavior = previousScrollBehavior;
        };

        // Restore immediately after the synchronous accordion layout change,
        // then across two frames in case Safari applies focus/scroll anchoring
        // after the click handler has completed.
        restore();
        window.requestAnimationFrame(() => {
            restore();
            window.requestAnimationFrame(restore);
        });
    };

    const setPhotoReady = image => {
        const frame = image.closest('[data-team-photo-frame]');
        if (!frame) return;
        frame.classList.remove('is-photo-loading');
        frame.classList.add('is-photo-ready');
        frame.setAttribute('aria-busy', 'false');
    };

    const loadPhoto = (image, priority = 'low') => {
        if (photoLoads.has(image)) return photoLoads.get(image);

        image.loading = 'eager';
        image.setAttribute('fetchpriority', priority);
        image.closest('[data-team-photo-frame]')?.setAttribute('aria-busy', 'true');

        const promise = new Promise(resolve => {
            let finished = false;
            const finish = async () => {
                if (finished) return;
                finished = true;
                image.removeEventListener('load', onLoad);
                image.removeEventListener('error', onError);
                if (image.naturalWidth && typeof image.decode === 'function') {
                    await image.decode().catch(() => undefined);
                }
                setPhotoReady(image);
                resolve();
            };
            const onLoad = () => finish();
            const onError = () => {
                const fallback = image.dataset.fallbackSrc;
                if (fallback && image.dataset.fallbackUsed !== 'true') {
                    image.dataset.fallbackUsed = 'true';
                    image.src = fallback;
                    return;
                }
                finish();
            };

            image.addEventListener('load', onLoad);
            image.addEventListener('error', onError);

            if (image.complete) {
                window.queueMicrotask(image.naturalWidth ? onLoad : onError);
            }
        });

        photoLoads.set(image, promise);
        return promise;
    };

    const warmDepartment = (button, priority = 'low') => {
        const panel = document.getElementById(button?.getAttribute('aria-controls'));
        if (!panel) return Promise.resolve();
        return Promise.allSettled(
            [...panel.querySelectorAll('[data-team-photo]')]
                .map(image => loadPhoto(image, priority)),
        );
    };

    root.querySelectorAll('[data-team-photo][loading="eager"]').forEach(image => loadPhoto(image, 'high'));

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

    root.querySelectorAll('[data-innovation-projects-carousel]').forEach(element => {
        const viewport = element.querySelector('[data-innovation-viewport]');
        const slides = [...element.querySelectorAll('[data-innovation-slide]')];
        const previous = element.querySelector('[data-innovation-previous]');
        const next = element.querySelector('[data-innovation-next]');
        const dotsContainer = element.querySelector('[data-innovation-dots]');
        const counter = element.querySelector('[data-innovation-counter]');
        const lightbox = element.querySelector('[data-innovation-lightbox]');
        const lightboxImage = element.querySelector('[data-innovation-lightbox-image]');
        const lightboxCounter = element.querySelector('[data-innovation-lightbox-counter]');
        const lightboxClose = element.querySelector('[data-innovation-lightbox-close]');
        const lightboxPrevious = element.querySelector('[data-innovation-lightbox-previous]');
        const lightboxNext = element.querySelector('[data-innovation-lightbox-next]');
        let page = 0, lightboxIndex = 0, visibleCount = 2, scheduled = false, lastFocus = null;

        if (!viewport || !slides.length) return;

        const getVisibleCount = () => window.matchMedia('(max-width: 768px)').matches ? 1 : 2;
        const getPageCount = () => Math.max(1, Math.ceil(slides.length / visibleCount));
        const getPageOffset = targetPage => slides[Math.min(slides.length - 1, targetPage * visibleCount)]?.offsetLeft ?? 0;
        const nearestPage = () => {
            let bestPage = 0, bestDistance = Number.POSITIVE_INFINITY;
            for (let candidate = 0; candidate < getPageCount(); candidate += 1) {
                const distance = Math.abs(viewport.scrollLeft - getPageOffset(candidate));
                if (distance < bestDistance) { bestDistance = distance; bestPage = candidate; }
            }
            return bestPage;
        };
        const goToPage = targetPage => {
            page = Math.max(0, Math.min(targetPage, getPageCount() - 1));
            viewport.scrollTo({left:getPageOffset(page), behavior:motion.matches ? 'auto' : 'smooth'});
            scheduleUpdate();
        };
        const renderDots = () => {
            if (!dotsContainer) return;
            dotsContainer.replaceChildren();
            for (let dotIndex = 0; dotIndex < getPageCount(); dotIndex += 1) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'innovation-gallery__dot';
                dot.setAttribute('aria-label', `Перейти до групи фото ${dotIndex + 1}`);
                dot.addEventListener('click', () => goToPage(dotIndex));
                dotsContainer.append(dot);
            }
        };
        const update = () => {
            scheduled = false;
            const nextVisibleCount = getVisibleCount();
            if (visibleCount !== nextVisibleCount) {
                const preservedIndex = page * visibleCount;
                visibleCount = nextVisibleCount;
                page = Math.min(Math.floor(preservedIndex / visibleCount), getPageCount() - 1);
                renderDots();
                viewport.scrollLeft = getPageOffset(page);
            } else {
                page = nearestPage();
            }
            const dots = dotsContainer ? [...dotsContainer.children] : [];
            dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', String(dotIndex === page)));
            const activeDot = dots[page];
            if (activeDot && dotsContainer && dotsContainer.scrollWidth > dotsContainer.clientWidth) {
                const left = activeDot.offsetLeft - ((dotsContainer.clientWidth - activeDot.clientWidth) / 2);
                dotsContainer.scrollTo({left, behavior:motion.matches ? 'auto' : 'smooth'});
            }
            if (previous) previous.disabled = page <= 0;
            if (next) next.disabled = page >= getPageCount() - 1;
            if (counter) {
                const first = page * visibleCount + 1;
                const last = Math.min(slides.length, first + visibleCount - 1);
                counter.textContent = first === last ? `${first} / ${slides.length}` : `${first}–${last} / ${slides.length}`;
            }
        };
        const scheduleUpdate = () => {
            if (scheduled) return;
            scheduled = true;
            window.requestAnimationFrame(update);
        };
        const renderLightbox = () => {
            const image = slides[lightboxIndex]?.querySelector('img');
            if (!image || !lightboxImage) return;
            lightboxImage.src = image.currentSrc || image.src;
            lightboxImage.alt = image.alt;
            if (lightboxCounter) lightboxCounter.textContent = `${lightboxIndex + 1} / ${slides.length}`;
        };
        const openLightbox = targetIndex => {
            if (!lightbox || lightbox.open) return;
            lightboxIndex = targetIndex;
            lastFocus = document.activeElement;
            renderLightbox();
            lightbox.showModal();
            document.body.classList.add('innovation-lightbox-open');
            lightboxClose?.focus();
        };
        const closeLightbox = () => {
            if (!lightbox || !lightbox.open) return;
            lightbox.close();
        };
        const cleanupLightbox = () => {
            document.body.classList.remove('innovation-lightbox-open');
            if (lightboxImage) { lightboxImage.src = ''; lightboxImage.alt = ''; }
            lastFocus?.focus?.();
        };
        const moveLightbox = direction => {
            lightboxIndex = (lightboxIndex + direction + slides.length) % slides.length;
            renderLightbox();
        };

        previous?.addEventListener('click', () => goToPage(page - 1));
        next?.addEventListener('click', () => goToPage(page + 1));
        slides.forEach((slide, slideIndex) => slide.addEventListener('click', () => openLightbox(slideIndex)));
        viewport.addEventListener('scroll', scheduleUpdate, {passive:true});
        viewport.addEventListener('keydown', event => {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
            event.preventDefault();
            goToPage(page + (event.key === 'ArrowRight' ? 1 : -1));
        });
        window.addEventListener('resize', scheduleUpdate, {passive:true});
        lightboxClose?.addEventListener('click', closeLightbox);
        lightboxPrevious?.addEventListener('click', () => moveLightbox(-1));
        lightboxNext?.addEventListener('click', () => moveLightbox(1));
        lightbox?.addEventListener('click', event => { if (event.target === lightbox) closeLightbox(); });
        lightbox?.addEventListener('close', cleanupLightbox);
        document.addEventListener('keydown', event => {
            if (!lightbox || !lightbox.open) return;
            if (event.key === 'ArrowLeft') moveLightbox(-1);
            else if (event.key === 'ArrowRight') moveLightbox(1);
        });

        visibleCount = getVisibleCount();
        renderDots();
        update();
        carousels.set(element, {
            sync:scheduleUpdate,
            reset:() => { page = 0; viewport.scrollLeft = 0; closeLightbox(); scheduleUpdate(); },
        });
    });

    root.addEventListener('click', event => {
        const department = event.target.closest('[data-department-toggle]');
        if (department && root.contains(department)) {
            if (department.getAttribute('aria-expanded') === 'true') return;
            preserveViewportAnchor(department, () => {
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
            });
            warmDepartment(department, 'high');
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

    ['pointerover', 'focusin', 'touchstart'].forEach(eventName => {
        root.addEventListener(eventName, event => {
            const department = event.target.closest('[data-department-toggle]');
            if (department && root.contains(department)) warmDepartment(department, 'low');
        }, {passive: eventName === 'touchstart'});
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
