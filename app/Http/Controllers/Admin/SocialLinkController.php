<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialLinkRequest;
use App\Http\Requests\Admin\UpdateSocialLinkRequest;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialLinkController extends Controller
{
    public function index(Request $request): Response
    {
        $links = SocialLink::query()
            ->ordered()
            ->get()
            ->map(fn (SocialLink $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'platform' => $l->platform,
                'url' => $l->url,
                'handle' => $l->handle,
                'sort_order' => (int) $l->sort_order,
                'is_active' => (bool) $l->is_active,
                'color' => $l->brand['color'],
                'label' => $l->brand['label'],
            ]);

        return Inertia::render('Admin/Settings/SocialLinks', [
            'links' => $links,
            'platforms' => SocialLink::platformOptions(),
        ]);
    }

    public function store(StoreSocialLinkRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? (int) SocialLink::max('sort_order') + 1;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        SocialLink::create($data);

        return back()->with('success', 'تم إضافة الحساب');
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $socialLink->update($data);

        return back()->with('success', 'تم تحديث الحساب');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $socialLink->delete();

        return back()->with('success', 'تم حذف الحساب');
    }
}