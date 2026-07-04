import { useEffect, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { CalendarOff } from 'lucide-react';

interface BlockedDate {
    id: number;
    start_date: string;
    end_date: string;
    reason: string | null;
}

interface BookingCreateProps {
    blocked_dates: BlockedDate[];
    weekday_price: number;
    weekend_price: number;
}

interface BookingForm {
    customer_name: string;
    customer_phone: string;
    start_date: string;
    end_date: string;
    total_amount: string;
    deposit_amount: string;
    booking_status: string;
    notes: string;
}

function getQueryParam(name: string): string | undefined {
    if (typeof window === 'undefined') return undefined;
    const params = new URLSearchParams(window.location.search);
    return params.get(name) ?? undefined;
}

export default function BookingsCreate({
    blocked_dates,
}: BookingCreateProps) {
    const initialStart = getQueryParam('start_date') ?? '';
    const initialEnd = getQueryParam('end_date') ?? initialStart;

    const { data, setData, post, processing, errors } = useForm<BookingForm>({
        customer_name: '',
        customer_phone: '',
        start_date: initialStart,
        end_date: initialEnd,
        total_amount: '',
        deposit_amount: '',
        booking_status: 'pending',
        notes: '',
    });

    const [warning, setWarning] = useState<string | null>(null);

    useEffect(() => {
        if (!data.start_date || !data.end_date) {
            setWarning(null);
            return;
        }
        const overlaps = blocked_dates.some((b) => {
            return !(
                b.end_date < data.start_date || b.start_date > data.end_date
            );
        });
        if (overlaps) {
            setWarning('هذه الفترة تتداخل مع فترة حظر مسجلة.');
        } else {
            setWarning(null);
        }
    }, [data.start_date, data.end_date, blocked_dates]);

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('admin.bookings.store'));
    }

    return (
        <AdminLayout>
            <Head title="حجز جديد" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            حجز جديد
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            إضافة حجز جديد للشاليه.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={route('admin.bookings.index')}>
                            <ArrowRight className="h-4 w-4" />
                            العودة للحجوزات
                        </Link>
                    </Button>
                </div>

                {blocked_dates.length > 0 && (
                    <Alert>
                        <CalendarOff className="h-4 w-4" />
                        <AlertTitle>فترات حظر قادمة</AlertTitle>
                        <AlertDescription>
                            <ul className="mt-2 space-y-1 text-xs">
                                {blocked_dates.slice(0, 5).map((b) => (
                                    <li key={b.id} className="font-mono">
                                        {b.start_date} → {b.end_date}{' '}
                                        {b.reason ? `— ${b.reason}` : ''}
                                    </li>
                                ))}
                                {blocked_dates.length > 5 && (
                                    <li className="text-muted-foreground">
                                        + {blocked_dates.length - 5} فترة
                                        أخرى...
                                    </li>
                                )}
                            </ul>
                        </AlertDescription>
                    </Alert>
                )}

                {warning && (
                    <div className="rounded-md border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                        {warning}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>بيانات العميل</CardTitle>
                            <CardDescription>
                                الاسم ورقم الجوال للتواصل.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Field
                                id="customer_name"
                                label="اسم العميل"
                                value={data.customer_name}
                                onChange={(v) => setData('customer_name', v)}
                                error={errors.customer_name}
                                required
                            />
                            <Field
                                id="customer_phone"
                                label="رقم الجوال"
                                value={data.customer_phone}
                                onChange={(v) => setData('customer_phone', v)}
                                error={errors.customer_phone}
                                dir="ltr"
                                required
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>تفاصيل الحجز</CardTitle>
                            <CardDescription>
                                التواريخ والمبالغ والحالة.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Field
                                id="start_date"
                                label="تاريخ الدخول"
                                type="date"
                                value={data.start_date}
                                onChange={(v) => setData('start_date', v)}
                                error={errors.start_date}
                                required
                            />
                            <Field
                                id="end_date"
                                label="تاريخ الخروج"
                                type="date"
                                value={data.end_date}
                                onChange={(v) => setData('end_date', v)}
                                error={errors.end_date}
                                required
                            />
                            <Field
                                id="total_amount"
                                label="المبلغ الإجمالي (ريال)"
                                type="number"
                                min="1"
                                step="any"
                                value={data.total_amount}
                                onChange={(v) => setData('total_amount', v)}
                                error={errors.total_amount}
                                required
                                dir="ltr"
                            />
                            <Field
                                id="deposit_amount"
                                label="العربون (اختياري)"
                                type="number"
                                min="0"
                                step="any"
                                value={data.deposit_amount}
                                onChange={(v) => setData('deposit_amount', v)}
                                error={errors.deposit_amount}
                                dir="ltr"
                            />
                            <div className="space-y-1">
                                <Label htmlFor="booking_status">
                                    حالة الحجز
                                </Label>
                                <select
                                    id="booking_status"
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    value={data.booking_status}
                                    onChange={(e) =>
                                        setData(
                                            'booking_status',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="pending">
                                        قيد الانتظار
                                    </option>
                                    <option value="confirmed">مؤكد</option>
                                    <option value="completed">مكتمل</option>
                                </select>
                                {errors.booking_status && (
                                    <p className="text-sm text-destructive">
                                        {errors.booking_status}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>ملاحظات</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Label htmlFor="notes" className="sr-only">
                                ملاحظات
                            </Label>
                            <Textarea
                                id="notes"
                                placeholder="ملاحظات داخلية (اختياري)"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                rows={4}
                            />
                            {errors.notes && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.notes}
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <div className="flex items-center justify-end gap-2">
                        <Button asChild variant="outline" type="button">
                            <Link href={route('admin.bookings.index')}>
                                إلغاء
                            </Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'جاري الحفظ...' : 'حفظ الحجز'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

interface FieldProps {
    id: string;
    label: string;
    value: string;
    onChange: (v: string) => void;
    error?: string;
    type?: string;
    required?: boolean;
    dir?: 'ltr' | 'rtl';
    min?: string;
    step?: string;
}

function Field({
    id,
    label,
    value,
    onChange,
    error,
    type = 'text',
    required,
    dir,
    min,
    step,
}: FieldProps) {
    return (
        <div className="space-y-1">
            <Label htmlFor={id}>
                {label}
                {required && <span className="ms-1 text-destructive">*</span>}
            </Label>
            <Input
                id={id}
                type={type}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                dir={dir}
                aria-invalid={!!error}
                min={min}
                step={step}
            />
            {error && (
                <p className="text-sm text-destructive">{error}</p>
            )}
        </div>
    );
}
