import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog';
import { Plus, Trash2, Edit, ExternalLink, Eye, EyeOff } from 'lucide-react';
import { cn } from '@/lib/utils';

type SocialLink = {
    id: number;
    name: string;
    platform: string;
    url: string;
    handle: string | null;
    sort_order: number;
    is_active: boolean;
    color: string;
    label: string;
};

type Platform = { value: string; label: string };

type Props = {
    links: SocialLink[];
    platforms: Platform[];
};

type FormData = {
    name: string;
    platform: string;
    url: string;
    handle: string;
    sort_order: number;
    is_active: boolean;
};

const emptyForm: FormData = {
    name: '',
    platform: 'whatsapp',
    url: '',
    handle: '',
    sort_order: 0,
    is_active: true,
};

export default function SocialLinks({ links, platforms }: Props) {
    const [editing, setEditing] = useState<SocialLink | null>(null);
    const [creating, setCreating] = useState(false);
    const [dialogOpen, setDialogOpen] = useState(false);

    const form = useForm<FormData>(emptyForm);

    function openCreate() {
        setEditing(null);
        form.setData({
            ...emptyForm,
            sort_order: links.length > 0 ? Math.max(...links.map((l) => l.sort_order)) + 1 : 1,
        });
        setCreating(true);
        setDialogOpen(true);
    }

    function openEdit(link: SocialLink) {
        setEditing(link);
        setCreating(false);
        form.setData({
            name: link.name,
            platform: link.platform,
            url: link.url,
            handle: link.handle || '',
            sort_order: link.sort_order,
            is_active: link.is_active,
        });
        setDialogOpen(true);
    }

    function closeDialog() {
        setDialogOpen(false);
        setEditing(null);
        setCreating(false);
        form.reset();
        form.clearErrors();
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        if (editing) {
            form.put(route('admin.social-links.update', editing.id), {
                onSuccess: () => closeDialog(),
            });
        } else {
            form.post(route('admin.social-links.store'), {
                onSuccess: () => closeDialog(),
            });
        }
    }

    function destroy(id: number) {
        if (confirm('هل أنت متأكد من حذف هذا الحساب؟')) {
            router.delete(route('admin.social-links.destroy', id));
        }
    }

    function toggleActive(link: SocialLink) {
        router.put(route('admin.social-links.update', link.id), {
            ...link,
            is_active: !link.is_active,
        }, { preserveScroll: true });
    }

    return (
        <AdminLayout>
            <Head title="حسابات التواصل الاجتماعي" />

            <div className="space-y-6">
                <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">حسابات التواصل الاجتماعي</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            الحسابات المضافة هنا تظهر للزوار في قسم "تواصل معنا" بالصفحة الرئيسية
                        </p>
                    </div>
                    <Button onClick={openCreate} className="gap-2">
                        <Plus className="h-4 w-4" />
                        إضافة حساب
                    </Button>
                </div>

                {links.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <p className="text-base font-medium text-foreground">لا توجد حسابات بعد</p>
                            <p className="text-sm text-muted-foreground">ابدأ بإضافة أول حساب تواصل اجتماعي</p>
                            <Button onClick={openCreate} className="mt-4 gap-2">
                                <Plus className="h-4 w-4" />
                                إضافة أول حساب
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {links.map((link) => (
                            <Card key={link.id} className={cn(!link.is_active && 'opacity-60')}>
                                <CardHeader className="flex flex-row items-start justify-between gap-2 pb-3">
                                    <div className="flex items-center gap-3">
                                        <span
                                            className="flex h-10 w-10 items-center justify-center rounded-full text-white"
                                            style={{ backgroundColor: link.color }}
                                        >
                                            <span className="h-5 w-5 [&>svg]:h-full [&>svg]:w-full"
                                                dangerouslySetInnerHTML={{ __html: platformsIconSvg(link.platform) }}
                                            />
                                        </span>
                                        <div>
                                            <CardTitle className="text-base">{link.name}</CardTitle>
                                            {link.handle && (
                                                <CardDescription className="text-xs" dir="ltr">
                                                    {link.handle}
                                                </CardDescription>
                                            )}
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <a
                                        href={link.url}
                                        target="_blank"
                                        rel="noopener"
                                        className="flex items-center gap-1.5 break-all text-xs text-muted-foreground transition hover:text-primary"
                                    >
                                        <ExternalLink className="h-3 w-3 shrink-0" />
                                        <span className="truncate">{link.url}</span>
                                    </a>

                                    <div className="flex items-center justify-between gap-2 border-t border-border pt-3">
                                        <button
                                            type="button"
                                            onClick={() => toggleActive(link)}
                                            className={cn(
                                                'inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium transition',
                                                link.is_active
                                                    ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                                            )}
                                        >
                                            {link.is_active ? (
                                                <>
                                                    <Eye className="h-3 w-3" /> ظاهر
                                                </>
                                            ) : (
                                                <>
                                                    <EyeOff className="h-3 w-3" /> مخفي
                                                </>
                                            )}
                                        </button>
                                        <div className="flex items-center gap-1">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => openEdit(link)}
                                            >
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => destroy(link.id)}
                                                className="text-destructive hover:bg-destructive/10"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            {/* Create/Edit dialog */}
            <Dialog open={dialogOpen} onOpenChange={(open) => !open && closeDialog()}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{creating ? 'إضافة حساب جديد' : 'تعديل الحساب'}</DialogTitle>
                        <DialogDescription>
                            {creating
                                ? 'أضف رابط حساب التواصل الاجتماعي ليظهر للزوار في الصفحة الرئيسية.'
                                : 'عدّل بيانات الحساب.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="name">اسم الحساب للعرض *</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                placeholder="مثال: واتساب الشاليه"
                                required
                                maxLength={64}
                            />
                            {form.errors.name && <p className="text-sm text-destructive">{form.errors.name}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="platform">المنصة *</Label>
                            <select
                                id="platform"
                                value={form.data.platform}
                                onChange={(e) => form.setData('platform', e.target.value)}
                                className="block w-full rounded-md border border-input bg-background px-3 py-2 text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-ring"
                                required
                            >
                                {platforms.map((p) => (
                                    <option key={p.value} value={p.value}>
                                        {p.label}
                                    </option>
                                ))}
                            </select>
                            {form.errors.platform && <p className="text-sm text-destructive">{form.errors.platform}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="url">الرابط *</Label>
                            <Input
                                id="url"
                                type="url"
                                value={form.data.url}
                                onChange={(e) => form.setData('url', e.target.value)}
                                placeholder="https://wa.me/9665XXXXXXXX أو https://instagram.com/..."
                                required
                                maxLength={500}
                                dir="ltr"
                            />
                            {form.errors.url && <p className="text-sm text-destructive">{form.errors.url}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="handle">اسم المستخدم (اختياري)</Label>
                            <Input
                                id="handle"
                                value={form.data.handle}
                                onChange={(e) => form.setData('handle', e.target.value)}
                                placeholder="@username"
                                maxLength={64}
                                dir="ltr"
                            />
                            <p className="text-xs text-muted-foreground">للعرض فقط، بدون علامة @</p>
                            {form.errors.handle && <p className="text-sm text-destructive">{form.errors.handle}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="sort_order">الترتيب</Label>
                                <Input
                                    id="sort_order"
                                    type="number"
                                    min="0"
                                    value={form.data.sort_order}
                                    onChange={(e) => form.setData('sort_order', Number(e.target.value))}
                                />
                                {form.errors.sort_order && (
                                    <p className="text-sm text-destructive">{form.errors.sort_order}</p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="is_active">الحالة</Label>
                                <label className="mt-2 flex items-center gap-2">
                                    <input
                                        id="is_active"
                                        type="checkbox"
                                        checked={form.data.is_active}
                                        onChange={(e) => form.setData('is_active', e.target.checked)}
                                        className="h-4 w-4 rounded border-input text-primary focus:ring-primary"
                                    />
                                    <span className="text-sm">مفعّل (يظهر للزوار)</span>
                                </label>
                            </div>
                        </div>

                        <DialogFooter className="gap-2">
                            <Button type="button" variant="outline" onClick={closeDialog}>
                                إلغاء
                            </Button>
                            <Button type="submit" disabled={form.processing}>
                                {creating ? 'إضافة' : 'حفظ التعديلات'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}

// Inline icon SVGs for the admin card preview (kept compact; matches Model's brand SVGs).
function platformsIconSvg(platform: string): string {
    const map: Record<string, string> = {
        whatsapp:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24z"/></svg>',
        instagram:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>',
        twitter:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231z"/></svg>',
        snapchat:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12.927-.215.243-.087.502-.18.74-.18.374 0 .677.13.922.244a1.62 1.62 0 0 1 .557.39c.272.32.355.706.355 1.045 0 .93-.74 1.55-1.265 1.95-.193.143-.42.314-.5.42-.297.382-.054.93.39 1.493.713.91 1.71 1.74 2.97 2.475.466.27 1.005.525 1.477.525.262 0 .474-.072.66-.18.18-.105.405-.27.612-.27.225 0 .444.117.575.21.18.135.42.359.42.674 0 .405-.39.674-1.245 1.155-.27.15-.585.314-.84.51-.404.314-.39.554-.36.674.06.225.255.404.585.599.45.27.96.585 1.005 1.155.045.66-.42 1.08-1.14 1.08-.36 0-.78-.105-1.275-.21-.6-.12-1.41-.27-1.83-.21-.585.075-1.005.39-1.485.78-.495.39-1.05.84-1.86.84z"/></svg>',
        tiktok:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43V8.62a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.84-.05z"/></svg>',
        telegram:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>',
        facebook:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.14 8.14 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z"/></svg>',
        youtube:
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
    };
    return map[platform] || '';
}