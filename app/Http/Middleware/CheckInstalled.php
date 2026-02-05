<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check ENV for Database setup
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');

        // If DB Name or User is empty/default, consider not installed
        // You might want to check against 'laravel' if that's the default in your example env
        // If DB Name is 'forge' (default) or empty, consider not installed
        $isInstalled = !empty($dbName) && $dbName !== 'forge';

        if (!$isInstalled) {
            if (!$request->is('install') && !$request->is('install/*')) {
                return redirect()->route('install.index');
            }
        } else {
            if ($request->is('install') || $request->is('install/*')) {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
