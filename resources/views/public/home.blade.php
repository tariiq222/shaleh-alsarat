@extends('public.layout')

@section('content')
    <a id="top"></a>

    @include('public.partials.header')

    {{-- Hero --}}
    <section class="relative isolate flex min-h-[80vh] items-center justify-center overflow-hidden bg-gradient-to-br from-primary via-primary to-primary/80 text-primary-foreground">
        @if($settings->photos->isNotEmpty())
            <div class="absolute inset-0 -z-10">
                <img src="{{ $settings->photos->first()->url }}" alt="{{ $settings->name }}" class="h-full w-full object-cover opacity-40">
                <div class="absolute inset-0 bg-gradient-to-b from-primary/60 via-primary/40 to-primary/80"></div>
            </div>
        @endif

        <div class="container relative text-center">
            <h1 class="text-4xl font-extrabold leading-tight drop-shadow-md sm:text-5xl md:text-6xl">
                {{ $settings->name }}
            </h1>

            @if($settings->description)
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-primary-foreground/90 sm:text-xl">
                    {{ $settings->description }}
                </p>
            @endif

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                @if($whatsapp_link)
                    <a href="{{ $settings->whatsappLink('مرحبًا، أرغب بالاستفسار عن الحجز') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-lg bg-[#25D366] px-6 py-3 text-base font-semibold text-white shadow-lg transition hover:bg-[#1ebe57] focus:outline-none focus:ring-2 focus:ring-[#25D366] focus:ring-offset-2 focus:ring-offset-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                            <path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        تواصل عبر واتساب
                    </a>
                @endif

                <a href="#booking-form"
                   class="inline-flex items-center justify-center rounded-lg border border-primary-foreground/30 bg-primary-foreground/10 px-6 py-3 text-base font-semibold text-primary-foreground backdrop-blur transition hover:bg-primary-foreground/20 focus:outline-none focus:ring-2 focus:ring-primary-foreground focus:ring-offset-2 focus:ring-offset-primary">
                    طلب حجز
                </a>
            </div>
        </div>

        <a href="#gallery" aria-label="مرر للأسفل" class="absolute bottom-6 left-1/2 -translate-x-1/2 animate-bounce text-primary-foreground/70 transition hover:text-primary-foreground">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </a>
    </section>

    {{-- Gallery --}}
    <section id="gallery" class="container scroll-mt-20 py-16 sm:py-20">
        <header class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">صور الشاليه</h2>
            <p class="mt-3 text-muted-foreground">تجوّل في أرجاء المكان قبل الحجز</p>
        </header>

        @if($settings->photos->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                @foreach($settings->photos as $photo)
                    <figure class="overflow-hidden rounded-lg border border-border bg-muted">
                        <img src="{{ $photo->url }}"
                             alt="{{ $settings->name }}{{ $photo->caption ? ' — ' . $photo->caption : '' }}"
                             loading="lazy"
                             class="aspect-square h-full w-full object-cover transition duration-500 hover:scale-105">
                    </figure>
                @endforeach
            </div>
        @else
            <p class="text-center text-muted-foreground">لم تُضف صور بعد</p>
        @endif
    </section>

    {{-- Features --}}
    @if($settings->featuresList->isNotEmpty())
        <section id="features" class="bg-muted/40 scroll-mt-20 py-16 sm:py-20">
            <div class="container">
                <header class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-foreground sm:text-4xl">مميزات الشاليه</h2>
                    <p class="mt-3 text-muted-foreground">كل ما تحتاجه لإقامة مريحة</p>
                </header>

                <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($settings->featuresList as $feature)
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

    {{-- Pricing --}}
    <section id="prices" class="container scroll-mt-20 py-16 sm:py-20">
        <header class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">الأسعار</h2>
            <p class="mt-3 text-muted-foreground">أسعار شفافة لكل أنواع الإقامات</p>
        </header>

        <div class="mx-auto grid max-w-3xl grid-cols-1 gap-5 sm:grid-cols-2">
            <article class="rounded-2xl border border-border bg-card p-7 text-center shadow-sm transition hover:shadow-md">
                <h3 class="text-lg font-semibold text-muted-foreground">أيام الأسبوع</h3>
                <p class="mt-3 text-4xl font-extrabold text-primary">
                    {{ number_format((float) $settings->weekday_price, 0) }}
                    <span class="text-xl font-medium text-muted-foreground">ريال / ليلة</span>
                </p>
            </article>

            <article class="relative rounded-2xl border-2 border-primary bg-card p-7 text-center shadow-md">
                <span class="absolute -top-3 right-1/2 translate-x-1/2 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground">الأكثر طلباً</span>
                <h3 class="text-lg font-semibold text-muted-foreground">نهاية الأسبوع</h3>
                <p class="mt-3 text-4xl font-extrabold text-primary">
                    {{ number_format((float) $settings->weekend_price, 0) }}
                    <span class="text-xl font-medium text-muted-foreground">ريال / ليلة</span>
                </p>
            </article>
        </div>

        <p class="mt-8 text-center text-sm text-muted-foreground">
            السعر قابل للتأكيد من الإدارة. تسجيل الدخول من <span class="font-semibold text-foreground">{{ $settings->check_in_time }}</span>، وتسجيل الخروج حتى <span class="font-semibold text-foreground">{{ $settings->check_out_time }}</span>.
        </p>
    </section>

    {{-- Location --}}
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
                        <a href="{{ $settings->map_url }}" target="_blank" rel="noopener"
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

    {{-- Booking Form --}}
    <section id="booking-form" class="container scroll-mt-20 py-16 sm:py-20">
        <div class="mx-auto max-w-2xl">
            <header class="mb-8 text-center">
                <h2 class="text-3xl font-bold text-foreground sm:text-4xl">طلب حجز</h2>
                <p class="mt-3 text-muted-foreground">املأ النموذج وسيتم التواصل معك لتأكيد التوفر</p>
            </header>

            @if(session('success'))
                <div role="status" class="mb-6 flex items-start gap-3 rounded-lg border border-green-300 bg-green-50 p-4 text-green-800">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <form action="{{ route('public.inquiries.store') }}" method="POST" class="space-y-5 rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8" novalidate>
                @csrf

                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-foreground">الاسم الكامل <span class="text-destructive">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                           autocomplete="name"
                           class="block w-full rounded-md border border-input bg-background px-3 py-2.5 text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-ring @error('name') border-destructive @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-foreground">رقم الجوال <span class="text-destructive">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required maxlength="32"
                           inputmode="tel" autocomplete="tel"
                           placeholder="05XXXXXXXX"
                           class="block w-full rounded-md border border-input bg-background px-3 py-2.5 text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-ring @error('phone') border-destructive @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="preferred_date" class="mb-1.5 block text-sm font-medium text-foreground">التاريخ المفضل</label>
                    <input type="date" id="preferred_date" name="preferred_date" value="{{ old('preferred_date') }}"
                           min="{{ date('Y-m-d') }}"
                           class="block w-full rounded-md border border-input bg-background px-3 py-2.5 text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-ring @error('preferred_date') border-destructive @enderror">
                    @error('preferred_date')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="mb-1.5 block text-sm font-medium text-foreground">رسالتك</label>
                    <textarea id="message" name="message" rows="4" maxlength="1000"
                              placeholder="عدد الأيام، عدد الأشخاص، أي طلبات خاصة..."
                              class="block w-full rounded-md border border-input bg-background px-3 py-2.5 text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-ring @error('message') border-destructive @enderror">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-primary px-6 py-3 text-base font-semibold text-primary-foreground shadow transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-60">
                    إرسال الطلب
                </button>
            </form>
        </div>
    </section>

    @include('public.partials.footer')
@endsection