<style>
    :root { --omc-a11y-scale: 1; }
    html[data-omc-a11y-font='medium'] { --omc-a11y-scale: 1.12; }
    html[data-omc-a11y-font='large'] { --omc-a11y-scale: 1.25; }

    /* Масштабує весь інтерфейс, зокрема елементи з розміром у px. */
    @supports (zoom: 1) {
        html[data-omc-a11y-font='medium'] body,
        html[data-omc-a11y-font='large'] body { zoom: var(--omc-a11y-scale); }
    }
    @supports not (zoom: 1) {
        html[data-omc-a11y-font='medium'] { font-size: 112.5%; }
        html[data-omc-a11y-font='large'] { font-size: 125%; }
    }

    /* Модуль має лишатися у межах екрана, навіть коли весь сайт збільшено. */
    @supports (zoom: 1) {
        html[data-omc-a11y-font='medium'] .omc-a11y-widget { zoom: .892857; }
        html[data-omc-a11y-font='large'] .omc-a11y-widget { zoom: .8; }
    }

    /* Контрастні режими навмисно перефарбовують весь публічний вміст. */
    html[data-omc-a11y-theme='light'] body,
    html[data-omc-a11y-theme='light'] body :where(*, *::before, *::after) {
        color: #000 !important; background-color: #fff !important; background-image: none !important;
        border-color: #000 !important; box-shadow: none !important; text-shadow: none !important;
    }
    html[data-omc-a11y-theme='dark'] body,
    html[data-omc-a11y-theme='dark'] body :where(*, *::before, *::after) {
        color: #fff !important; background-color: #000 !important; background-image: none !important;
        border-color: #fff !important; box-shadow: none !important; text-shadow: none !important;
    }
    html[data-omc-a11y-theme='light'] body a { color: #001ee6 !important; }
    html[data-omc-a11y-theme='dark'] body a { color: #ffff00 !important; }
    html[data-omc-a11y-theme='light'] body a,
    html[data-omc-a11y-theme='dark'] body a { font-weight: 800 !important; text-decoration: underline !important; text-decoration-thickness: 2px !important; text-underline-offset: 3px !important; }
    html[data-omc-a11y-theme='light'] body img,
    html[data-omc-a11y-theme='dark'] body img { filter: grayscale(1) contrast(1.45) !important; }
    html[data-omc-a11y-theme='light'] body [aria-hidden='true'],
    html[data-omc-a11y-theme='dark'] body [aria-hidden='true'] { display: none !important; }

    /* Великі фонові слова є лише декором. У контрастному режимі вони не потрібні
       й можуть заважати читанню, тому основний текст лишається єдиним заголовком. */
    html[data-omc-a11y-theme='light'] :is(.team-hero-bg, .calendar-bg-text, .reports-bg-text, .statut-bg-text, .summary-watermark),
    html[data-omc-a11y-theme='dark'] :is(.team-hero-bg, .calendar-bg-text, .reports-bg-text, .statut-bg-text, .summary-watermark) {
        display: none !important;
    }
    html[data-omc-a11y-theme='light'] :focus-visible { outline: 4px solid #001ee6 !important; outline-offset: 4px !important; }
    html[data-omc-a11y-theme='dark'] :focus-visible { outline: 4px solid #ffff00 !important; outline-offset: 4px !important; }

    .omc-a11y-widget { position: fixed; right: 20px; bottom: 20px; z-index: 10000; font-family: Arial, sans-serif; }
    .omc-a11y-launcher { display: inline-flex; align-items: center; gap: 9px; min-height: 48px; padding: 10px 15px; border: 2px solid #2f36c9; border-radius: 999px; background: #fff; box-shadow: 0 8px 25px rgba(17,24,39,.2); color: #171b74; cursor: pointer; font: inherit; font-size: 14px; font-weight: 800; line-height: 1.15; animation: omc-a11y-attention 4s ease-in-out infinite; }
    .omc-a11y-launcher:hover { background: #eef0ff; }
    .omc-a11y-launcher:focus-visible { outline: 4px solid #f59e0b; outline-offset: 3px; }
    .omc-a11y-launcher svg { width: 22px; height: 22px; flex: 0 0 auto; }
    .omc-a11y-launcher[aria-expanded='true'], .omc-a11y-widget:hover .omc-a11y-launcher, .omc-a11y-widget:focus-within .omc-a11y-launcher { animation-play-state: paused; }
    @keyframes omc-a11y-attention { 0%, 10%, 20%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(17,24,39,.2); } 5%, 15% { transform: scale(1.055); box-shadow: 0 10px 31px rgba(47,54,201,.46); } }
    .omc-a11y-panel { position: absolute; right: 0; bottom: calc(100% + 12px); box-sizing: border-box; width: min(350px, calc(100vw - 32px)); max-height: calc(100dvh - 92px); overflow-y: auto; overscroll-behavior: contain; padding: 18px; border: 2px solid #2f36c9; border-radius: 18px; background: #fff; box-shadow: 0 18px 45px rgba(17,24,39,.25); color: #171717; }
    .omc-a11y-panel[hidden] { display: none !important; }
    .omc-a11y-panel__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .omc-a11y-panel__title { margin: 0; color: #181c79; font-size: 18px; font-weight: 800; line-height: 1.15; }
    .omc-a11y-panel__subtitle { margin: 5px 0 0; color: #55576c; font-size: 12px; line-height: 1.4; }
    .omc-a11y-close { display: grid; width: 32px; height: 32px; flex: 0 0 auto; place-items: center; border: 1px solid #aeb2e8; border-radius: 8px; background: #fff; color: #171b74; cursor: pointer; font-size: 22px; line-height: 1; }
    .omc-a11y-close:hover { background: #eef0ff; }
    .omc-a11y-group { margin-top: 17px; }
    .omc-a11y-label { display: block; margin-bottom: 8px; color: #20213c; font-size: 13px; font-weight: 800; }
    .omc-a11y-options { display: flex; flex-wrap: wrap; gap: 7px; }
    .omc-a11y-option { min-width: 44px; min-height: 40px; border: 1px solid #777ba2; border-radius: 8px; padding: 7px 10px; background: #fff; color: #16182e; cursor: pointer; font: inherit; font-weight: 800; }
    .omc-a11y-option:hover, .omc-a11y-option[aria-pressed='true'] { border-color: #2f36c9; background: #2f36c9; color: #fff; }
    .omc-a11y-option--font-medium { font-size: 17px; }
    .omc-a11y-option--font-large { font-size: 21px; }
    .omc-a11y-option--light { background: #fff; color: #000; }
    .omc-a11y-option--dark { background: #000; color: #fff; }
    .omc-a11y-reset { width: 100%; min-height: 42px; margin-top: 18px; border: 2px solid #2f36c9; border-radius: 9px; background: #fff; color: #2f36c9; cursor: pointer; font: inherit; font-size: 13px; font-weight: 800; }
    .omc-a11y-reset:hover { background: #eef0ff; }
    .omc-a11y-status { min-height: 16px; margin: 10px 0 0; color: #565a81; font-size: 11px; font-weight: 700; }

    html[data-omc-a11y-theme='light'] .omc-a11y-widget :where(button, p, h2, span),
    html[data-omc-a11y-theme='dark'] .omc-a11y-widget :where(button, p, h2, span) { font-family: Arial, sans-serif !important; }
    html[data-omc-a11y-theme='light'] .omc-a11y-widget button { color: #000 !important; background: #fff !important; border-color: #000 !important; }
    html[data-omc-a11y-theme='dark'] .omc-a11y-widget button { color: #fff !important; background: #000 !important; border-color: #fff !important; }
    html[data-omc-a11y-theme='light'] .omc-a11y-widget .omc-a11y-option[aria-pressed='true'] { color: #fff !important; background: #001ee6 !important; }
    html[data-omc-a11y-theme='dark'] .omc-a11y-widget .omc-a11y-option[aria-pressed='true'] { color: #000 !important; background: #ffff00 !important; }

    @media (prefers-reduced-motion: reduce) { .omc-a11y-launcher { animation: none !important; } }
    @media (max-width: 575px) { .omc-a11y-widget { right: 12px; bottom: 12px; } .omc-a11y-launcher { min-height: 45px; padding: 9px 12px; font-size: 12px; } .omc-a11y-panel { right: 0; width: min(350px, calc(100vw - 24px)); max-height: calc(100dvh - 76px); padding: 15px; } }
</style>

<div class="omc-a11y-widget" data-omc-a11y-widget>
    <section class="omc-a11y-panel" id="omcAccessibilityPanel" aria-labelledby="omcAccessibilityTitle" hidden>
        <div class="omc-a11y-panel__head">
            <div>
                <h2 class="omc-a11y-panel__title" id="omcAccessibilityTitle">Версія для зручного читання</h2>
                <p class="omc-a11y-panel__subtitle">Налаштування застосовуються до всього сайту.</p>
            </div>
            <button class="omc-a11y-close" type="button" data-omc-a11y-close aria-label="Закрити налаштування">×</button>
        </div>

        <div class="omc-a11y-group">
            <span class="omc-a11y-label">Розмір усього вмісту</span>
            <div class="omc-a11y-options" role="group" aria-label="Розмір вмісту">
                <button class="omc-a11y-option" type="button" data-omc-a11y-font="normal" aria-pressed="true">A</button>
                <button class="omc-a11y-option omc-a11y-option--font-medium" type="button" data-omc-a11y-font="medium" aria-pressed="false">A</button>
                <button class="omc-a11y-option omc-a11y-option--font-large" type="button" data-omc-a11y-font="large" aria-pressed="false">A</button>
            </div>
        </div>

        <div class="omc-a11y-group">
            <span class="omc-a11y-label">Контрастний колір сайту</span>
            <div class="omc-a11y-options" role="group" aria-label="Контрастний колір сайту">
                <button class="omc-a11y-option" type="button" data-omc-a11y-theme="normal" aria-pressed="true">Звичайний</button>
                <button class="omc-a11y-option omc-a11y-option--light" type="button" data-omc-a11y-theme="light" aria-pressed="false">Світлий</button>
                <button class="omc-a11y-option omc-a11y-option--dark" type="button" data-omc-a11y-theme="dark" aria-pressed="false">Чорний</button>
            </div>
        </div>

        <button class="omc-a11y-reset" type="button" data-omc-a11y-reset>Повернути звичайну версію</button>
        <p class="omc-a11y-status" data-omc-a11y-status aria-live="polite"></p>
    </section>

    <button class="omc-a11y-launcher" type="button" data-omc-a11y-toggle aria-expanded="false" aria-controls="omcAccessibilityPanel">
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/></svg>
        <span>Для людей з порушенням зору</span>
    </button>
</div>

<script>
    (() => {
        const storageKey = 'omc-accessibility-settings';
        const defaults = { font: 'normal', theme: 'normal' };
        const root = document.documentElement;

        function readSettings() {
            try {
                const stored = JSON.parse(window.localStorage.getItem(storageKey) || '{}');
                return { font: ['normal', 'medium', 'large'].includes(stored.font) ? stored.font : defaults.font, theme: ['normal', 'light', 'dark'].includes(stored.theme) ? stored.theme : defaults.theme };
            } catch (error) { return { ...defaults }; }
        }

        function applySettings(settings) {
            root.dataset.omcA11yFont = settings.font;
            root.dataset.omcA11yTheme = settings.theme;
            try { window.localStorage.setItem(storageKey, JSON.stringify(settings)); } catch (error) {}
            window.dispatchEvent(new CustomEvent('omc-accessibility-change', { detail: settings }));
        }

        applySettings(readSettings());

        document.addEventListener('DOMContentLoaded', () => {
            const widget = document.querySelector('[data-omc-a11y-widget]');
            if (!widget) return;

            const panel = widget.querySelector('#omcAccessibilityPanel');
            const toggle = widget.querySelector('[data-omc-a11y-toggle]');
            const close = widget.querySelector('[data-omc-a11y-close]');
            const reset = widget.querySelector('[data-omc-a11y-reset]');
            const status = widget.querySelector('[data-omc-a11y-status]');
            const getSettings = () => ({ font: root.dataset.omcA11yFont || defaults.font, theme: root.dataset.omcA11yTheme || defaults.theme });

            function updateButtons() {
                const settings = getSettings();
                widget.querySelectorAll('[data-omc-a11y-font]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.omcA11yFont === settings.font)));
                widget.querySelectorAll('[data-omc-a11y-theme]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.omcA11yTheme === settings.theme)));
            }
            function openPanel() { panel.hidden = false; toggle.setAttribute('aria-expanded', 'true'); updateButtons(); panel.querySelector('[data-omc-a11y-font]')?.focus(); }
            function closePanel() { panel.hidden = true; toggle.setAttribute('aria-expanded', 'false'); toggle.focus(); }
            function announce() {
                const { font, theme } = getSettings();
                const fontNames = { normal: 'звичайний', medium: 'збільшений', large: 'великий' };
                const themeNames = { normal: 'звичайний', light: 'світлий контрастний', dark: 'чорний контрастний' };
                status.textContent = `Розмір: ${fontNames[font]}. Колір: ${themeNames[theme]}.`;
            }

            toggle.addEventListener('click', () => panel.hidden ? openPanel() : closePanel());
            close.addEventListener('click', closePanel);
            widget.querySelectorAll('[data-omc-a11y-font]').forEach(button => button.addEventListener('click', () => { const settings = getSettings(); settings.font = button.dataset.omcA11yFont; applySettings(settings); updateButtons(); announce(); }));
            widget.querySelectorAll('[data-omc-a11y-theme]').forEach(button => button.addEventListener('click', () => { const settings = getSettings(); settings.theme = button.dataset.omcA11yTheme; applySettings(settings); updateButtons(); announce(); }));
            reset.addEventListener('click', () => { applySettings({ ...defaults }); updateButtons(); status.textContent = 'Увімкнено звичайну версію сайту.'; });
            document.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) closePanel(); });
            document.addEventListener('click', event => { if (!panel.hidden && !widget.contains(event.target)) closePanel(); });
            updateButtons();
        });
    })();
</script>
