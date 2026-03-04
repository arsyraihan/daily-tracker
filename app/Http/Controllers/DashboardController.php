<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Redirect based on roles
        if ($user->hasRole('superadmin')) {
             return redirect('/super-admin/dashboard');
        }

        if ($user->hasAnyRole(['manager', 'supervisor'])) {
            if ($user->hasPermissionTo('view-dashboard')) {
                return redirect()->route('manager.dashboard');
            }
            if ($user->hasPermissionTo('kontrol absensi')) {
                return redirect()->route('manager.absensi.index');
            }
        }

        return redirect()->route('employee.dashboard');
    }
}
