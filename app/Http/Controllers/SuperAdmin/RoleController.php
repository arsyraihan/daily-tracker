<?php
 
namespace App\Http\Controllers\SuperAdmin;
 
use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\Role\RoleService;
use App\Services\SuperAdmin\Permission\PermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
 
class RoleController extends Controller
{
    protected $roleService;
    protected $permissionService;
 
    public function __construct(RoleService $roleService, PermissionService $permissionService)
    {
        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
    }
 
    public function index()
    {
        return Inertia::render('SuperAdmin/Roles/index', [
            'roles' => $this->roleService->getAllRoles(),
            'permissions' => $this->permissionService->getAllPermissions(),
        ]);
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);
 
        $role = $this->roleService->createRole(['name' => $request->name, 'guard_name' => 'web']);
        if ($request->has('permissions')) {
            $this->roleService->syncPermissions($role->id, $request->permissions);
        }
 
        return redirect()->back()->with('success', 'Role created successfully.');
    }
 
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);
 
        $this->roleService->updateRole($id, ['name' => $request->name]);
        $this->roleService->syncPermissions($id, $request->permissions);
 
        return redirect()->back()->with('success', 'Role updated successfully.');
    }
 
    public function destroy($id)
    {
        $this->roleService->deleteRole($id);
        return redirect()->route('super-admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
