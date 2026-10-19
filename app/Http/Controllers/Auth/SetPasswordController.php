<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Set Password Controller
 *
 * Where the link from an invitation (the Users page) lands: the account exists with a password nobody knows, and this
 * turns the emailed token into the real one.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class SetPasswordController extends Controller
{
    use LogActivity;

    /**
     * Show the set-password form
     *
     * @param  Request  $request
     * @param  string   $token
     * @return View
     */
    public function create(Request $request, string $token): View
    {
        return view('auth.set-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    /**
     * Set the password using the emailed token
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)],
        ], [
            'password.min' => __('set_password.hint'),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                $this->log($user, 'password-set');
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
