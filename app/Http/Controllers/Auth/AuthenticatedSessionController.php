<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page (Inertia).
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        // A guest visit to a public page (e.g. "/") can leave a stale
        // `url.intended` in the session. Sending an admin there after login
        // would dump them back on the public site. Only honor the intended
        // URL when it points to an admin path on this host.
        $intended = $request->session()->get('url.intended');
        if (! is_string($intended) || ! $this->isSafeAdminIntended($intended, $request)) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Determine whether a session-stored intended URL is safe to redirect an
     * admin to. Accepts the exact path `/admin` and paths starting with the
     * `/admin/` prefix. Absolute URLs must also match the current request's
     * host. Everything else — including lookalikes such as `/administrator`
     * and `/admin-public`, and cross-host URLs — is rejected to avoid open
     * redirects.
     */
    private function isSafeAdminIntended(string $intended, Request $request): bool
    {
        $host = parse_url($intended, PHP_URL_HOST);
        if ($host !== null && $host !== $request->getHost()) {
            return false;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?: '';

        return $path === '/admin' || str_starts_with($path, '/admin/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}