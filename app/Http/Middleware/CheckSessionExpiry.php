<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSessionExpiry
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ Skip session expiry check for Stripe callback routes
        if ($request->routeIs('order.checkout.success') ||
            $request->routeIs('class.checkout.success') ||
            $request->routeIs('order.checkout.cancel')  ||
            $request->routeIs('class.checkout.cancel')) {
            return $next($request);
        }

        if (Auth::check()) {
            $lastActivity = session('last_activity_time');
            $timeout      = config('session.lifetime') * 60; // convert to seconds

            if ($lastActivity && (time() - $lastActivity > $timeout)) {
                Auth::logout();
                session()->flush();
                session()->regenerate();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired. Please log in again.'], 401);
                }

                return redirect()->route('login')
                    ->with('error', 'Your session has expired. Please log in again.');
            }

            // Update last activity timestamp on every request
            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}