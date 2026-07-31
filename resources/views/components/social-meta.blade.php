@props([
    'title' => 'Обласний молодіжний центр Полтавської обласної ради',
    'description' => 'Молодіжні можливості, події та ініціативи Полтавщини.',
    'image' => null,
    'imageType' => null,
    'url' => null,
    'type' => 'website',
])

@php
    $brandName = 'ОМЦ';
    $baseTitle = trim($title) ?: 'Обласний молодіжний центр Полтавської обласної ради';
    $metaTitle = \Illuminate\Support\Str::contains($baseTitle, $brandName)
        ? $baseTitle
        : $baseTitle . ' — ' . $brandName;
    $metaDescription = \Illuminate\Support\Str::limit(
        \Illuminate\Support\Str::squish(strip_tags($description ?: 'Молодіжні можливості, події та ініціативи Полтавщини.')),
        200,
        '',
    );
    $metaUrl = $url ?: url()->current();
    $metaImage = null;

    if ($image) {
        $metaImage = \Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])
            ? $image
            : \App\Support\MediaUrl::storage($image);
    }
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="index,follow">
<link rel="canonical" href="{{ $metaUrl }}">

<meta property="og:locale" content="uk_UA">
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="Обласний молодіжний центр">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaUrl }}">

@if ($metaImage)
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:secure_url" content="{{ $metaImage }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
    @if ($imageType)
        <meta property="og:image:type" content="{{ $imageType }}">
    @endif
@endif

<meta name="twitter:card" content="{{ $metaImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
@if ($metaImage)
    <meta name="twitter:image" content="{{ $metaImage }}">
@endif
