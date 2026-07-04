import { PropsWithChildren } from 'react';
import { Head } from '@inertiajs/react';
import { Home } from 'lucide-react';

export default function AuthLayout({
    children,
}: PropsWithChildren) {
    return (
        <>
            <Head title="تسجيل الدخول" />
            <div
                dir="rtl"
                className="flex min-h-screen items-center justify-center bg-muted/50 p-4"
            >
                <div className="w-full max-w-md">
                    <div className="mb-8 flex flex-col items-center gap-2">
                        <div className="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-md">
                            <Home className="h-7 w-7" />
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            شاليهات السراة
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            لوحة إدارة الحجوزات
                        </p>
                    </div>
                    <div className="rounded-lg border border-border bg-card p-6 shadow-sm">
                        {children}
                    </div>
                    <p className="mt-6 text-center text-xs text-muted-foreground">
                        © {new Date().getFullYear()} شاليهات السراة — جميع الحقوق
                        محفوظة.
                    </p>
                </div>
            </div>
        </>
    );
}
