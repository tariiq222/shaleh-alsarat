<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO defaults; Inertia pages can override via <Head> --}}
    <title inertia>{{ config('app.name', 'Chalet MVP') }}</title>
    <meta name="description" content="احجز إقامتك في شاليهات السراة — استرخِ في أحضان الطبيعة.">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:title" content="{{ config('app.name', 'Chalet MVP') }}">
    <meta property="og:site_name" content="شاليهات السراة">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name', 'Chalet MVP') }}">

    {{-- PWA / Theme --}}
    <meta name="theme-color" content="#0f766e">
    <meta name="format-detection" content="telephone=no">

    {{-- Fonts (Tajawal for Arabic UI) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Favicon (placeholder; replace with actual favicon) --}}
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='13' font-size='13'>🏔️</text></svg>">

    {{-- Scripts --}}
    @routes
    @viteReactRefresh
    @vite(['resources/css/app.css', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased h-full bg-background text-foreground">
    @inertia
</body>
</html>