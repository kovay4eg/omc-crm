<script>
document.addEventListener('livewire:init', () => {

    function update() {
        const form = document.querySelector('form');
        const btn = document.querySelector('button[type="submit"]');

        if (!form || !btn) return;

        if (form.querySelector('[wire\\:dirty]')) {
            btn.classList.add('pulse-save');
        } else {
            btn.classList.remove('pulse-save');
        }
    }

    setInterval(update, 500);

});
</script>

<style>
.pulse-save {
    animation: pulseSave 1s infinite;
}

@keyframes pulseSave {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>