<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSupervisor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->hasAnyRole(['supervisor', 'manager', 'superadmin'])) {
            abort(403, 'Akses Terbatas: Hanya untuk Supervisor/Manager/SuperAdmin');
        }
        return $next($request);
    }
}
