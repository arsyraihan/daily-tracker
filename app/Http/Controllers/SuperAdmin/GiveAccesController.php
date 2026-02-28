<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GiveAccesController extends Controller
{
    protected $service;

    public function __construct(\App\Services\Superadmin\GiveAccesTo\GiveAccesToService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('SuperAdmin/GiveAccesTo/Index', [
            'users' => $this->service->getAll(),
            'roles' => \Spatie\Permission\Models\Role::all(),
            'permissions' => \Spatie\Permission\Models\Permission::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        if ($request->has('roles')) {
            $this->service->syncRoles((int) $id, $request->roles);
        }

        if ($request->has('permissions')) {
            $this->service->syncPermissions((int) $id, $request->permissions);
        }

        return redirect()->back()->with('success', 'Access updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Use service to delete if needed
    }
}
