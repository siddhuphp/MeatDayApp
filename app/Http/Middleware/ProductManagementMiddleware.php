<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductManagementMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next) {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized access. Authentication required.'], 401);
        }

        // Allow admin and content creator roles
        if (!$request->user()->isAdmin() && !$request->user()->isContentCreator()) {
            return response()->json(['message' => 'Unauthorized access. Admin or Content Creator role required.'], 403);
        }

        return $next($request);
    }
} 