<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Listing Controller
 *
 * The public listings: the map search at /properties (filters | Mapbox map | listings) and a listing's own page,
 * /property/{code}/{slug}. The code finds it; the slug is only for people and search engines, so a missing or old one
 * (the name changed) is sent on to the current URL with a 301.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class ListingController extends Controller
{
    // Where the map opens: the middle of Koh Phangan
    public const CENTER = [100.025, 9.745]; // lng, lat (Mapbox order)

    public const ZOOM = 11.3;

    /**
     * The map search: every listing on the island goes to the page at once and is filtered in the browser
     * (resources/js/home.js)
     *
     * @return View
     */
    public function index(): View
    {
        // ponytail: every listing in one page, filtered client-side; fine for one island, page it server-side past ~1,000
        $listings = Property::query()
            ->with(['coverPhoto', 'tags'])
            ->listed()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->latest()
            ->get()
            ->map(fn (Property $property) => [
                'id' => $property->id,
                'code' => $property->code,
                'url' => $property->publicUrl(),
                'title' => $property->title(),
                'type' => $property->type->value,
                'typeLabel' => $property->type->label(),
                'reserved' => $property->status === PropertyStatus::Reserved,
                'location' => $property->location,
                'lat' => (float) $property->lat,
                'lng' => (float) $property->lng,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'area' => $property->area ? (int) $property->area : null,
                'land' => $property->land_area ? (int) $property->land_area : null,
                'sale' => $property->is_sale ? (int) $property->sale_price : null,
                'rent' => $property->is_rent ? (int) $property->rent_price : null,
                'photo' => $property->coverPhoto?->src('md'),
                'facilities' => $property->tagsWithType(Property::FACILITY)->pluck('name')->take(3)->values(),
            ]);

        return view('listings', [
            'listings' => $listings,
            'areas' => $listings->pluck('location')->filter()->unique()->sort()->values(),
            'types' => $listings->pluck('typeLabel', 'type')->sortKeys(),
            'map' => ['token' => config('services.mapbox.token'), 'center' => self::CENTER, 'zoom' => self::ZOOM],
        ]);
    }

    /**
     * Show a listing still on the market
     *
     * @param  string  $code
     * @param  string|null  $slug
     * @return View|RedirectResponse
     */
    public function show(string $code, ?string $slug = null): View|RedirectResponse
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
