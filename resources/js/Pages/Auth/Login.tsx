import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

import AuthLayout from '@/Layouts/AuthLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

interface PageProps {
    status?: string;
}

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
}

export default function Login() {
    const { status } = usePage<PageProps>().props;
    const { data, setData, post, processing, errors } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/login');
    }

    return (
        <AuthLayout>
            <Head title="تسجيل الدخول" />

            <div className="space-y-6">
                <div className="space-y-1">
                    <h2 className="text-xl font-semibold">أهلاً بك مجدداً</h2>
                    <p className="text-sm text-muted-foreground">
                        سجّل الدخول إلى حسابك للمتابعة.
                    </p>
                </div>

                {status && (
                    <div className="rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">
                        {status}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <div className="space-y-2">
                        <Label htmlFor="email">البريد الإلكتروني</Label>
                        <Input
                            id="email"
                            type="email"
                            autoComplete="email"
                            required
                            autoFocus
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            aria-invalid={!!errors.email}
                            disabled={processing}
                        />
                        {errors.email && (
                            <p className="text-sm text-destructive">
                                {errors.email}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password">كلمة المرور</Label>
                        <Input
                            id="password"
                            type="password"
                            autoComplete="current-password"
                            required
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                            aria-invalid={!!errors.password}
                            disabled={processing}
                        />
                        {errors.password && (
                            <p className="text-sm text-destructive">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    <label className="flex cursor-pointer items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            className="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />
                        <span>تذكرني</span>
                    </label>

                    <Button
                        type="submit"
                        className="w-full"
                        disabled={processing}
                    >
                        {processing ? 'جاري الدخول...' : 'تسجيل الدخول'}
                    </Button>
                </form>
            </div>
        </AuthLayout>
    );
}
