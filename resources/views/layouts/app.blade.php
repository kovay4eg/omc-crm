<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-favicon />
    <title>@yield('title', 'OMC')</title>

    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f5f5f5;
            font-family: 'Commissioner', sans-serif;
        }
    </style>
</head>

<body>

    {{-- header --}}
    @include('components.header')

    {{-- content --}}
    @yield('content')

</body>
</html>
