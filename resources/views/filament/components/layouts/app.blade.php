<x-filament::layouts.app>
    {{ $slot }}

    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</x-filament::layouts.app>