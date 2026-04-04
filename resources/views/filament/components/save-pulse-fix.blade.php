<script>
document.addEventListener('livewire:init', () => {

    let changed = false;

    function markChanged() {
        changed = true;
    }

    function attachInputs() {
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.removeEventListener('input', markChanged);
            el.addEventListener('input', markChanged);
        });
    }

    function applyPulse() {
        const btn = document.querySelector('button[type="submit"]');
        if (!btn) return;

        if (changed) {
            btn.style.animation = 'pulseFix 1s infinite';
        } else {
            btn.style.animation = '';
        }
    }

    // 🔥 ГОЛОВНЕ — слідкуємо за перерендером
    const observer = new MutationObserver(() => {
        attachInputs();
        applyPulse();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    attachInputs();

});
</script>

<style>
@keyframes pulseFix {
    0% { transform: scale(1); }
    50% { transform: scale(1.07); }
    100% { transform: scale(1); }
}
</style>