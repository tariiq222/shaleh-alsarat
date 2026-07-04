import * as React from 'react';

import { cn } from '@/lib/utils';

/**
 * Lightweight Radix-free tabs implementation using React context.
 * Used by the Settings page to switch between sections.
 *
 * Note: The initial value comes from `defaultValue`. Switching is uncontrolled.
 * For controlled mode (rare), pass `value` and `onValueChange`.
 */

interface TabsContextValue {
    value: string;
    setValue: (v: string) => void;
}

const TabsContext = React.createContext<TabsContextValue | null>(null);

function useTabsContext(component: string): TabsContextValue {
    const ctx = React.useContext(TabsContext);
    if (!ctx) {
        throw new Error(`<${component}> must be used inside <Tabs>.`);
    }
    return ctx;
}

interface TabsProps {
    defaultValue: string;
    value?: string;
    onValueChange?: (value: string) => void;
    className?: string;
    children: React.ReactNode;
    dir?: 'ltr' | 'rtl';
}

const Tabs = React.forwardRef<HTMLDivElement, TabsProps>(function Tabs(
    { defaultValue, value, onValueChange, className, children, ...rest },
    ref,
) {
    const [internal, setInternal] = React.useState<string>(defaultValue);
    const isControlled = value !== undefined;
    const current = isControlled ? (value as string) : internal;

    const setValue = React.useCallback(
        (next: string) => {
            if (!isControlled) setInternal(next);
            onValueChange?.(next);
        },
        [isControlled, onValueChange],
    );

    return (
        <TabsContext.Provider value={{ value: current, setValue }}>
            <div ref={ref} className={className} {...rest}>
                {children}
            </div>
        </TabsContext.Provider>
    );
});

interface TabsListProps extends React.HTMLAttributes<HTMLDivElement> {
    children: React.ReactNode;
}

const TabsList = React.forwardRef<HTMLDivElement, TabsListProps>(function TabsList(
    { className, children, ...rest },
    ref,
) {
    return (
        <div
            ref={ref}
            role="tablist"
            className={cn(
                'inline-flex h-10 items-center justify-center gap-1 rounded-md bg-muted p-1 text-muted-foreground',
                className,
            )}
            {...rest}
        >
            {children}
        </div>
    );
});

interface TabsTriggerProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    value: string;
    children: React.ReactNode;
}

const TabsTrigger = React.forwardRef<HTMLButtonElement, TabsTriggerProps>(function TabsTrigger(
    { value, className, children, ...rest },
    ref,
) {
    const ctx = useTabsContext('TabsTrigger');
    const active = ctx.value === value;

    return (
        <button
            ref={ref}
            type="button"
            role="tab"
            aria-selected={active}
            data-state={active ? 'active' : 'inactive'}
            onClick={() => ctx.setValue(value)}
            className={cn(
                'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium transition-all',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                'disabled:pointer-events-none disabled:opacity-50',
                active
                    ? 'bg-background text-foreground shadow-sm'
                    : 'hover:bg-background/60 hover:text-foreground',
                className,
            )}
            {...rest}
        >
            {children}
        </button>
    );
});

interface TabsContentProps extends React.HTMLAttributes<HTMLDivElement> {
    value: string;
    children: React.ReactNode;
}

const TabsContent = React.forwardRef<HTMLDivElement, TabsContentProps>(function TabsContent(
    { value, className, children, ...rest },
    ref,
) {
    const ctx = useTabsContext('TabsContent');
    if (ctx.value !== value) return null;

    return (
        <div
            ref={ref}
            role="tabpanel"
            data-state="active"
            className={cn('mt-4 focus-visible:outline-none', className)}
            {...rest}
        >
            {children}
        </div>
    );
});

export { Tabs, TabsList, TabsTrigger, TabsContent };