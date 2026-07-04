@extends('public.layout')

@section('content')
    <a id="top"></a>

    @include('public.partials.header')

    {{-- 1. HERO --}}
    <section class="relative isolate flex min-h-[80vh] items-center justify-center overflow-hidden bg-primary/70 text-primary-foreground">
        @if($settings->photos->isNotEmpty())
            <div class="absolute inset-0 -z-10">
                <img src="{{ $settings->photos->first()->url }}" alt="{{ $settings->name }}" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-b from-primary/70 via-primary/55 to-primary/70 mix-blend-multiply"></div>
            </div>
        @endif

        <div class="container relative px-4 text-center sm:px-6">
            {{-- Glass card wrapping title + description for better readability --}}
            <div class="mx-auto max-w-3xl rounded-3xl border border-white/20 bg-white/10 px-6 py-8 shadow-2xl backdrop-blur-md sm:px-10 sm:py-10">
                <h1 class="text-3xl font-extrabold leading-tight drop-shadow-md sm:text-4xl md:text-5xl lg:text-6xl">
                    {{ $settings->name }} في {{ 'النماص' }}
                </h1>

                <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-primary-foreground/95 sm:mt-6 sm:text-lg md:text-xl">
                    {{ $settings->description }}
                </p>
            </div>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:mt-10 sm:flex-row sm:gap-4">
                @if($settings->phoneLink())
                    <a href="{{ $settings->phoneLink() }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-white/10 px-6 py-3 text-base font-bold text-primary-foreground ring-1 ring-inset ring-primary-foreground/30 backdrop-blur transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-primary-foreground focus:ring-offset-2 focus:ring-offset-primary sm:w-auto"
                       aria-label="اتصل بنا على {{ $settings->phone_number }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        اتصل بنا
                    </a>
                @endif

                @if($whatsapp_link)
                    <a href="{{ $settings->whatsappLink('مرحبًا، أرغب بالاستفسار عن حجز شاليهات السراة في النماص') }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#25D366] px-6 py-3 text-base font-bold text-white shadow-lg transition hover:bg-[#1ebe57] focus:outline-none focus:ring-2 focus:ring-[#25D366] focus:ring-offset-2 focus:ring-offset-primary sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                            <path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        تواصل واتساب
                    </a>
                @endif
            </div>
        </div>

        <a href="#about" aria-label="مرر للأسفل" class="absolute bottom-6 left-1/2 -translate-x-1/2 animate-bounce text-primary-foreground/80 transition hover:text-primary-foreground">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </a>
    </section>

    {{-- 2. ABOUT (نبذة عن الشاليه) --}}
    <section id="about" class="container scroll-mt-20 py-16 sm:py-20">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">عن {{ $settings->name }}</h2>
            <p class="mt-6 text-lg leading-loose text-foreground/80">
                {{ $settings->description }}
            </p>
        </div>
    </section>

    {{-- 3. FEATURES (المميزات) --}}
    @if($settings->feature_lines->isNotEmpty())
        <section id="features" class="bg-muted/40 scroll-mt-20 py-16 sm:py-20">
            <div class="container">
                <header class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-foreground sm:text-4xl">مميزات {{ $settings->name }}</h2>
                    <p class="mt-3 text-muted-foreground">كل ما تحتاجه لإقامة مريحة أو مناسبة لا تُنسى</p>
                </header>

                <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" role="list">
                    @foreach($settings->feature_lines as $feature)
                        <li class="flex items-start gap-3 rounded-lg border border-border bg-card p-5 shadow-sm transition hover:shadow-md">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span class="text-base font-medium text-foreground">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- 4. USE CASES (مناسب لـ / المناسبات) --}}
    <section id="use-cases" class="container scroll-mt-20 py-16 sm:py-20">
        <div class="mx-auto max-w-3xl text-center">
            <span class="inline-block rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">مناسب لـ</span>
            <h2 class="mt-4 text-3xl font-bold text-foreground sm:text-4xl">مناسب لمناسباتك الخاصة</h2>
            <p class="mt-5 text-lg leading-loose text-foreground/80">
                {{ $settings->name }} مناسب للتجمعات العائلية والمناسبات الخاصة بعدد يصل إلى {{ (int) $settings->max_capacity }} شخص تقريبًا، مع جلسات واسعة وفناء خارجي وركن شواء.
            </p>

            <ul class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4" role="list">
                <li class="flex flex-col items-center gap-2 rounded-lg border border-border bg-card p-4 text-center">
                    <span class="text-2xl" aria-hidden="true">👨‍👩‍👧‍👦</span>
                    <span class="text-sm font-medium text-foreground">إقامة عائلية قصيرة</span>
                </li>
                <li class="flex flex-col items-center gap-2 rounded-lg border border-border bg-card p-4 text-center">
                    <span class="text-2xl" aria-hidden="true">🛋️</span>
                    <span class="text-sm font-medium text-foreground">جلسات هادئة مع الأهل</span>
                </li>
                <li class="flex flex-col items-center gap-2 rounded-lg border border-border bg-card p-4 text-center">
                    <span class="text-2xl" aria-hidden="true">🍽️</span>
                    <span class="text-sm font-medium text-foreground">ضيافة خاصة</span>
                </li>
                <li class="flex flex-col items-center gap-2 rounded-lg border border-border bg-card p-4 text-center">
                    <span class="text-2xl" aria-hidden="true">🎉</span>
                    <span class="text-sm font-medium text-foreground">مناسبات بسيطة</span>
                </li>
            </ul>
        </div>
    </section>

    {{-- 5. GALLERY (معرض الصور) --}}
    <section id="gallery" class="bg-muted/40 scroll-mt-20 py-16 sm:py-20">
        <div class="container">
            <header class="mb-10 text-center">
                <h2 class="text-3xl font-bold text-foreground sm:text-4xl">صور من الشاليه</h2>
                <p class="mt-3 text-muted-foreground">الجلسات الداخلية، الفناء الخارجي، غرف النوم، المطبخ، ركن الشواء، والمداخل الخارجية</p>
            </header>

            @if($settings->photos->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                    @foreach($settings->photos as $photo)
                        <figure class="overflow-hidden rounded-lg border border-border bg-muted">
                            <button type="button"
                                    class="group block h-full w-full cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                    data-lightbox-src="{{ $photo->url }}"
                                    data-lightbox-caption="{{ $photo->caption ?? $settings->name }}"
                                    onclick="openLightbox(this.dataset.lightboxSrc, this.dataset.lightboxCaption)"
                                    aria-label="تكبير الصورة: {{ $photo->caption ?? $settings->name }}">
                                <img src="{{ $photo->url }}"
                                     alt="{{ $settings->name }}{{ $photo->caption ? ' — ' . $photo->caption : '' }}"
                                     loading="lazy"
                                     class="aspect-square h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            </button>
                        </figure>
                    @endforeach
                </div>
            @else
                <p class="text-center text-muted-foreground">لم تُضف صور بعد</p>
            @endif
        </div>
    </section>

    {{-- 6. PRICING & CAPACITY (السعر والحجز) --}}
    <section id="pricing" class="container scroll-mt-20 py-16 sm:py-20">
        <header class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">السعر والحجز</h2>
            <p class="mt-3 text-muted-foreground">حجز يومي مع تسعير واضح وشفاف</p>
        </header>

        <div class="mx-auto grid max-w-4xl grid-cols-1 gap-5 sm:grid-cols-3">
            <article class="rounded-2xl border-2 border-border bg-card p-7 text-center shadow-sm transition hover:shadow-md">
                <h3 class="text-lg font-semibold text-muted-foreground">السعر الموحد</h3>
                <p class="mt-3 text-5xl font-extrabold text-primary">
                    {{ number_format((float) $settings->weekday_price, 0) }}
                    <span class="text-xl font-medium text-muted-foreground">ريال</span>
                </p>
                <p class="mt-3 text-sm text-muted-foreground">لليلة الواحدة — حجز يومي</p>
            </article>

            <article class="relative rounded-2xl border-2 border-primary bg-card p-7 text-center shadow-md">
                <span class="absolute -top-3 right-1/2 translate-x-1/2 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground">الأساسية</span>
                <h3 class="text-lg font-semibold text-muted-foreground">السعة القصوى</h3>
                <p class="mt-3 text-5xl font-extrabold text-primary">
                    {{ (int) $settings->max_capacity }}
                    <span class="text-xl font-medium text-muted-foreground">شخص</span>
                </p>
                <p class="mt-3 text-sm text-muted-foreground">مناسب للعائلات والمناسبات</p>
            </article>

            <article class="rounded-2xl border-2 border-border bg-card p-7 text-center shadow-sm transition hover:shadow-md">
                <h3 class="text-lg font-semibold text-muted-foreground">السعر الأقصى</h3>
                <p class="mt-3 text-5xl font-extrabold text-primary">
                    {{ number_format((float) $settings->weekend_price, 0) }}
                    <span class="text-xl font-medium text-muted-foreground">ريال</span>
                </p>
                <p class="mt-3 text-sm text-muted-foreground">في المواسم والمناسبات الخاصة</p>
            </article>
        </div>

        <p class="mt-8 text-center text-sm text-muted-foreground">
            السعر قابل للتأكيد من الإدارة. تسجيل الدخول من <span class="font-semibold text-foreground">{{ $settings->check_in_time }}</span>، وتسجيل الخروج حتى <span class="font-semibold text-foreground">{{ $settings->check_out_time }}</span>.
        </p>
    </section>

    {{-- 7. LOCATION (الموقع) --}}
    @if($settings->location_text || $settings->map_url)
        <section id="location" class="bg-muted/40 scroll-mt-20 py-16 sm:py-20">
            <div class="container">
                <header class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-foreground sm:text-4xl">الموقع</h2>
                    <p class="mt-3 text-muted-foreground">كيف تصل إلينا</p>
                </header>

                <div class="mx-auto max-w-2xl rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
                    @if($settings->location_text)
                        <p class="text-lg leading-relaxed text-foreground">{{ $settings->location_text }}</p>
                    @endif

                    @if($settings->map_url)
                        <a href="{{ $settings->map_url }}"
                           target="_blank"
                           rel="noopener"
                           class="mt-6 inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-base font-semibold text-primary-foreground shadow transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            فتح في خرائط جوجل
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- 8. BOOKING (احجز يومك) --}}
    <section id="booking" class="container scroll-mt-20 py-16 sm:py-20">
        <div class="mx-auto max-w-2xl text-center">
            <span class="inline-block rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">حجز يومي</span>
            <h2 class="mt-4 text-3xl font-bold text-foreground sm:text-4xl">احجز يومك في {{ $settings->name }}</h2>
            <p class="mt-5 text-lg leading-loose text-foreground/80">
                للاستفسار عن التوفر والأسعار، تواصل معنا عبر الواتساب أو اتصل بنا مباشرة.
            </p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                @if($whatsapp_link)
                    <a href="{{ $settings->whatsappLink('مرحبًا، أرغب بحجز يوم في شاليهات السراة في النماص. هل من توفر؟') }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex w-full items-center justify-center gap-3 rounded-lg bg-[#25D366] px-8 py-4 text-lg font-bold text-white shadow-lg transition hover:bg-[#1ebe57] focus:outline-none focus:ring-2 focus:ring-[#25D366] focus:ring-offset-2 sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6" aria-hidden="true">
                            <path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        تواصل عبر واتساب
                    </a>
                @endif

                @if($settings->phoneLink())
                    <a href="{{ $settings->phoneLink() }}"
                       class="inline-flex w-full items-center justify-center gap-3 rounded-lg border-2 border-primary bg-card px-8 py-4 text-lg font-bold text-primary shadow-sm transition hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto"
                       aria-label="اتصل بنا على {{ $settings->phone_number }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        اتصل بنا
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- 7. SOCIAL LINKS (حسابات إضافية) --}}
    @if($social_links->isNotEmpty())
        <section id="social" class="bg-muted/40 scroll-mt-20 py-12 sm:py-16">
            <div class="container">
                <header class="mb-8 text-center">
                    <h2 class="text-2xl font-bold text-foreground sm:text-3xl">تابعنا على منصاتنا</h2>
                </header>
                <ul class="flex flex-wrap items-center justify-center gap-4 sm:gap-6" role="list">
                    @foreach($social_links as $link)
                        @php $brand = $link->brand; @endphp
                        <li>
                            <a href="{{ $link->url }}"
                               target="_blank"
                               rel="noopener"
                               aria-label="{{ $link->name }}{{ $link->handle ? ' — ' . $link->handle : '' }}"
                               class="group flex items-center gap-2 rounded-full border border-border bg-card px-4 py-2 transition hover:-translate-y-0.5 hover:shadow-md">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-white"
                                      style="background-color: {{ $brand['color'] }};">
                                    <span class="h-4 w-4 [&>svg]:h-full [&>svg]:w-full">{!! $brand['svg'] !!}</span>
                                </span>
                                <span class="text-sm font-medium text-foreground">{{ $link->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @include('public.partials.footer')

    {{-- LIGHTBOX MODAL — opens at full size when a gallery image is clicked --}}
    <div id="lightbox"
         role="dialog"
         aria-modal="true"
         aria-label="معرض الصور"
         class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/90 p-2 backdrop-blur-sm sm:p-4"
         onclick="closeLightbox(event)">
        <button type="button"
                onclick="closeLightbox()"
                aria-label="إغلاق"
                class="absolute right-3 top-3 z-20 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-3xl font-bold text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white sm:right-6 sm:top-6">
            &times;
        </button>
        {{-- Scrollable container: on mobile the image can extend past the viewport,
             letting the user drag/swipe horizontally & vertically to see all of it --}}
        <figure id="lightbox-figure"
                class="flex max-h-[90vh] max-w-[95vw] flex-col items-center overflow-auto rounded-lg sm:max-w-[90vw] sm:overflow-visible"
                style="-webkit-overflow-scrolling: touch;"
                onclick="event.stopPropagation()">
            <img id="lightbox-img"
                 src=""
                 alt=""
                 draggable="false"
                 class="w-auto max-w-none touch-none rounded-lg object-contain shadow-2xl sm:max-h-[80vh] sm:max-w-full"
                 style="-webkit-user-drag: none; user-select: none;">
            <figcaption id="lightbox-caption"
                        class="mt-4 max-w-2xl shrink-0 text-center text-base font-medium text-white/90"
                        dir="rtl"></figcaption>
        </figure>
    </div>

    <script>
        (function () {
            const lb = document.getElementById('lightbox');
            const lbImg = document.getElementById('lightbox-img');
            const lbCap = document.getElementById('lightbox-caption');
            const body = document.body;

            // Exposed to global so the inline onclick="openLightbox(...)" can call it.
            window.openLightbox = function (src, caption) {
                lbImg.src = src;
                lbImg.alt = caption || '';
                lbCap.textContent = caption || '';
                lb.classList.remove('hidden');
                lb.classList.add('flex');
                body.style.overflow = 'hidden';
            };

            window.closeLightbox = function (event) {
                // Allow Esc key and clicks on the backdrop (not the image itself).
                if (event && event.target.closest('figure')) return;
                lb.classList.add('hidden');
                lb.classList.remove('flex');
                lbImg.src = '';
                body.style.overflow = '';
            };

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeLightbox();
            });
        })();
    </script>
@endsection