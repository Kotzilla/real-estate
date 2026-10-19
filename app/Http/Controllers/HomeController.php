<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\View\View;

/**
 * Home Controller
 *
 * The public landing page: a hero (a slideshow of public/assets/img/hero) with buy / rent, the newest listings (plain
 * links, so search engines find every listing's page) and the island's areas, each opening the map search
 * (/properties) already filtered, then who we are and how to reach us (config/site.php).
 *
 * @package App\Controllers
 */
class HomeController extends Controller
{
    // how many of the newest listings the page shows
    public const NEWEST = 6;

    /**
     * Show the landing page
     *
     * @return View
     */
    public function __invoke(): View
    {
        $listed = Property::query()->listed();

        return view('home', [
            // the hero's slides: every image in public/assets/img/hero, by name (slide-01, slide-02…)
            'slides' => collect(glob(public_path('assets/img/hero/*')) ?: [])
                ->filter(fn ($file) => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true))
                ->sort()->map(fn ($file) => asset('assets/img/hero/'.basename($file)))->values(),
            'newest' => (clone $listed)->with('coverPhoto')->latest()->limit(self::NEWEST)->get(),
            'total' => (clone $listed)->count(),
            'forSale' => (clone $listed)->where('is_sale', true)->count(),
            'forRent' => (clone $listed)->where('is_rent', true)->count(),
            'areas' => (clone $listed)->whereNotNull('location')->selectRaw('location, count(*) as total')
                ->groupBy('location')->orderBy('location')->pluck('total', 'location'),
            'mapToken' => config('services.mapbox.token'),
        ]);
    }
}
