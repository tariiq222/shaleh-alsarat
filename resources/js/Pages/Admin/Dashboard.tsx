import { Fragment } from 'react';
import { Head, Link } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { arSA } from 'date-fns/locale';
import {
    CalendarDays,
    CalendarCheck,
    Wallet,
    AlertCircle,
    ArrowLeft,
} from 'lucide-react';

import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { cn } from '@/lib/utils';

type BookingStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed';
type PaymentStatus = 'unpaid' | 'partially_paid' | 'paid';

interface Stats {
    today_count: number;
    upcoming_count: number;
    monthly_income: number;
    outstanding: number;
}

interface TodayBooking {
    id: number;
    booking_number: string;
    customer_name: string;
    start_date: string;
    end_date: string;
    booking_status: BookingStatus;
}

interface RecentBooking {
    id: number;
    booking_number: string;
    customer_name: string;
    start_date: string;
    end_date: string;
    booking_status: BookingStatus;
    payment_status: PaymentStatus;
    total_amount: number;
}

interface RecentPayment {
    id: number;
    booking_number: string | null;
    customer_name: string | null;
    amount: number;
    payment_method: string;
    payment_method_label: string;
    payment_date: string;
}

interface DashboardProps {
    stats: Stats;
    todays_bookings: TodayBooking[];
    recent_bookings: RecentBooking[];
    recent_payments: RecentPayment[];
}

const bookingStatusStyles: Record<BookingStatus, string> = {
    pending: 'booking-status-pending',
    confirmed: 'booking-status-confirmed',
    cancelled: 'booking-status-cancelled',
    completed: 'booking-status-completed',
};

const bookingStatusLabels: Record<BookingStatus, string> = {
    pending: 'قيد الانتظار',
    confirmed: 'مؤكد',
    cancelled: 'ملغي',
    completed: 'مكتمل',
};

const paymentStatusStyles: Record<PaymentStatus, string> = {
    unpaid: 'payment-status-unpaid',
    partially_paid: 'payment-status-partially_paid',
    paid: 'payment-status-paid',
};

const paymentStatusLabels: Record<PaymentStatus, string> = {
    unpaid: 'غير مدفوع',
    partially_paid: 'مدفوع جزئياً',
    paid: 'مدفوع',
};

function formatDate(d: string) {
    try {
        return format(parseISO(d), 'PPP', { locale: arSA });
    } catch {
        return d;
    }
}

function formatCurrency(v: number) {
    return `${new Intl.NumberFormat('ar-SA', {
        maximumFractionDigits: 0,
    }).format(v)} ريال`;
}

export default function Dashboard({
    stats,
    todays_bookings,
    recent_bookings,
    recent_payments,
}: DashboardProps) {
    return (
        <AdminLayout>
            <Head title="لوحة التحكم" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        لوحة التحكم
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        نظرة عامة على الحجوزات والإيرادات.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        title="حجوزات اليوم"
                        value={stats.today_count}
                        icon={CalendarDays}
                        accent="text-blue-600"
                    />
                    <StatCard
                        title="الحجوزات القادمة"
                        value={stats.upcoming_count}
                        icon={CalendarCheck}
                        accent="text-teal-600"
                    />
                    <StatCard
                        title="دخل الشهر"
                        value={formatCurrency(stats.monthly_income)}
                        icon={Wallet}
                        accent="text-green-600"
                    />
                    <StatCard
                        title="المتبقي"
                        value={formatCurrency(stats.outstanding)}
                        icon={AlertCircle}
                        accent="text-red-600"
                    />
                </div>

                {todays_bookings.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>حجوزات اليوم</CardTitle>
                            <CardDescription>
                                النزلاء المتوقع وصولهم أو وجودهم حالياً.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul className="divide-y divide-border rounded-md border">
                                {todays_bookings.map((b) => (
                                    <li
                                        key={b.id}
                                        className="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm"
                                    >
                                        <div className="space-y-1">
                                            <Link
                                                href={route(
                                                    'admin.bookings.show',
                                                    b.id,
                                                )}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {b.booking_number}
                                            </Link>
                                            <p className="text-muted-foreground">
                                                {b.customer_name}
                                            </p>
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {formatDate(b.start_date)} ←{' '}
                                            {formatDate(b.end_date)}
                                        </div>
                                        <Badge
                                            className={cn(
                                                bookingStatusStyles[
                                                    b.booking_status
                                                ],
                                                'border',
                                            )}
                                        >
                                            {
                                                bookingStatusLabels[
                                                    b.booking_status
                                                ]
                                            }
                                        </Badge>
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>
                )}

                <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>أحدث الحجوزات</CardTitle>
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('admin.bookings.index')}>
                                        عرض الكل
                                        <ArrowLeft className="h-4 w-4" />
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {recent_bookings.length === 0 ? (
                                <EmptyHint text="لا توجد حجوزات بعد." />
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>رقم الحجز</TableHead>
                                            <TableHead>العميل</TableHead>
                                            <TableHead>المبلغ</TableHead>
                                            <TableHead>الحالة</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recent_bookings.map((b) => (
                                            <TableRow key={b.id}>
                                                <TableCell>
                                                    <Link
                                                        href={route(
                                                            'admin.bookings.show',
                                                            b.id,
                                                        )}
                                                        className="font-medium text-primary hover:underline"
                                                    >
                                                        {b.booking_number}
                                                    </Link>
                                                </TableCell>
                                                <TableCell>
                                                    {b.customer_name}
                                                </TableCell>
                                                <TableCell>
                                                    {formatCurrency(
                                                        b.total_amount,
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex flex-col gap-1">
                                                        <Badge
                                                            className={cn(
                                                                bookingStatusStyles[
                                                                    b
                                                                        .booking_status
                                                                ],
                                                                'border',
                                                            )}
                                                        >
                                                            {
                                                                bookingStatusLabels[
                                                                    b
                                                                        .booking_status
                                                                ]
                                                            }
                                                        </Badge>
                                                        <Badge
                                                            className={cn(
                                                                paymentStatusStyles[
                                                                    b
                                                                        .payment_status
                                                                ],
                                                            )}
                                                        >
                                                            {
                                                                paymentStatusLabels[
                                                                    b
                                                                        .payment_status
                                                                ]
                                                            }
                                                        </Badge>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>أحدث الدفعات</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {recent_payments.length === 0 ? (
                                <EmptyHint text="لا توجد دفعات مسجلة." />
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>الحجز</TableHead>
                                            <TableHead>المبلغ</TableHead>
                                            <TableHead>الطريقة</TableHead>
                                            <TableHead>التاريخ</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recent_payments.map((p) => (
                                            <TableRow key={p.id}>
                                                <TableCell>
                                                    <span className="font-mono text-xs">
                                                        {p.booking_number ?? '—'}
                                                    </span>
                                                    <p className="text-xs text-muted-foreground">
                                                        {p.customer_name ?? ''}
                                                    </p>
                                                </TableCell>
                                                <TableCell>
                                                    {formatCurrency(p.amount)}
                                                </TableCell>
                                                <TableCell>
                                                    {p.payment_method_label}
                                                </TableCell>
                                                <TableCell>
                                                    {formatDate(
                                                        p.payment_date,
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <Fragment />
        </AdminLayout>
    );
}

interface StatCardProps {
    title: string;
    value: string | number;
    icon: React.ComponentType<{ className?: string }>;
    accent?: string;
}

function StatCard({ title, value, icon: Icon, accent }: StatCardProps) {
    return (
        <Card>
            <CardContent className="flex items-center justify-between p-6">
                <div className="space-y-1">
                    <p className="text-xs font-medium text-muted-foreground">
                        {title}
                    </p>
                    <p className="text-2xl font-bold tabular-nums">{value}</p>
                </div>
                <div
                    className={cn(
                        'flex h-10 w-10 items-center justify-center rounded-full bg-muted',
                        accent,
                    )}
                >
                    <Icon className="h-5 w-5" />
                </div>
            </CardContent>
        </Card>
    );
}

function EmptyHint({ text }: { text: string }) {
    return (
        <div className="rounded-md border border-dashed border-border bg-muted/30 px-4 py-8 text-center text-sm text-muted-foreground">
            {text}
        </div>
    );
}
