@php
    $mobileCommunityMapPath = public_path('images/structure/mobile/community-map.svg');
    $mobileCommunityMapSvg = is_file($mobileCommunityMapPath)
        ? file_get_contents($mobileCommunityMapPath)
        : '';

    $mobileCommunityMapSvg = preg_replace('/<\?xml.*?\?>\s*/s', '', $mobileCommunityMapSvg) ?? '';
    $mobileCommunityMapSvg = preg_replace('/<!--.*?-->\s*/s', '', $mobileCommunityMapSvg) ?? '';
    $mobileCommunityMapSvg = preg_replace('/<style>.*?<\/style>\s*/s', '', $mobileCommunityMapSvg, 1) ?? '';
    $mobileCommunityMapSvg = preg_replace(
        '/<svg\b/',
        '<svg class="mobile-community-map__svg" data-mobile-community-svg aria-hidden="true" focusable="false"',
        $mobileCommunityMapSvg,
        1,
    ) ?? '';
@endphp

@once
    <link rel="stylesheet" href="{{ asset('css/mobile-community-map.css') }}?v=20261117-8">
    <script src="{{ asset('js/mobile-community-map.js') }}?v=20261117-8" defer></script>
@endonce

<section
    class="mobile-community-map mb-5"
    data-mobile-community-map
    data-animation="points"
    data-map-source="images/structure/mobile/community-map.svg"
    data-local-preview="{{ app()->environment(['local', 'testing']) ? 'true' : 'false' }}"
    aria-labelledby="mobile-community-map-title"
>
    <h3
        id="mobile-community-map-title"
        class="mobile-community-map__title"
        data-mobile-community-map-title
    >
        Де ми були у 2026 році?
    </h3>

    <figure class="mobile-community-map__figure mb-0">
        <div
            class="mobile-community-map__stage"
            data-mobile-community-map-stage
            role="img"
            aria-labelledby="mobile-community-map-caption"
            aria-describedby="mobile-community-map-description"
        >
            <div class="mobile-community-map__motion">
                {!! $mobileCommunityMapSvg !!}
            </div>
        </div>

        <figcaption id="mobile-community-map-caption" class="visually-hidden">
            Карта громад, у яких працює команда мобільної молодіжної роботи.
        </figcaption>

        <p id="mobile-community-map-description" class="visually-hidden">
            Пирятинська, Лохвицька, Котелевська, Опішнянська, Мартинівська,
            Новогалещинська, Кременчуцька та Горішньоплавнівська громади.
        </p>
    </figure>

</section>
