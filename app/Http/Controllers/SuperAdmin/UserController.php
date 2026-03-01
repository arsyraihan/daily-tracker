<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\Users\UsersService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $usersService;
    protected $divisiService;
    protected $jabatanService;

    public function __construct(
        UsersService $usersService,
        \App\Services\Superadmin\Divisi\DivisiService $divisiService,
        \App\Services\Superadmin\Jabatan\JabatanService $jabatanService
    ) {
        $this->usersService = $usersService;
        $this->divisiService = $divisiService;
        $this->jabatanService = $jabatanService;
    }

    public function index()
    {
        // Get the role names that actually exist in the system to avoid exceptions
        $supervisorRoles = ['superadmin', 'supervisor', 'manager'];
        $existingRoles = \Spatie\Permission\Models\Role::whereIn('name', $supervisorRoles)->pluck('name')->toArray();

        return Inertia::render("SuperAdmin/Users/index", [
            "users" => $this->usersService->getAll()->load(['roles', 'divisi', 'jabatan', 'atasan']),
            "roles" => \Spatie\Permission\Models\Role::all(),
            "divisi" => $this->divisiService->getAllDivisi(),
            "jabatan" => $this->jabatanService->getAllJabatan(),
            "availableSupervisors" => !empty($existingRoles) ? \App\Models\User::role($existingRoles)->get() : [],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string',
            'kode_karyawan' => 'nullable|string|max:50|unique:users',
            'divisi_id' => 'nullable|exists:divisi,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'atasan_id' => 'nullable|exists:users,id',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $user = $this->usersService->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'kode_karyawan' => $request->kode_karyawan,
            'divisi_id' => $request->divisi_id,
            'jabatan_id' => $request->jabatan_id,
            'atasan_id' => $request->atasan_id,
            'status' => $request->status,
        ]);

        if ($request->filled('role')) {
            $user->assignRole($request->role);
        }

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $user = $this->usersService->find($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string',
            'kode_karyawan' => 'nullable|string|max:50|unique:users,kode_karyawan,' . $id,
            'divisi_id' => 'nullable|exists:divisi,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'atasan_id' => 'nullable|exists:users,id',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'kode_karyawan' => $request->kode_karyawan,
            'divisi_id' => $request->divisi_id,
            'jabatan_id' => $request->jabatan_id,
            'atasan_id' => $request->atasan_id,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $this->usersService->update($id, $data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->usersService->delete($id);
        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
