<?php
 
namespace App\Http\Controllers\SuperAdmin;
 
use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\Permission\PermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
 
class PermissionController extends Controller
{
    protected $permissionService;
 
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
 
    public function index()
    {
        return Inertia::render('SuperAdmin/Permissions/index', [
            'permissions' => $this->permissionService->getAllPermissions(),
        ]);
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);
 
        $this->permissionService->createPermission(['name' => $request->name, 'guard_name' => 'web']);
 
        return redirect()->back()->with('success', 'Permission created successfully.');
    }
 
    public function destroy($id)
    {
        $this->permissionService->deletePermission($id);
        return redirect()->back()->with('success', 'Permission deleted successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $id,
        ]);

        $this->permissionService->updatePermission($id, ['name' => $request->name]);

        return redirect()->back()->with('success', 'Permission updated successfully.');
    }
}
