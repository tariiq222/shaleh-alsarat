<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\ChaletPhoto;
use App\Models\ChaletSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        $settings = ChaletSettings::current()->load('photos');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'chalet_settings' => $settings,
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $settings = ChaletSettings::current();
        $settings->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'تم تحديث الإعدادات');
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ], [], [
            'photo' => 'الصورة',
        ]);

        $file = $request->file('photo');
        $extension = $file->extension();
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = Storage::disk('public')->putFileAs('photos', $file, $filename);

        $maxSort = (int) ChaletPhoto::query()->max('sort_order');

        ChaletPhoto::create([
            'chalet_settings_id' => ChaletSettings::current()->id,
            'path' => $path,
            'caption' => null,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم رفع الصورة');
    }

    public function deletePhoto(ChaletPhoto $photo): RedirectResponse
    {
        $photo->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف الصورة');
    }

    public function reorderPhotos(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'photos' => ['required', 'array'],
            'photos.*' => ['integer', 'exists:chalet_photos,id'],
        ], [], [
            'photos' => 'ترتيب الصور',
        ]);

        foreach ($data['photos'] as $index => $id) {
            ChaletPhoto::query()->where('id', $id)->update(['sort_order' => $index]);
        }

        return redirect()
            ->back()
            ->with('success', 'تم تحديث ترتيب الصور');
    }
}