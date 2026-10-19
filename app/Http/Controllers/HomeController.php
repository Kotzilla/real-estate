<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\View\View;

/**
 * Home Controller
 *
 * The public home page: filters, a Mapbox map of Koh Phangan and the listings beside it. Every published listing on
 * the island that is still on the market goes to the page at once and is filtered in the browser (resources/js/home.js).
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class HomeController extends Controller
{
    // Where the map opens: the middle of Koh Phangan
    public const CENTER = [100.025, 9.745]; // lng, lat (Mapbox order)

    public const ZOOM = 11.3;

    /**
     * Show the home page
     *
     * @return View
     */
    public function __invoke(): View
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

        return view('home', [
            'listings' => $listings,
            'areas' => $listings->pluck('location')->filter()->unique()->sort()->values(),
            'types' => $listings->pluck('typeLabel', 'type')->sortKeys(),
            'map' => ['token' => config('services.mapbox.token'), 'center' => self::CENTER, 'zoom' => self::ZOOM],
        ]);
    }
}
