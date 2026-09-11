<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Show the code entry page.
     */
    public function notice(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify', ['email' => $request->user()->email]);
    }

    /**
     * Verify the confirmation code and activate the account.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (! $request->user()->verifyEmailConfirmationCode((string) $request->input('code'))) {
            return back()->withErrors(['code' => __('auth.invalid_code')]);
        }

        return redirect()->route('dashboard')->with('success', __('auth.verified'));
    }

    /**
     * Send a fresh confirmation code to the user.
     */
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', __('auth.code_sent'));
    }
}
