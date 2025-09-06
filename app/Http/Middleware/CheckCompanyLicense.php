<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if ($user && $user->company && (is_null($user->company->expired_at) || now()->gt($user->company->expired_at))) {
            return response()->json([
                'message' => 'License expired. Please contact administrator.',
                'license_expired' => true,
            ], 403);
        }
        
        return $next($request);
    }
}
