<script>
document.addEventListener('alpine:init', () => {

    Alpine.store('formWatcher', {
        changed: false
    });

});

document.addEventListener('livewire:init', () => {

    console.log('LIVEWIRE INIT');

    function attach() {

        document.querySelectorAll('input, textarea, select').forEach(el => {

            el.addEventListener('change', () => {

                Alpine.store('formWatcher').changed = true;

                console.log('FORM CHANGED');

            });

        });

    }

    setTimeout(attach, 500);

    Livewire.hook('message.processed', () => {
        attach();
    });

});

window.addEventListener('form-changed', () => {
    Alpine.store('formWatcher').changed = true;
});
</script>