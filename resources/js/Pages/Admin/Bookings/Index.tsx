import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { arSA } from 'date-fns/locale';
import {
    Plus,
    MoreVertical,
    Eye,
    Pencil,
    XCircle,
    MessageCircle,
    Search,
    Filter,
} from 'lucide-react';

import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Badge } from '@/Components/ui/badge';
import {
    Card,
    CardContent,
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
import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/Components/ui/dropdown-menu';
import { cn } from '@/lib/utils';

type BookingStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed';
type PaymentStatus = 'unpaid' | 'partially_paid' | 'paid';

interface BookingRow {
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
    payments_count?: number;
}

interface Paginated<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    meta?: Record<string, unknown>;
}

interface Filters {
    status?: string | null;
    payment_status?: string | null;
    search?: string | null;
    date_from?: string | null;
    date_to?: string | null;
}

interface BookingsIndexProps {
    bookings: Paginated<BookingRow>;
    filters: Filters;
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

function formatDate(d: string) {
    try {
        return format(parseISO(d), 'd MMM yyyy', { locale: arSA });
    } catch {
        return d;
    }
}

function formatCurrency(v: number) {
    return `${new Intl.NumberFormat('ar-SA', {
        maximumFractionDigits: 0,
    }).format(v)} ريال`;
}

export default function BookingsIndex({
    bookings,
    filters,
}: BookingsIndexProps) {
    const [statusVal, setStatusVal] = useState(filters.status ?? '');
    const [paymentVal, setPaymentVal] = useState(filters.payment_status ?? '');
    const [searchVal, setSearchVal] = useState(filters.search ?? '');
    const [fromVal, setFromVal] = useState(filters.date_from ?? '');
    const [toVal, setToVal] = useState(filters.date_to ?? '');

    function applyFilters() {
        router.get(
            route('admin.bookings.index'),
            {
                status: statusVal || undefined,
                payment_status: paymentVal || undefined,
                search: searchVal || undefined,
                date_from: fromVal || undefined,
                date_to: toVal || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    function resetFilters() {
        setStatusVal('');
        setPaymentVal('');
        setSearchVal('');
        setFromVal('');
        setToVal('');
        router.get(route('admin.bookings.index'), {}, { preserveScroll: true });
    }

    function cancelBooking(id: number) {
        if (!confirm('هل تريد إلغاء هذا الحجز؟')) return;
        router.patch(route('admin.bookings.cancel', id), {}, {
            preserveScroll: true,
        });
    }

    return (
        <AdminLayout>
            <Head title="الحجوزات" />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            الحجوزات
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            إدارة جميع حجوزات الشاليه.
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.bookings.create')}>
                            <Plus className="h-4 w-4" />
                            حجز جديد
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base">
                            <Filter className="h-4 w-4" />
                            تصفية
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
                        <div className="space-y-1">
                            <Label htmlFor="f-status">الحالة</Label>
                            <select
                                id="f-status"
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                value={statusVal}
                                onChange={(e) => setStatusVal(e.target.value)}
                            >
                                <option value="">الكل</option>
                                <option value="pending">قيد الانتظار</option>
                                <option value="confirmed">مؤكد</option>
                                <option value="completed">مكتمل</option>
                                <option value="cancelled">ملغي</option>
                            </select>
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="f-pay">الدفع</Label>
                            <select
                                id="f-pay"
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                value={paymentVal}
                                onChange={(e) => setPaymentVal(e.target.value)}
                            >
                                <option value="">الكل</option>
                                <option value="unpaid">غير مدفوع</option>
                                <option value="partially_paid">
                                    مدفوع جزئياً
                                </option>
                                <option value="paid">مدفوع</option>
                            </select>
                        </div>
                        <div className="space-y-1 lg:col-span-2">
                            <Label htmlFor="f-search">بحث</Label>
                            <div className="relative">
                                <Search className="absolute start-2 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="f-search"
                                    className="ps-8"
                                    placeholder="اسم العميل أو الجوال"
                                    value={searchVal}
                                    onChange={(e) =>
                                        setSearchVal(e.target.value)
                                    }
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') applyFilters();
                                    }}
                                />
                            </div>
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="f-from">من تاريخ</Label>
                            <Input
                                id="f-from"
                                type="date"
                                value={fromVal}
                                onChange={(e) => setFromVal(e.target.value)}
                            />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="f-to">إلى تاريخ</Label>
                            <Input
                                id="f-to"
                                type="date"
                                value={toVal}
                                onChange={(e) => setToVal(e.target.value)}
                            />
                        </div>
                        <div className="flex items-end gap-2 lg:col-span-3">
                            <Button onClick={applyFilters}>تطبيق</Button>
                            <Button variant="outline" onClick={resetFilters}>
                                مسح
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>رقم الحجز</TableHead>
                                    <TableHead>اسم العميل</TableHead>
                                    <TableHead>الجوال</TableHead>
                                    <TableHead>الدخول</TableHead>
                                    <TableHead>الخروج</TableHead>
                                    <TableHead>المبلغ</TableHead>
                                    <TableHead>المتبقي</TableHead>
                                    <TableHead>حالة الحجز</TableHead>
                                    <TableHead>الدفع</TableHead>
                                    <TableHead className="w-12" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {bookings.data.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={10}
                                            className="py-10 text-center text-sm text-muted-foreground"
                                        >
                                            لا توجد حجوزات مطابقة.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {bookings.data.map((b) => (
                                    <TableRow key={b.id}>
                                        <TableCell>
                                            <Link
                                                href={route(
                                                    'admin.bookings.show',
                                                    b.id,
                                                )}
                                                className="font-mono text-xs font-medium text-primary hover:underline"
                                            >
                                                {b.booking_number}
                                            </Link>
                                        </TableCell>
                                        <TableCell>{b.customer_name}</TableCell>
                                        <TableCell dir="ltr" className="text-end">
                                            {b.customer_phone}
                                        </TableCell>
                                        <TableCell>
                                            {formatDate(b.start_date)}
                                        </TableCell>
                                        <TableCell>
                                            {formatDate(b.end_date)}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(b.total_amount)}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(b.remaining_amount)}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                className={cn(
                                                    bookingStatusStyles[
                                                        b.booking_status
                                                    ],
                                                )}
                                            >
                                                {
                                                    bookingStatusLabels[
                                                        b.booking_status
                                                    ]
                                                }
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                className={cn(
                                                    paymentStatusStyles[
                                                        b.payment_status
                                                    ],
                                                )}
                                            >
                                                {
                                                    paymentStatusLabels[
                                                        b.payment_status
                                                    ]
                                                }
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label="إجراءات"
                                                    >
                                                        <MoreVertical className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>
                                                        إجراءات
                                                    </DropdownMenuLabel>
                                                    <DropdownMenuItem asChild>
                                                        <Link
                                                            href={route(
                                                                'admin.bookings.show',
                                                                b.id,
                                                            )}
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                            عرض
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link
                                                            href={route(
                                                                'admin.bookings.edit',
                                                                b.id,
                                                            )}
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                            تعديل
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    {b.booking_status !==
                                                        'cancelled' &&
                                                        b.booking_status !==
                                                            'completed' && (
                                                            <DropdownMenuItem
                                                                onClick={() =>
                                                                    cancelBooking(
                                                                        b.id,
                                                                    )
                                                                }
                                                                className="text-destructive focus:text-destructive"
                                                            >
                                                                <XCircle className="h-4 w-4" />
                                                                إلغاء
                                                            </DropdownMenuItem>
                                                        )}
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem asChild>
                                                        <a
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            href={`https://wa.me/${String(b.customer_phone).replace(/[^0-9]/g, '')}`}
                                                        >
                                                            <MessageCircle className="h-4 w-4" />
                                                            واتساب
                                                        </a>
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {bookings.links && bookings.links.length > 0 && (
                    <nav
                        aria-label="ترقيم الصفحات"
                        className="flex flex-wrap items-center justify-center gap-1"
                    >
                        {bookings.links.map((l, i) => (
                            <Link
                                key={i}
                                href={l.url ?? '#'}
                                disabled={!l.url}
                                preserveScroll
                                className={cn(
                                    'rounded-md border px-3 py-1.5 text-sm transition-colors',
                                    !l.url &&
                                        'pointer-events-none cursor-not-allowed opacity-50',
                                    l.active
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-border bg-card hover:bg-accent',
                                )}
                                dangerouslySetInnerHTML={{ __html: l.label }}
                            />
                        ))}
                    </nav>
                )}
            </div>
        </AdminLayout>
    );
}
