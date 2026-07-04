<header class="sticky top-0 z-40 w-full border-b border-border bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/70">
    <nav class="container flex h-16 items-center justify-between gap-4" aria-label="القائمة الرئيسية">
        <a href="#top" class="flex items-center gap-2 text-base font-bold text-primary sm:text-lg">
            <span>{{ $settings->name }}</span>
        </a>

        <ul class="flex items-center gap-1 text-sm font-medium sm:gap-2">
            <li>
                <a href="#about" class="rounded-md px-2 py-2 text-foreground/80 transition hover:text-primary sm:px-3">نبذة</a>
            </li>
            <li class="hidden sm:block">
                <a href="#features" class="rounded-md px-2 py-2 text-foreground/80 transition hover:text-primary sm:px-3">المميزات</a>
            </li>
            <li class="hidden sm:block">
                <a href="#use-cases" class="rounded-md px-2 py-2 text-foreground/80 transition hover:text-primary sm:px-3">مناسب لـ</a>
            </li>
            <li>
                <a href="#gallery" class="rounded-md px-2 py-2 text-foreground/80 transition hover:text-primary sm:px-3">صور</a>
            </li>
            <li class="hidden sm:block">
                <a href="#pricing" class="rounded-md px-2 py-2 text-foreground/80 transition hover:text-primary sm:px-3">السعر</a>
            </li>
            <li class="hidden sm:block">
                <a href="#location" class="rounded-md px-2 py-2 text-foreground/80 transition hover:text-primary sm:px-3">الموقع</a>
            </li>
            <li>
                <a href="#booking" class="ml-1 inline-flex items-center justify-center rounded-md bg-primary px-3 py-2 text-primary-foreground transition hover:bg-primary/90 sm:ml-2">
                    احجز الآن
                </a>
            </li>
        </ul>
    </nav>
</header>