<x-filament::layouts.app>

    {{-- 🔥 PREVIEW BANNER --}}
    @include('filament.components.preview-banner')

    {{ $slot }}

    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    @include('filament.components.save-button-watcher')

</x-filament::layouts.app>