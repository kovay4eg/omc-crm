<link rel="stylesheet" href="/css/custom.css">


<script>
document.addEventListener('livewire:init', () => {

    function attachWatcher() {

        const btn = document.getElementById('save-btn');
        if (!btn) return;

        document.querySelectorAll('input, textarea, select').forEach(el => {

            el.addEventListener('input', () => {

                btn.classList.add('btn-pulse');

                console.log('FORM CHANGED');

            });

        });

    }

    setTimeout(attachWatcher, 500);

    Livewire.hook('message.processed', () => {
        attachWatcher();
    });

});

window.addEventListener('form-changed', () => {
    const btn = document.getElementById('save-btn');
    if (btn) {
        btn.classList.add('btn-pulse');
    }
});
</script>