<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Listing Controller
 *
 * A listing's public page, /property/{code}/{slug}. The code finds it; the slug is only for people and search engines,
 * so a missing or old one (the name changed) is sent on to the current URL with a 301.
 *
 * @package App\Controllers
 */
class ListingController extends Controller
{
    /**
     * Show a listing still on the market
     *
     * @param  string  $code
     * @param  string|null  $slug
     * @return View|RedirectResponse
     */
    public function __invoke(string $code, ?string $slug = null): View|RedirectResponse
    {
        $property = Property::query()->listed()->where('code', $code)
            ->with(['photos', 'tags', 'agent', 'district'])->firstOrFail();

        if ($slug !== $property->slug()) {
            return redirect($property->publicUrl(), 301);
        }

        return view('listing', [
            'property' => $property,
            'facilities' => $property->tagsWithType(Property::FACILITY)->pluck('name'),
            'mapToken' => config('services.mapbox.token'),
        ]);
    }
}
