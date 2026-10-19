<?php namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Locale Controller
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class LocaleController extends Controller
{
    /**
     * Switch application locale
     *
     * @param  Request  $request
     * @param  string  $locale
     * @return RedirectResponse
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, ['en', 'th'], true)) {
            $request->session()->put('locale', $locale);
            app()->setLocale($locale);
            Carbon::setLocale($locale);
        }

        return redirect()->back(fallback: route('backend.dashboard'));
    }
}
