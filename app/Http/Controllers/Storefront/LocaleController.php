<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('app.available_locales')), 404);

        $request->session()->put('locale', $locale);

        return back(fallback: route('home'))->withCookie(cookie()->forever('locale', $locale));
    }
}
