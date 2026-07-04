<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ChaletSettings;
use App\Models\SocialLink;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $settings = ChaletSettings::current()->load('photos');

        return view('public.home', [
            'settings' => $settings,
            'whatsapp_link' => $settings->whatsappLink(),
            'social_links' => SocialLink::active()->ordered()->get(),
        ]);
    }
}