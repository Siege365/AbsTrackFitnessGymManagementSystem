<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecurityManager
{
    /**
     * Handle an incoming request.
     * Restrict access strictly to 'owner' or 'manager' roles.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->canAccessSecurity()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. The CyberShield Security Subsystem is restricted to Owner, Manager, and Admin roles only.'
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Access denied. The CyberShield Security Subsystem is restricted to Owner, Manager, and Admin roles only.');
        }

        return $next($request);
    }
}

