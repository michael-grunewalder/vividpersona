<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Store the user's preferred locale in the session.
     */
    public function update(Request $request): RedirectResponse
    {
        $locale = $request->validate([
            'locale' => ['required', Rule::in(['en', 'de'])],
        ])['locale'];

        session(['locale' => $locale]);

        return redirect()->back();
    }
}
