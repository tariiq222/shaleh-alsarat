<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BlockedDateController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Public\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (SEO-critical — server-rendered Blade)
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])
    ->name('public.home');

/*
|--------------------------------------------------------------------------
| Admin Routes (Inertia SPA — no SEO needed)
|--------------------------------------------------------------------------
*/

// `/admin/login` is the obvious admin login URL. The auth login page lives at
// `/login`, so redirect there before the auth-protected admin group runs.
Route::redirect('/admin/login', '/login', 301);

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Calendar
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

        // Bookings
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');

        // Payments (nested under bookings)
        Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store'])->name('bookings.payments.store');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // Blocked dates
        Route::post('/blocked-dates', [BlockedDateController::class, 'store'])->name('blocked-dates.store');
        Route::delete('/blocked-dates/{blockedDate}', [BlockedDateController::class, 'destroy'])->name('blocked-dates.destroy');

        // Settings (singleton)
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Photos (settings sub-resource)
        Route::post('/settings/photos', [SettingsController::class, 'uploadPhoto'])->name('settings.photos.store');
        Route::delete('/settings/photos/{photo}', [SettingsController::class, 'deletePhoto'])->name('settings.photos.destroy');
        Route::post('/settings/photos/reorder', [SettingsController::class, 'reorderPhotos'])->name('settings.photos.reorder');

        // Social links (CRUD)
        Route::get('/settings/social-links', [SocialLinkController::class, 'index'])->name('social-links.index');
        Route::post('/settings/social-links', [SocialLinkController::class, 'store'])->name('social-links.store');
        Route::put('/settings/social-links/{socialLink}', [SocialLinkController::class, 'update'])->name('social-links.update');
        Route::delete('/settings/social-links/{socialLink}', [SocialLinkController::class, 'destroy'])->name('social-links.destroy');
    });

/*
|--------------------------------------------------------------------------
| Authentication routes (loaded from routes/auth.php)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';