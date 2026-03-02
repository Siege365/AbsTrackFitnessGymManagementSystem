<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Only allow admin users to proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can perform this action.'
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Unauthorized. Only admins can access Staff Accounts management.');
        }

        return $next($request);
    }
}
