<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckHrAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Assuming the authenticated user model has a 'role' attribute
        // and 'admin' or 'hr' are valid roles for HR/Admin access.
        if (!Auth::check() || (!in_array(Auth::user()->role, ['admin', 'hr']))) {
            abort(403, 'Unauthorized: You do not have HR/Admin privileges.');
        }

        return $next($request);
    }
}