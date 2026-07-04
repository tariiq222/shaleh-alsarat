<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Primary SEO --}}
    <title>@yield('title', $settings->name ?? 'شاليهات السراة')</title>
    <meta name="description" content="{{ $settings->description ?? 'احجز إقامتك في شاليهات السراة — استرخِ في أحضان الطبيعة.' }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og:title', $settings->name)">
    <meta property="og:description" content="@yield('og:description', $settings->description)">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:site_name" content="{{ $settings->name }}">
    @if($settings->photos->isNotEmpty())
        <meta property="og:image" content="{{ $settings->photos->first()->url }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter:title', $settings->name)">
    <meta name="twitter:description" content="@yield('twitter:description', $settings->description)">
    @if($settings->photos->isNotEmpty())
        <meta name="twitter:image" content="{{ $settings->photos->first()->url }}">
    @endif

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Theme / PWA --}}
    <meta name="theme-color" content="#0f766e">
    <meta name="format-detection" content="telephone=no">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Favicon (emoji placeholder) --}}
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='13' font-size='13'>🏔️</text></svg>">

    {{-- Styles --}}
    @vite('resources/css/app.css')

    {{-- Schema.org LocalBusiness JSON-LD --}}
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $settings->name,
            'description' => $settings->description,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->location_text,
            ],
            'telephone' => $settings->whatsapp_number,
            'url' => url('/'),
            'image' => $settings->photos->first()?->url,
            'priceRange' => 'SAR',
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body class="font-sans antialiased bg-background text-foreground">
    @yield('content')
</body>
</html>