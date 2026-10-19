<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\LogActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Login Controller
 *
 * Back-office sign-in with a username or an email (cashiers imported from the POS have no email).
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class LoginController extends Controller
{
    use LogActivity;

    /**
     * Show the login form
     *
     * @return View
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Sign the user in
     *
     * Rate limiting is the `throttle:login` route middleware (see AppServiceProvider).
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($request->string('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Honeypot: a hidden field a person never fills; bots that do get the same error.
        // One message for an unknown account and a wrong password, so accounts can't be guessed.
        if ($request->filled('website') || ! Auth::attempt([$field => $login, 'password' => $request->input('password')], $request->boolean('remember'))) {
            $this->log(null, 'login-failed', ['login' => $login]);
            throw ValidationException::withMessages(['login' => __('login.failed')]);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();
        $this->log($request->user(), 'login');

        return redirect()->intended(route('backend.dashboard'));
    }

    /**
     * Sign the user out
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->log($request->user(), 'logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
