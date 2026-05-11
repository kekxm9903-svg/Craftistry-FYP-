<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckKyc
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->kyc_status !== 'passed') {
            // Allow access to kyc routes and logout only
            if (!$request->routeIs('kyc.*', 'logout', 'verification.*')) {
                return redirect()->route('kyc.show');
            }
        }

        return $next($request);
    }
}