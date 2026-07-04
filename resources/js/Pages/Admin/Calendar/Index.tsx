import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import type {
    EventClickArg,
    DatesSetArg,
    DateSelectArg,
    EventInput,
} from '@fullcalendar/core';

import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Clock, Trash2 } from 'lucide-react';

interface CalendarSettings {
    check_in_time: string;
    check_out_time: string;
}

interface CalendarIndexProps {
    chalet_settings?: CalendarSettings;
    // Back-compat fallback: some dispatchers may pass flatly
    check_in_time?: string;
    check_out_time?: string;
}

export default function CalendarIndex(props: CalendarIndexProps) {
    const checkIn = props.chalet_settings?.check_in_time ?? props.check_in_time ?? '16:00';
    const checkOut =
        props.chalet_settings?.check_out_time ?? props.check_out_time ?? '12:00';

    const [events, setEvents] = useState<EventInput[]>([]);
    const [range, setRange] = useState<{ start: string; end: string } | null>(
        null,
    );
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Dialog state for date-click (add booking / block)
    const [dateClickState, setDateClickState] = useState<{
        start: string;
        end: string;
    } | null>(null);

    // Dialog state for event-click (delete block)
    const [pendingDelete, setPendingDelete] = useState<{
        id: number;
        start: string;
        end: string;
    } | null>(null);
    const [deleting, setDeleting] = useState(false);

    async function fetchEvents(start: string, end: string) {
        setLoading(true);
        setError(null);
        try {
            const url = `/admin/calendar/events?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
            const res = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            const data: EventInput[] = await res.json();
            setEvents(data);
        } catch (err) {
            console.warn('Calendar events fetch failed', err);
            setError('تعذر تحميل أحداث التقويم.');
            setEvents([]);
        } finally {
            setLoading(false);
        }
    }

    function handleDatesSet(arg: DatesSetArg) {
        const start = arg.startStr.slice(0, 10);
        // FullCalendar's `end` is exclusive for month view. Pad with 7 days to be safe.
        const rawEnd = arg.endStr.slice(0, 10);
        const end = rawEnd;
        setRange({ start, end });
        void fetchEvents(start, end);
    }

    function handleSelect(arg: DateSelectArg) {
        setDateClickState({
            start: arg.startStr.slice(0, 10),
            end: arg.endStr
                ? arg.endStr.slice(0, 10)
                : arg.startStr.slice(0, 10),
        });
    }

    function handleDateClick(arg: { dateStr: string }) {
        setDateClickState({
            start: arg.dateStr,
            end: arg.dateStr,
        });
    }

    function handleEventClick(arg: EventClickArg) {
        arg.jsEvent.preventDefault();
        const type = arg.event.extendedProps?.type as string | undefined;
        if (type === 'booking') {
            const bookingId = arg.event.extendedProps?.bookingId as
                number
                | undefined;
            if (bookingId) {
                router.visit(route('admin.bookings.show', bookingId));
            }
            return;
        }
        if (type === 'block') {
            const blockId = arg.event.extendedProps?.blockId as number | undefined;
            if (blockId) {
                setPendingDelete({
                    id: blockId,
                    start: arg.event.startStr.slice(0, 10),
                    end: arg.event.endStr.slice(0, 10),
                });
            }
            return;
        }
    }

    function navigateToBookingCreate() {
        if (!dateClickState) return;
        const { start, end } = dateClickState;
        const url = `/admin/bookings/create?start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`;
        setDateClickState(null);
        router.visit(url);
    }

    function submitBlock() {
        if (!dateClickState) return;
        router.post(
            route('admin.blocked-dates.store'),
            {
                start_date: dateClickState.start,
                end_date: dateClickState.end,
                reason: 'حظر يدوي من التقويم',
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setDateClickState(null);
                    if (range) void fetchEvents(range.start, range.end);
                },
            },
        );
    }

    function confirmDeleteBlock() {
        if (!pendingDelete) return;
        setDeleting(true);
        router.delete(route('admin.blocked-dates.destroy', pendingDelete.id), {
            preserveScroll: true,
            onSuccess: () => {
                setPendingDelete(null);
                if (range) void fetchEvents(range.start, range.end);
            },
            onFinish: () => setDeleting(false),
        });
    }

    return (
        <AdminLayout>
            <Head title="التقويم" />

            <div className="space-y-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        التقويم
                    </h1>
                    <p className="flex items-center gap-2 text-sm text-muted-foreground">
                        <Clock className="h-4 w-4" />
                        وقت الدخول: {checkIn} — وقت الخروج: {checkOut}
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>الحجوزات وفترات الحظر</CardTitle>
                        <CardDescription>
                            انقر على يوم لإضافة حجز أو حظر، أو انقر على حدث
                            لفتح تفاصيله.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {error && (
                            <div className="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-900">
                                {error}
                            </div>
                        )}
                        {loading && (
                            <p className="mb-2 text-xs text-muted-foreground">
                                جاري التحميل...
                            </p>
                        )}
                        <div dir="ltr">
                            <FullCalendar
                                plugins={[dayGridPlugin, interactionPlugin]}
                                initialView="dayGridMonth"
                                firstDay={6}
                                locale="ar"
                                direction="ltr"
                                headerToolbar={{
                                    start: 'prev,next today',
                                    center: 'title',
                                    end: 'dayGridMonth',
                                }}
                                height="auto"
                                events={events}
                                selectable={true}
                                selectMirror={true}
                                datesSet={handleDatesSet}
                                select={handleSelect}
                                dateClick={handleDateClick}
                                eventClick={handleEventClick}
                                dayMaxEvents={3}
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Date-click dialog: choose action */}
            <Dialog
                open={dateClickState !== null}
                onOpenChange={(o) =>
                    !o ? setDateClickState(null) : undefined
                }
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>إضافة إلى التقويم</DialogTitle>
                        <DialogDescription>
                            {dateClickState && (
                                <span>
                                    الفترة:{' '}
                                    <span className="font-mono">
                                        {dateClickState.start}
                                    </span>{' '}
                                    →{' '}
                                    <span className="font-mono">
                                        {dateClickState.end}
                                    </span>
                                </span>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDateClickState(null)}
                        >
                            إلغاء
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={submitBlock}
                        >
                            حظر الفترة
                        </Button>
                        <Button onClick={navigateToBookingCreate}>
                            إضافة حجز
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Confirm-delete block dialog */}
            <Dialog
                open={pendingDelete !== null}
                onOpenChange={(o) =>
                    !o ? setPendingDelete(null) : undefined
                }
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>حذف فترة الحظر</DialogTitle>
                        <DialogDescription>
                            {pendingDelete && (
                                <span>
                                    هل تريد حذف الحظر من{' '}
                                    <span className="font-mono">
                                        {pendingDelete.start}
                                    </span>{' '}
                                    إلى{' '}
                                    <span className="font-mono">
                                        {pendingDelete.end}
                                    </span>
                                    ؟ لا يمكن التراجع.
                                </span>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setPendingDelete(null)}
                            disabled={deleting}
                        >
                            إلغاء
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={confirmDeleteBlock}
                            disabled={deleting}
                        >
                            <Trash2 className="h-4 w-4" />
                            {deleting ? 'جاري الحذف...' : 'حذف'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
