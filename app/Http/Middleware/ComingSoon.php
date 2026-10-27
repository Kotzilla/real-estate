<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Coming Soon Middleware
 *
 * Shows the coming-soon page instead of the public site while `site.coming_soon` is on (production by default,
 * COMING_SOON=false to launch). Signed-in staff still see the real site.
 *
 * @package App\Http\Middleware
 */
class ComingSoon
{
    /**
     * Handle incoming request
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('site.coming_soon') && ! $request->user()) {
            return response()->view('coming-soon');
        }

        return $next($request);
    }
}
