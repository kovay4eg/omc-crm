(() => {
    const communities = [
        { key: 'lokhvytska', label: 'Лохвицька громада', circleX: '523.78', order: 0, rayIndex: 0, rayClip: 'inset(0 100% 0 0)' },
        { key: 'martynivska', label: 'Мартинівська громада', circleX: '1213.25', order: 4, rayIndex: 1, rayClip: 'inset(0 0 0 100%)' },
        { key: 'novohaleshchynska', label: 'Новогалещинська громада', circleX: '663.36', order: 5, rayIndex: 2, rayClip: 'inset(100% 0 0 0)' },
        { key: 'horishnoplavnivska', label: 'Горішньоплавнівська громада', circleX: '904.48', order: 7, rayIndex: 7, rayClip: 'inset(100% 0 0 0)' },
        { key: 'pyriatynska', label: 'Пирятинська громада', circleX: '353.4', order: 1, rayIndex: 6, rayClip: 'inset(0 100% 0 0)' },
        { key: 'opishnianska', label: 'Опішнянська громада', circleX: '1031.79', order: 3, rayIndex: 5, rayClip: 'inset(0 0 0 100%)' },
        { key: 'kremenchutska', label: 'Кременчуцька громада', circleX: '706.51', order: 6, rayIndex: 3, rayClip: 'inset(100% 0 0 0)' },
        { key: 'kotelevska', label: 'Котелевська громада', circleX: '1093.19', order: 2, rayIndex: 4, rayClip: 'inset(0 0 0 100%)' },
    ];

    function initializeCommunityMap(block) {
        const svg = block.querySelector('[data-mobile-community-svg]');
        const panel = block.closest('.view-panel');

        if (!svg || !panel) {
            return;
        }

        const labelGroups = Array.from(svg.querySelectorAll(':scope > g'));
        const labelBoxes = Array.from(svg.querySelectorAll('rect.st0'));
        const points = Array.from(svg.querySelectorAll('circle.st1'));
        const regions = Array.from(svg.querySelectorAll('path.st2'));
        const leaderRays = Array.from(svg.querySelectorAll(':scope > path.st0')).slice(-communities.length);
        let replayFrame = null;

        function applyAnimationTiming(element, order) {
            const delay = step => `${(order * step).toFixed(2)}s`;

            element.style.setProperty('--label-delay-points', delay(.22));
            element.style.setProperty('--point-points-idle-delay', delay(.28));
        }

        regions.forEach(region => {
            region.classList.add('mobile-community-map__region');
        });

        communities.forEach((community, fileIndex) => {
            const labelGroup = labelGroups[fileIndex] ?? null;
            const labelBox = labelBoxes[fileIndex] ?? null;
            const point = points.find(candidate => candidate.getAttribute('cx') === community.circleX) ?? null;
            const ray = leaderRays[community.rayIndex] ?? null;
            const labelParts = [];

            if (labelGroup) {
                labelParts.push(labelGroup);
            }

            if (labelBox && !labelGroup?.contains(labelBox)) {
                labelParts.push(labelBox);
            }

            labelParts.forEach((part, partIndex) => {
                part.classList.add('mobile-community-map__label-part');
                part.dataset.communityLabel = community.key;
                applyAnimationTiming(part, community.order);
                part.setAttribute('aria-hidden', 'true');
                part.dataset.labelPart = String(partIndex + 1);
            });

            if (point) {
                point.classList.add('mobile-community-map__point');
                point.dataset.communityPoint = community.key;
                applyAnimationTiming(point, community.order);
            }

            if (ray) {
                ray.classList.add('mobile-community-map__ray');
                ray.dataset.communityRay = community.key;
                applyAnimationTiming(ray, community.order);
                ray.style.setProperty('--ray-clip-start', community.rayClip);
                ray.setAttribute('aria-hidden', 'true');
            }

            const region = findCommunityRegion(svg, regions, point);

            if (region) {
                region.dataset.communityRegion = community.key;
                applyAnimationTiming(region, community.order);
            }

        });

        function replayAnimation() {
            if (replayFrame !== null) {
                window.cancelAnimationFrame(replayFrame);
            }

            block.classList.remove('is-playing');
            void block.offsetWidth;

            replayFrame = window.requestAnimationFrame(() => {
                replayFrame = null;
                block.classList.add('is-playing');
            });
        }

        block.classList.add('is-enhanced');

        document.addEventListener('structure:view-opened', event => {
            if (event.detail?.viewId === panel.id) {
                replayAnimation();
            }
        });

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    block.classList.toggle('is-paused-offscreen', !entry.isIntersecting);
                });
            }, { threshold: .04 });

            observer.observe(block);
        }

        document.addEventListener('visibilitychange', () => {
            block.classList.toggle('is-paused-hidden', document.hidden);
        });

        if (panel.classList.contains('active')) {
            replayAnimation();
        }
    }

    function findCommunityRegion(svg, regions, point) {
        if (!point) {
            return null;
        }

        const svgPoint = svg.createSVGPoint();
        svgPoint.x = Number.parseFloat(point.getAttribute('cx'));
        svgPoint.y = Number.parseFloat(point.getAttribute('cy'));

        return regions.slice().reverse().find(region => {
            try {
                return typeof region.isPointInFill === 'function' && region.isPointInFill(svgPoint);
            } catch (error) {
                return false;
            }
        }) ?? null;
    }

    function initializeMaps() {
        const maps = document.querySelectorAll('[data-mobile-community-map]');

        maps.forEach(initializeCommunityMap);

        const previewRequested = new URLSearchParams(window.location.search).get('preview') === 'mobile-map';
        const localPreviewMap = document.querySelector('[data-mobile-community-map][data-local-preview="true"]');

        if (!previewRequested || !localPreviewMap) {
            return;
        }

        const mobileCard = document.querySelector('[aria-controls="mobile-view"]');

        if (mobileCard?.getAttribute('aria-expanded') !== 'true') {
            window.requestAnimationFrame(() => mobileCard?.click());
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMaps, { once: true });
    } else {
        initializeMaps();
    }
})();
