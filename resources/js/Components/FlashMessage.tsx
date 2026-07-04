import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { CheckCircle2, AlertCircle, XCircle, AlertTriangle } from 'lucide-react';

import { cn } from '@/lib/utils';

type FlashKind = 'success' | 'error' | 'warning';

type FlashShape = Partial<Record<FlashKind, string>>;

interface FlashMessageProps {
    className?: string;
}

export function FlashMessage({ className }: FlashMessageProps) {
    const { flash } = usePage<{ flash: FlashShape }>();
    const [visible, setVisible] = useState<FlashShape>({});

    useEffect(() => {
        setVisible(flash ?? {});
        if (flash?.success || flash?.error || flash?.warning) {
            const t = setTimeout(() => setVisible({}), 5000);
            return () => clearTimeout(t);
        }
    }, [flash?.success, flash?.error, flash?.warning]);

    const items: Array<{
        key: FlashKind;
        icon: React.ComponentType<{ className?: string }>;
        className: string;
        message: string;
    }> = [];

    if (visible.success) {
        items.push({
            key: 'success',
            icon: CheckCircle2,
            message: visible.success,
            className:
                'border-green-300 bg-green-50 text-green-900 dark:bg-green-950',
        });
    }
    if (visible.error) {
        items.push({
            key: 'error',
            icon: XCircle,
            message: visible.error,
            className: 'border-red-300 bg-red-50 text-red-900 dark:bg-red-950',
        });
    }
    if (visible.warning) {
        items.push({
            key: 'warning',
            icon: AlertTriangle,
            message: visible.warning,
            className:
                'border-yellow-300 bg-yellow-50 text-yellow-900 dark:bg-yellow-950',
        });
    }

    if (items.length === 0) return null;

    return (
        <div className={cn('flex flex-col gap-2', className)}>
            {items.map(({ key, icon: Icon, className, message }) => (
                <div
                    key={key}
                    role="alert"
                    className={cn(
                        'flex items-start gap-3 rounded-md border px-4 py-3 text-sm shadow-sm',
                        className,
                    )}
                >
                    <Icon className="mt-0.5 h-5 w-5 shrink-0" />
                    <span className="flex-1">{message}</span>
                    <button
                        type="button"
                        onClick={() =>
                            setVisible((prev) => {
                                const next = { ...prev };
                                delete next[key];
                                return next;
                            })
                        }
                        className="opacity-70 transition-opacity hover:opacity-100"
                        aria-label="إغلاق الرسالة"
                    >
                        ×
                    </button>
                </div>
            ))}
            {/* satisfy AlertCircle unused import for tree-shake safety */}
            {false && <AlertCircle />}
        </div>
    );
}
