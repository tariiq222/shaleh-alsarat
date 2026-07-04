<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Primary SEO --}}
    {{-- SEO title: keyword-rich for search ("شاليهات في النماص"); the displayed name is "شاليهات السراة" --}}
    <title>@yield('title', 'شاليهات في النماص | شاليهات السراة — حجز يومي ومناسبات')</title>
    <meta name="description" content="{{ $settings->description ?? 'شاليهات في النماص — شاليهات السراة، شاليهين مستقلين بتصميم عصري للإقامة اليومية والمناسبات الخاصة. غرف نوم ماستر، جلسات واسعة، فناء خارجي، وركن شواء. حجز يومي.' }}">
    <meta name="keywords" content="شاليهات في النماص, شاليهات السراة, شاليهات النماص, حجز شاليه, شاليهات عسير, إيجار شاليه يومي, مناسبات النماص, ركن شواء, إقامة عائلية, شاليهين مستقلين">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og:title', 'شاليهات السراة في النماص | حجز يومي')">
    <meta property="og:description" content="@yield('og:description', 'شاليهات في النماص — شاليهين مستقلين بتصميم عصري. غرف نوم ماستر، جلسات واسعة، فناء خارجي، وركن شواء.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:site_name" content="شاليهات السراة — النماص">
    @if($settings->photos->isNotEmpty())
        <meta property="og:image" content="{{ $settings->photos->first()->url }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter:title', 'شاليهات السراة في النماص')">
    <meta name="twitter:description" content="@yield('twitter:description', 'شاليهات في النماص — شاليهين للإقامة اليومية والمناسبات')">
    @if($settings->photos->isNotEmpty())
        <meta name="twitter:image" content="{{ $settings->photos->first()->url }}">
    @endif

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Theme / PWA --}}
    <meta name="theme-color" content="#0f766e">
    <meta name="format-detection" content="telephone=no">

    {{-- Fonts: IBM Plex Sans Arabic (primary) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Favicon (text-only placeholder, will be replaced with real .ico in production) --}}
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='64' height='64' rx='12' fill='%230f766e'/><text x='50%25' y='54%25' font-size='40' font-weight='700' text-anchor='middle' fill='white' font-family='system-ui' dominant-baseline='middle'>ش</text></svg>">

    {{-- Styles --}}
    @vite('resources/css/app.css')

    {{-- Schema.org LocalBusiness JSON-LD --}}
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $settings->name,
            'alternateName' => 'شاليهات في النماص',
            'description' => $settings->description,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'النماص',
                'addressRegion' => 'منطقة عسير',
                'addressCountry' => 'SA',
                'streetAddress' => $settings->location_text,
            ],
            'telephone' => $settings->whatsapp_number,
            'url' => url('/'),
            'image' => $settings->photos->first()?->url,
            'priceRange' => 'SAR',
            'amenityFeature' => $settings->feature_lines->map(fn ($f) => [
                '@type' => 'LocationFeatureSpecification',
                'name' => $f,
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    {{-- Floating WhatsApp button + smooth animations (inline so we don't need a separate CSS file) --}}
    <style>
        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .wa-fab, .wa-fab::before { animation: none !important; }
        }

        /* Floating WhatsApp button — pinned bottom-left in RTL is the visual end side */
        .wa-fab {
            position: fixed;
            left: 1rem;
            bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
            z-index: 60;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #25D366;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 9999px;
            box-shadow: 0 10px 25px -5px rgba(37, 211, 102, 0.5), 0 4px 6px -2px rgba(37, 211, 102, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease, padding 0.2s ease;
        }
        .wa-fab:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 15px 30px -5px rgba(37, 211, 102, 0.6), 0 6px 10px -2px rgba(37, 211, 102, 0.4); }
        .wa-fab:active { transform: translateY(0) scale(0.98); }
        .wa-fab:focus-visible { outline: 3px solid #25D366; outline-offset: 3px; }

        /* Pulse ring */
        .wa-fab::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: #25D366;
            z-index: -1;
            animation: wa-pulse 2.4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes wa-pulse {
            0%   { transform: scale(1);    opacity: 0.6; }
            70%  { transform: scale(1.6);  opacity: 0;   }
            100% { transform: scale(1.6);  opacity: 0;   }
        }

        /* Mobile: collapse to icon-only with smaller size */
        @media (max-width: 640px) {
            .wa-fab {
                left: auto;
                right: 1rem;
                padding: 0.875rem;
                border-radius: 9999px;
            }
            .wa-fab .wa-fab-label { display: none; }
            .wa-fab svg { width: 1.5rem; height: 1.5rem; }
        }

        /* On very small screens, position above the bottom nav safe area */
        @media (max-width: 380px) {
            .wa-fab { padding: 0.75rem; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-background text-foreground">
    @yield('content')

    {{-- Floating WhatsApp CTA — sticky on every scroll, hidden if no number set --}}
    @if($whatsapp_link)
        <a href="{{ $settings->whatsappLink('مرحبًا، أرغب بالاستفسار عن الحجز في ' . $settings->name) }}"
           target="_blank"
           rel="noopener"
           aria-label="تواصل معنا عبر واتساب"
           class="wa-fab">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 shrink-0" aria-hidden="true">
                <path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            <span class="wa-fab-label">تواصل واتساب</span>
        </a>
    @endif
</body>
</html>