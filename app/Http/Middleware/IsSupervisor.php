<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // SuperAdmin has full bypass
        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        // Leader bypass for specific coordination routes
        $leaderRoutes = [
            'manager.tugas.propose-sub',
            'manager.tugas.sub.complete',
            'manager.tugas.detail'
        ];

        if ($user->hasAnyRole(['leader', 'employee']) && $request->routeIs($leaderRoutes)) {
            return $next($request);
        }

        // Basic Role Check: Must be management staff
        if (!$user->hasAnyRole(['supervisor', 'manager'])) {
            abort(403, 'Unauthorized: Management Access Only.');
        }

        // 1. GATE UTAMA: Izin Akses Manager Portal
        if (!$user->can('view-dashboard')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Izin akses Portal Management Anda telah dicabut.');
        }

        // 2. PROTEKSI FITUR SPESIFIK
        if ($request->routeIs('manager.absensi.*')) {
            if (!$user->can('kontrol absensi')) {
                abort(403, 'Anda tidak memiliki hak akses (kontrol absensi) untuk fitur ini.');
            }
        }

        if ($request->routeIs('manager.tugas.*')) {
            if (!$user->can('kelola tugas')) {
                abort(403, 'Anda tidak memiliki hak akses (kelola tugas) untuk fitur ini.');
            }
        }

        return $next($request);
    }
}
