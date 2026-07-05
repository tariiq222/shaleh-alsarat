import { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { arSA } from 'date-fns/locale';
import {
    ArrowRight,
    Pencil,
    XCircle,
    CheckCircle2,
    Copy,
    MessageCircle,
    Plus,
    Trash2,
    FileText,
    Receipt,
    User,
    CalendarDays,
    Wallet,
} from 'lucide-react';

import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Badge } from '@/Components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { cn } from '@/lib/utils';

type BookingStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed';
type PaymentStatus = 'unpaid' | 'partially_paid' | 'paid';
type PaymentMethod = 'cash' | 'bank_transfer' | 'other';

interface Booking {
    id: number;
    booking_number: string;
    customer_name: string;
    customer_phone: string;
    start_date: string;
    end_date: string;
    total_amount: number;
    deposit_amount: number | null;
    remaining_amount: number;
    booking_status: BookingStatus;
    payment_status: PaymentStatus;
    notes: string | null;
    payments: Payment[];
}

interface Payment {
    id: number;
    amount: number;
    payment_method: PaymentMethod;
    payment_method_label: string;
    payment_date: string;
    note: string | null;
    receipt_url: string | null;
}

interface WhatsAppLinkShape {
    pending_message: string | null;
    confirmed_message: string | null;
    reminder_message: string | null;
    remaining_message: string | null;
}

interface BookingsShowProps {
    booking: Booking;
    whatsapp_link: WhatsAppLinkShape;
}

interface PaymentForm {
    amount: string;
    payment_method: PaymentMethod;
    payment_date: string;
    note: string;
    receipt: File | null;
}

const bookingStatusLabels: Record<BookingStatus, string> = {
    pending: 'قيد الانتظار',
    confirmed: 'مؤكد',
    cancelled: 'ملغي',
    completed: 'مكتمل',
};

const bookingStatusStyles: Record<BookingStatus, string> = {
    pending: 'booking-status-pending border',
    confirmed: 'booking-status-confirmed border',
    cancelled: 'booking-status-cancelled border',
    completed: 'booking-status-completed border',
};

const paymentStatusLabels: Record<PaymentStatus, string> = {
    unpaid: 'غير مدفوع',
    partially_paid: 'مدفوع جزئياً',
    paid: 'مدفوع',
};

const paymentStatusStyles: Record<PaymentStatus, string> = {
    unpaid: 'payment-status-unpaid',
    partially_paid: 'payment-status-partially_paid',
    paid: 'payment-status-paid',
};

const whatsappLabels: Array<{
    key: keyof WhatsAppLinkShape;
    label: string;
    text: keyof WhatsAppLinkShape;
}> = [
    {
        key: 'pending_message',
        label: 'رسالة تأكيد مبدئي',
        text: 'pending_message',
    },
    {
        key: 'confirmed_message',
        label: 'تأكيد الحجز',
        text: 'confirmed_message',
    },
    {
        key: 'reminder_message',
        label: 'تذكير',
        text: 'reminder_message',
    },
    {
        key: 'remaining_message',
        label: 'المتبقي',
        text: 'remaining_message',
    },
];

function formatDate(d: string) {
    try {
        return format(parseISO(d), 'PPP', { locale: arSA });
    } catch {
        return d;
    }
}

function formatCurrency(v: number) {
    return `${new Intl.NumberFormat('ar-SA', {
        maximumFractionDigits: 2,
    }).format(v)} ريال`;
}

function today() {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${dd}`;
}

export default function BookingsShow({
    booking,
    whatsapp_link,
}: BookingsShowProps) {
    const [paymentOpen, setPaymentOpen] = useState(true);
    const [copied, setCopied] = useState<string | null>(null);

    const paymentForm = useFormPayment();

    function cancelBooking() {
        if (!confirm('هل تريد إلغاء هذا الحجز؟')) return;
        router.patch(
            route('admin.bookings.cancel', booking.id),
            {},
            { preserveScroll: true },
        );
    }

    function completeBooking() {
        if (
            !confirm('هل تريد تأكيد اكتمال هذا الحجز؟')
        )
            return;
        router.patch(
            route('admin.bookings.complete', booking.id),
            {},
            { preserveScroll: true },
        );
    }

    async function copyWaLink(key: keyof WhatsAppLinkShape) {
        const url = whatsapp_link[key];
        if (!url) return;
        try {
            await navigator.clipboard.writeText(url);
            setCopied(key);
            setTimeout(() => setCopied(null), 1500);
        } catch {
            window.prompt('انسخ الرابط:', url);
        }
    }

    function submitPayment(e: React.FormEvent) {
        e.preventDefault();
        paymentForm.post(
            route('admin.bookings.payments.store', booking.id),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    paymentForm.reset();
                    setPaymentOpen(false);
                },
            },
        );
    }

    function deletePayment(p: Payment) {
        if (!confirm('هل تريد حذف هذه الدفعة؟')) return;
        router.delete(route('admin.payments.destroy', p.id), {
            preserveScroll: true,
        });
    }

    return (
        <AdminLayout>
            <Head title={`الحجز ${booking.booking_number}`} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="space-y-1">
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-bold tracking-tight">
                                الحجز {booking.booking_number}
                            </h1>
                            <Badge
                                className={cn(
                                    bookingStatusStyles[booking.booking_status],
                                )}
                            >
                                {bookingStatusLabels[booking.booking_status]}
                            </Badge>
                            <Badge
                                className={cn(
                                    paymentStatusStyles[booking.payment_status],
                                )}
                            >
                                {paymentStatusLabels[booking.payment_status]}
                            </Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            {booking.customer_name} —{' '}
                            <span dir="ltr">{booking.customer_phone}</span>
                        </p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <Button asChild variant="outline">
                            <Link href={route('admin.bookings.edit', booking.id)}>
                                <Pencil className="h-4 w-4" />
                                تعديل
                            </Link>
                        </Button>
                        {booking.booking_status === 'confirmed' && (
                            <Button variant="outline" onClick={completeBooking}>
                                <CheckCircle2 className="h-4 w-4" />
                                تأكيد الاكتمال
                            </Button>
                        )}
                        {booking.booking_status !== 'cancelled' &&
                            booking.booking_status !== 'completed' && (
                                <Button
                                    variant="destructive"
                                    onClick={cancelBooking}
                                >
                                    <XCircle className="h-4 w-4" />
                                    إلغاء
                                </Button>
                            )}
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>تفاصيل الحجز</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <DetailRow
                                icon={User}
                                label="اسم العميل"
                                value={booking.customer_name}
                            />
                            <DetailRow
                                icon={MessageCircle}
                                label="رقم الجوال"
                                value={booking.customer_phone}
                                dir="ltr"
                            />
                            <DetailRow
                                icon={CalendarDays}
                                label="فترة الإقامة"
                                value={`${formatDate(booking.start_date)} ← ${formatDate(booking.end_date)}`}
                            />
                            <DetailRow
                                icon={Wallet}
                                label="المبلغ الإجمالي"
                                value={formatCurrency(booking.total_amount)}
                            />
                            <DetailRow
                                icon={Wallet}
                                label="المتبقي"
                                value={formatCurrency(booking.remaining_amount)}
                            />
                            {booking.notes && (
                                <div className="rounded-md border border-dashed border-border bg-muted/30 p-3 text-sm">
                                    <p className="mb-1 font-medium">ملاحظات:</p>
                                    <p className="whitespace-pre-wrap text-muted-foreground">
                                        {booking.notes}
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>رسائل واتساب</CardTitle>
                            <CardDescription>
                                انقر لنسخ رابط الرسالة، ثم أرسله عبر واتساب.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            {whatsappLabels.map((item) => {
                                const url = whatsapp_link[item.text];
                                if (!url) {
                                    return (
                                        <Button
                                            key={item.key}
                                            variant="outline"
                                            className="w-full justify-between"
                                            disabled
                                        >
                                            {item.label}
                                            <Copy className="h-4 w-4" />
                                        </Button>
                                    );
                                }
                                return (
                                    <div
                                        key={item.key}
                                        className="flex items-center gap-2"
                                    >
                                        <Button
                                            variant="outline"
                                            className="flex-1 justify-between"
                                            onClick={() =>
                                                copyWaLink(item.text)
                                            }
                                        >
                                            {item.label}
                                            {copied === item.key ? (
                                                <CheckCircle2 className="h-4 w-4 text-green-600" />
                                            ) : (
                                                <Copy className="h-4 w-4" />
                                            )}
                                        </Button>
                                        <Button asChild variant="ghost" size="icon">
                                            <a
                                                href={url}
                                                target="_blank"
                                                rel="noreferrer"
                                                aria-label="فتح في واتساب"
                                            >
                                                <MessageCircle className="h-4 w-4" />
                                            </a>
                                        </Button>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>الدفعات ({booking.payments.length})</CardTitle>
                            <Button
                                variant={paymentOpen ? 'secondary' : 'outline'}
                                size="sm"
                                onClick={() => setPaymentOpen((v) => !v)}
                            >
                                <Plus className="h-4 w-4" />
                                {paymentOpen ? 'إخفاء النموذج' : 'إضافة دفعة'}
                            </Button>
                        </div>
                        {paymentOpen && (
                            <CardDescription>
                                سجّل دفعة جديدة على هذا الحجز.
                            </CardDescription>
                        )}
                    </CardHeader>
                    {paymentOpen && (
                        <CardContent>
                            <form
                                onSubmit={submitPayment}
                                className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5"
                            >
                                <div className="space-y-1">
                                    <Label htmlFor="amount">المبلغ (ريال)</Label>
                                    <Input
                                        id="amount"
                                        type="number"
                                        min="0.01"
                                        step="any"
                                        dir="ltr"
                                        value={paymentForm.data.amount}
                                        onChange={(e) =>
                                            paymentForm.setData(
                                                'amount',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                    {paymentForm.errors.amount && (
                                        <p className="text-xs text-destructive">
                                            {paymentForm.errors.amount}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-1">
                                    <Label htmlFor="payment_method">
                                        طريقة الدفع
                                    </Label>
                                    <select
                                        id="payment_method"
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        value={paymentForm.data.payment_method}
                                        onChange={(e) =>
                                            paymentForm.setData(
                                                'payment_method',
                                                e.target.value as PaymentMethod,
                                            )
                                        }
                                    >
                                        <option value="cash">نقدي</option>
                                        <option value="bank_transfer">
                                            تحويل بنكي
                                        </option>
                                        <option value="other">آخر</option>
                                    </select>
                                </div>
                                <div className="space-y-1">
                                    <Label htmlFor="payment_date">
                                        تاريخ الدفع
                                    </Label>
                                    <Input
                                        id="payment_date"
                                        type="date"
                                        value={paymentForm.data.payment_date}
                                        onChange={(e) =>
                                            paymentForm.setData(
                                                'payment_date',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                </div>
                                <div className="space-y-1">
                                    <Label htmlFor="receipt">
                                        الإيصال (اختياري)
                                    </Label>
                                    <Input
                                        id="receipt"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        onChange={(e) =>
                                            paymentForm.setData(
                                                'receipt',
                                                e.target.files?.[0] ?? null,
                                            )
                                        }
                                    />
                                </div>
                                <div className="space-y-1 lg:col-span-1">
                                    <Label htmlFor="note" className="sr-only">
                                        ملاحظة
                                    </Label>
                                    <Input
                                        id="note"
                                        placeholder="ملاحظة (اختياري)"
                                        value={paymentForm.data.note}
                                        onChange={(e) =>
                                            paymentForm.setData(
                                                'note',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                                <div className="md:col-span-2 lg:col-span-5">
                                    <Button
                                        type="submit"
                                        disabled={paymentForm.processing}
                                    >
                                        <Plus className="h-4 w-4" />
                                        {paymentForm.processing
                                            ? 'جاري الحفظ...'
                                            : 'تسجيل الدفعة'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    )}
                    <CardContent className={paymentOpen ? 'pt-0' : ''}>
                        {booking.payments.length === 0 ? (
                            <div className="rounded-md border border-dashed border-border bg-muted/30 px-4 py-8 text-center text-sm text-muted-foreground">
                                لا توجد دفعات على هذا الحجز.
                            </div>
                        ) : (
                            <ul className="divide-y divide-border rounded-md border">
                                {booking.payments.map((p) => (
                                    <li
                                        key={p.id}
                                        className="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm"
                                    >
                                        <div className="space-y-1">
                                            <p className="font-medium">
                                                {formatCurrency(p.amount)}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {p.payment_method_label} —{' '}
                                                {formatDate(p.payment_date)}
                                            </p>
                                            {p.note && (
                                                <p className="text-xs text-muted-foreground">
                                                    {p.note}
                                                </p>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {p.receipt_url && (
                                                <Button asChild variant="ghost" size="sm">
                                                    <a
                                                        href={p.receipt_url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                    >
                                                        <Receipt className="h-4 w-4" />
                                                        الإيصال
                                                    </a>
                                                </Button>
                                            )}
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => deletePayment(p)}
                                                className="text-destructive hover:text-destructive"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                                حذف
                                            </Button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>

                <div>
                    <Button asChild variant="outline">
                        <Link href={route('admin.bookings.index')}>
                            <ArrowRight className="h-4 w-4" />
                            العودة للحجوزات
                        </Link>
                    </Button>
                    <span className="hidden">
                        <FileText />
                    </span>
                </div>
            </div>
        </AdminLayout>
    );
}

interface DetailRowProps {
    icon: React.ComponentType<{ className?: string }>;
    label: string;
    value: React.ReactNode;
    dir?: 'ltr' | 'rtl';
}

function DetailRow({ icon: Icon, label, value, dir }: DetailRowProps) {
    return (
        <div className="flex items-start gap-3">
            <Icon className="mt-0.5 h-4 w-4 text-muted-foreground" />
            <div className="flex-1 space-y-0.5">
                <p className="text-xs text-muted-foreground">{label}</p>
                <p className="text-sm font-medium" dir={dir}>
                    {value}
                </p>
            </div>
        </div>
    );
}

function useFormPayment() {
    // Local hook wrapper to keep types tidy
    return useForm<PaymentForm>({
        amount: '',
        payment_method: 'cash',
        payment_date: today(),
        note: '',
        receipt: null,
    });
}
