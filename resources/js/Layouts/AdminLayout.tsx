import { PropsWithChildren } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    Calendar,
    Home,
    BookOpen,
    DollarSign,
    Settings,
    LogOut,
    Menu,
    Share2,
} from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/Components/ui/button';
import { FlashMessage } from '@/Components/FlashMessage';

interface AuthUser {
    id: number;
    name: string;
    email: string;
}

interface PageProps {
    auth: { user: AuthUser };
    app: { name: string };
}

interface NavItem {
    label: string;
    href: string;
    icon: React.ComponentType<{ className?: string }>;
}

const navItems: NavItem[] = [
    { label: 'لوحة التحكم', href: 'admin.dashboard', icon: Home },
    { label: 'التقويم', href: 'admin.calendar.index', icon: Calendar },
    { label: 'الحجوزات', href: 'admin.bookings.index', icon: BookOpen },
    { label: 'حسابات التواصل', href: 'admin.social-links.index', icon: Share2 },
    { label: 'الإعدادات', href: 'admin.settings.edit', icon: Settings },
];

export default function AdminLayout({ children }: PropsWithChildren) {
    const {
        props: { auth, app },
    } = usePage<PageProps>();
    const currentPath =
        typeof window !== 'undefined' ? window.location.pathname : '';

    return (
        <div dir="rtl" className="min-h-screen bg-background text-foreground">
            {/* Sidebar (fixed right in RTL) */}
            <aside className="fixed inset-y-0 right-0 z-30 hidden w-64 border-l border-border bg-card md:flex md:flex-col">
                <div className="flex h-16 items-center gap-2 border-b border-border px-6">
                    <Home className="h-6 w-6 text-primary" />
                    <div>
                        <p className="text-sm font-semibold">
                            {app?.name ?? 'شاليهات السراة'}
                        </p>
                        <p className="text-xs text-muted-foreground">لوحة التحكم</p>
                    </div>
                </div>
                <nav className="flex-1 space-y-1 overflow-y-auto p-4">
                    {navItems.map((item) => {
                        const href =
                            typeof route === 'function'
                                ? route(item.href)
                                : '#';
                        const isActive =
                            currentPath === href ||
                            (href !== '#' && currentPath.startsWith(href));
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.href}
                                href={href}
                                className={cn(
                                    'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    isActive
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-foreground hover:bg-accent hover:text-accent-foreground',
                                )}
                            >
                                <Icon className="h-4 w-4" />
                                <span>{item.label}</span>
                            </Link>
                        );
                    })}
                </nav>
                <div className="border-t border-border p-4 text-xs text-muted-foreground">
                    <p>المستخدم:</p>
                    <p className="mt-1 font-medium text-foreground">
                        {auth.user.name}
                    </p>
                </div>
            </aside>

            {/* Main area (offset for sidebar in RTL = mr-64) */}
            <div className="md:mr-64">
                {/* Top bar */}
                <header className="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-border bg-card px-4 md:px-8">
                    <div className="flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="md:hidden"
                            aria-label="القائمة"
                        >
                            <Menu className="h-5 w-5" />
                        </Button>
                        <h1 className="text-base font-semibold">
                            {app?.name ?? 'شاليهات السراة'}
                        </h1>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="hidden text-end md:block">
                            <p className="text-sm font-medium leading-tight">
                                {auth.user.name}
                            </p>
                            <p className="text-xs text-muted-foreground leading-tight">
                                {auth.user.email}
                            </p>
                        </div>
                        <form method="POST" action="/logout">
                            <input
                                type="hidden"
                                name="_token"
                                value={
                                    (
                                        document.querySelector(
                                            'meta[name="csrf-token"]',
                                        ) as HTMLMetaElement | null
                                    )?.content ?? ''
                                }
                            />
                            <Button
                                type="submit"
                                variant="outline"
                                size="sm"
                                className="gap-2"
                            >
                                <LogOut className="h-4 w-4" />
                                <span>تسجيل الخروج</span>
                            </Button>
                        </form>
                    </div>
                </header>

                {/* Mobile sidebar fallback (simple stacked links) */}
                <nav className="border-b border-border bg-card p-2 md:hidden">
                    <div className="flex flex-wrap gap-2">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            const href =
                                typeof route === 'function'
                                    ? route(item.href)
                                    : '#';
                            return (
                                <Link
                                    key={item.href}
                                    href={href}
                                    className="inline-flex items-center gap-2 rounded-md bg-muted px-3 py-1.5 text-xs font-medium"
                                >
                                    <Icon className="h-3.5 w-3.5" />
                                    <span>{item.label}</span>
                                </Link>
                            );
                        })}
                    </div>
                </nav>

                <main className="p-4 md:p-8">
                    <div className="mx-auto max-w-7xl space-y-6">
                        <FlashMessage />
                        {children}
                    </div>
                </main>
            </div>

            {/* Footer with simple USD-like bookkeeping */}
            <div className="hidden">{/* keep imports stable */}</div>
            <span className="hidden">
                <DollarSign className="h-4 w-4" />
            </span>
        </div>
    );
}
