(() => {
    if (window.__bongobayLoaderInitialized) return;
    window.__bongobayLoaderInitialized = true;

    const loader = document.getElementById('bongobay-loader');
    const counter = document.getElementById('bongobay-loader-count');
    if (!loader || !counter) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const duration = reducedMotion ? 1 : 1200;
    let animationFrame;
    let fallbackTimer;
    let leaving = false;

    const finish = () => {
        if (leaving) return;
        leaving = true;
        cancelAnimationFrame(animationFrame);
        clearTimeout(fallbackTimer);
        counter.textContent = '100';
        loader.classList.remove('is-running');
        loader.classList.add('is-leaving');
        window.setTimeout(() => loader.classList.add('is-hidden'), reducedMotion ? 20 : 560);
    };

    const start = () => {
        let startTime;
        leaving = false;
        loader.className = 'bongobay-loader is-ready is-running';
        counter.textContent = '00';
        fallbackTimer = window.setTimeout(finish, 3200);

        const tick = (timestamp) => {
            startTime ??= timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            counter.textContent = String(Math.round(eased * 100)).padStart(2, '0');

            if (progress < 1) {
                animationFrame = requestAnimationFrame(tick);
            } else {
                finish();
            }
        };

        animationFrame = requestAnimationFrame(tick);
    };

    const isInternalNavigation = (anchor, event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
        if (anchor.target && anchor.target !== '_self') return false;
        if (anchor.hasAttribute('download') || anchor.closest('[data-no-loader]')) return false;
        if (anchor.dataset.method && anchor.dataset.method.toLowerCase() !== 'get') return false;

        const rawHref = (anchor.getAttribute('href') || '').trim();
        if (!rawHref || rawHref === '#' || rawHref.startsWith('#') || /^(mailto:|tel:|javascript:)/i.test(rawHref)) return false;
        if (/(^|\/)logout(?:\/?$|[?#])/i.test(rawHref) || anchor.matches('[data-logout]')) return false;

        const destination = new URL(anchor.href, window.location.href);
        if (destination.origin !== window.location.origin) return false;
        if (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) return false;

        return true;
    };

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a[href]');
        if (!anchor || !isInternalNavigation(anchor, event)) return;

        event.preventDefault();
        start();
        window.setTimeout(() => window.location.assign(anchor.href), reducedMotion ? 0 : 240);
    });

    window.addEventListener('pageshow', start, { once: true });
})();
