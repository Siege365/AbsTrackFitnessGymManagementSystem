<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'owner', 'manager'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Access denied');
        }

        return $next($request);
    }
}
