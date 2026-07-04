import { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    Trash2,
    Image as ImageIcon,
    Upload,
    Save,
    Pencil,
} from 'lucide-react';

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
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/Components/ui/tabs';

interface Photo {
    id: number;
    url: string;
    caption: string | null;
    sort_order: number;
    created_at: string;
}

interface Settings {
    id: number;
    name: string;
    description: string | null;
    features: string | null;
    location_text: string | null;
    map_url: string | null;
    whatsapp_number: string | null;
    phone_number: string | null;
    weekday_price: number | string;
    weekend_price: number | string;
    max_capacity: number | string;
    check_in_time: string;
    check_out_time: string;
    is_active: boolean;
    photos: Photo[];
}

interface SettingsIndexProps {
    settings: Settings;
    chalet_settings?: Settings;
}

interface SettingsForm {
    name: string;
    description: string;
    features: string;
    location_text: string;
    map_url: string;
    whatsapp_number: string;
    phone_number: string;
    weekday_price: string;
    weekend_price: string;
    max_capacity: string;
    check_in_time: string;
    check_out_time: string;
    is_active: boolean;
}

export default function SettingsIndex({ settings }: SettingsIndexProps) {
    const form = useForm<SettingsForm>({
        name: settings.name ?? '',
        description: settings.description ?? '',
        features: settings.features ?? '',
        location_text: settings.location_text ?? '',
        map_url: settings.map_url ?? '',
        whatsapp_number: settings.whatsapp_number ?? '',
        phone_number: settings.phone_number ?? '',
        weekday_price: String(settings.weekday_price ?? ''),
        weekend_price: String(settings.weekend_price ?? ''),
        max_capacity: String(settings.max_capacity ?? 50),
        check_in_time: settings.check_in_time ?? '16:00',
        check_out_time: settings.check_out_time ?? '12:00',
        is_active: Boolean(settings.is_active),
    });

    function save(e: React.FormEvent) {
        e.preventDefault();
        form.put(route('admin.settings.update'), {
            preserveScroll: true,
        });
    }

    function deletePhoto(p: Photo) {
        if (!confirm('هل تريد حذف هذه الصورة؟')) return;
        router.delete(route('admin.settings.photos.destroy', p.id), {
            preserveScroll: true,
        });
    }

    return (
        <AdminLayout>
            <Head title="الإعدادات" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        إعدادات الشاليه
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        المعلومات الأساسية، الأسعار، وروابط التواصل.
                    </p>
                </div>

                {/* Quick link to social links management */}
                <Card>
                    <CardContent className="flex flex-col items-start justify-between gap-3 p-5 sm:flex-row sm:items-center">
                        <div>
                            <CardTitle className="text-base">حسابات التواصل الاجتماعي</CardTitle>
                            <CardDescription className="mt-1">
                                الحسابات التي تظهر للزوار في قسم "تواصل معنا" بالصفحة الرئيسية
                            </CardDescription>
                        </div>
                        <Link href={route('admin.social-links.index')}>
                            <Button variant="outline" className="gap-2">
                                إدارة الحسابات
                            </Button>
                        </Link>
                    </CardContent>
                </Card>

                <form onSubmit={save}>
                    <Tabs defaultValue="general" dir="rtl">
                        <TabsList className="grid w-full grid-cols-2 md:grid-cols-4">
                            <TabsTrigger value="general">
                                المعلومات الأساسية
                            </TabsTrigger>
                            <TabsTrigger value="pricing">
                                الأسعار والمواعيد
                            </TabsTrigger>
                            <TabsTrigger value="visibility">
                                الظهور
                            </TabsTrigger>
                            <TabsTrigger value="features">
                                المميزات
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent value="general">
                            <Card>
                                <CardHeader>
                                    <CardTitle>المعلومات الأساسية</CardTitle>
                                    <CardDescription>
                                        الاسم والوصف وموقع الشاليه.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <Field
                                        id="name"
                                        label="اسم الشاليه"
                                        value={form.data.name}
                                        onChange={(v) => form.setData('name', v)}
                                        error={form.errors.name}
                                        required
                                    />
                                    <Field
                                        id="location_text"
                                        label="الموقع (نص)"
                                        value={form.data.location_text}
                                        onChange={(v) =>
                                            form.setData('location_text', v)
                                        }
                                        error={form.errors.location_text}
                                    />
                                    <div className="md:col-span-2 space-y-1">
                                        <Label htmlFor="description">
                                            الوصف
                                        </Label>
                                        <Textarea
                                            id="description"
                                            rows={4}
                                            value={form.data.description}
                                            onChange={(e) =>
                                                form.setData(
                                                    'description',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {form.errors.description && (
                                            <p className="text-sm text-destructive">
                                                {form.errors.description}
                                            </p>
                                        )}
                                    </div>
                                    <Field
                                        id="map_url"
                                        label="رابط الخريطة (URL)"
                                        type="url"
                                        value={form.data.map_url}
                                        onChange={(v) =>
                                            form.setData('map_url', v)
                                        }
                                        error={form.errors.map_url}
                                        dir="ltr"
                                    />
                                    <Field
                                        id="whatsapp_number"
                                        label="رقم الواتساب"
                                        value={form.data.whatsapp_number}
                                        onChange={(v) =>
                                            form.setData(
                                                'whatsapp_number',
                                                v,
                                            )
                                        }
                                        error={form.errors.whatsapp_number}
                                        dir="ltr"
                                    />
                                    <Field
                                        id="phone_number"
                                        label="رقم الاتصال المباشر (مختلف عن الواتساب)"
                                        value={form.data.phone_number}
                                        onChange={(v) =>
                                            form.setData('phone_number', v)
                                        }
                                        error={form.errors.phone_number}
                                        dir="ltr"
                                    />
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="pricing">
                            <Card>
                                <CardHeader>
                                    <CardTitle>الأسعار ومواعيد الدخول/الخروج</CardTitle>
                                    <CardDescription>
                                        قيمة إيجار الليلة وأوقات الدخول والخروج.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <Field
                                        id="weekday_price"
                                        label="سعر أيام الأسبوع (ريال)"
                                        type="number"
                                        min="0"
                                        step="any"
                                        value={form.data.weekday_price}
                                        onChange={(v) =>
                                            form.setData('weekday_price', v)
                                        }
                                        error={form.errors.weekday_price}
                                        dir="ltr"
                                        required
                                    />
                                    <Field
                                        id="weekend_price"
                                        label="سعر نهاية الأسبوع (ريال)"
                                        type="number"
                                        min="0"
                                        step="any"
                                        value={form.data.weekend_price}
                                        onChange={(v) =>
                                            form.setData('weekend_price', v)
                                        }
                                        error={form.errors.weekend_price}
                                        dir="ltr"
                                        required
                                    />
                                    <Field
                                        id="max_capacity"
                                        label="السعة القصوى (عدد الضيوف)"
                                        type="number"
                                        min="1"
                                        max="10000"
                                        value={String(form.data.max_capacity ?? 50)}
                                        onChange={(v) =>
                                            form.setData('max_capacity', Number(v))
                                        }
                                        error={form.errors.max_capacity}
                                        dir="ltr"
                                        required
                                    />
                                    <Field
                                        id="check_in_time"
                                        label="وقت الدخول (HH:MM)"
                                        value={form.data.check_in_time}
                                        onChange={(v) =>
                                            form.setData('check_in_time', v)
                                        }
                                        error={form.errors.check_in_time}
                                        placeholder="16:00"
                                        dir="ltr"
                                        required
                                    />
                                    <Field
                                        id="check_out_time"
                                        label="وقت الخروج (HH:MM)"
                                        value={form.data.check_out_time}
                                        onChange={(v) =>
                                            form.setData('check_out_time', v)
                                        }
                                        error={form.errors.check_out_time}
                                        placeholder="12:00"
                                        dir="ltr"
                                        required
                                    />
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="visibility">
                            <Card>
                                <CardHeader>
                                    <CardTitle>ظهور الصفحة العامة</CardTitle>
                                    <CardDescription>
                                        عند الإيقاف، تظهر الصفحة العامة كمسوّدة.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <label className="flex cursor-pointer items-center gap-3 text-sm">
                                        <input
                                            type="checkbox"
                                            className="h-5 w-5 rounded border-input text-primary focus:ring-ring"
                                            checked={form.data.is_active}
                                            onChange={(e) =>
                                                form.setData(
                                                    'is_active',
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                        <span>الصفحة العامة مفعّلة</span>
                                    </label>
                                    {form.errors.is_active && (
                                        <p className="mt-1 text-sm text-destructive">
                                            {form.errors.is_active}
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="features">
                            <Card>
                                <CardHeader>
                                    <CardTitle>مميزات الشاليه</CardTitle>
                                    <CardDescription>
                                        ميزة واحدة في كل سطر.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Label htmlFor="features" className="sr-only">
                                        المميزات
                                    </Label>
                                    <Textarea
                                        id="features"
                                        rows={8}
                                        value={form.data.features}
                                        onChange={(e) =>
                                            form.setData(
                                                'features',
                                                e.target.value,
                                            )
                                        }
                                        placeholder={'مسبح خاص\nإطلالة جبلية\nشواء خارجي'}
                                    />
                                    {form.errors.features && (
                                        <p className="mt-1 text-sm text-destructive">
                                            {form.errors.features}
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>

                    <div className="mt-6 flex justify-end">
                        <Button type="submit" disabled={form.processing}>
                            <Save className="h-4 w-4" />
                            {form.processing ? 'جاري الحفظ...' : 'حفظ الإعدادات'}
                        </Button>
                    </div>
                </form>

                <PhotosCard photos={settings.photos ?? []} />
            </div>

            <span className="hidden">
                <Pencil />
                <ImageIcon />
                <Upload />
            </span>
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
    placeholder?: string;
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
    placeholder,
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
                placeholder={placeholder}
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

interface PhotosCardProps {
    photos: Photo[];
}

function PhotosCard({ photos }: PhotosCardProps) {
    const [fileError, setFileError] = useState<string | null>(null);
    const { data, setData, post, processing, reset, errors } = useForm<{
        photo: File | null;
    }>({
        photo: null,
    });

    function submitPhoto(e: React.FormEvent) {
        e.preventDefault();
        if (!data.photo) {
            setFileError('الرجاء اختيار صورة أولاً.');
            return;
        }
        setFileError(null);
        post(route('admin.settings.photos.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset();
            },
        });
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <ImageIcon className="h-4 w-4" />
                    معرض الصور
                </CardTitle>
                <CardDescription>
                    الصور المعروضة في الصفحة العامة.
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                <form
                    onSubmit={submitPhoto}
                    className="flex flex-wrap items-end gap-2"
                >
                    <div className="flex-1 space-y-1">
                        <Label htmlFor="new-photo">إضافة صورة جديدة</Label>
                        <Input
                            id="new-photo"
                            type="file"
                            accept="image/*"
                            onChange={(e) => {
                                setData('photo', e.target.files?.[0] ?? null);
                                setFileError(null);
                            }}
                        />
                        {(errors.photo || fileError) && (
                            <p className="text-sm text-destructive">
                                {errors.photo ?? fileError}
                            </p>
                        )}
                    </div>
                    <Button type="submit" disabled={processing}>
                        <Upload className="h-4 w-4" />
                        {processing ? 'جاري الرفع...' : 'رفع'}
                    </Button>
                </form>

                {photos.length === 0 ? (
                    <div className="rounded-md border border-dashed border-border bg-muted/30 px-4 py-8 text-center text-sm text-muted-foreground">
                        لا توجد صور بعد. ارفع أول صورة للشاليه.
                    </div>
                ) : (
                    <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                        {photos.map((p) => (
                            <div
                                key={p.id}
                                className="group relative overflow-hidden rounded-md border border-border bg-muted/30"
                            >
                                <img
                                    src={p.url}
                                    alt={p.caption ?? 'صورة الشاليه'}
                                    className="aspect-[4/3] w-full object-cover"
                                    loading="lazy"
                                />
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon"
                                    className="absolute left-2 top-2 h-7 w-7 opacity-0 transition-opacity group-hover:opacity-100"
                                    onClick={() => deletePhoto(p)}
                                    aria-label="حذف الصورة"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </div>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
