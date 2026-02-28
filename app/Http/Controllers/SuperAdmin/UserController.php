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

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    public function index()
    {
        return Inertia::render("SuperAdmin/Users/index", [
            "users" => $this->usersService->getAll()->load('roles'),
            "roles" => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_karyawan' => 'required|string|max:50|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string',
            'jabatan' => 'nullable|string|max:100',
            'divisi' => 'nullable|string|max:100',
            'tanggal_masuk' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
        ]);

        $user = $this->usersService->create([
            'kode_karyawan' => $request->kode_karyawan,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'jabatan' => $request->jabatan,
            'divisi' => $request->divisi,
            'tanggal_masuk' => $request->tanggal_masuk,
            'status' => $request->status ?? 'active',
        ]);

        if ($request->filled('role')) {
            $user->assignRole($request->role);
        }

        return redirect()->back()->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $this->usersService->find($id);

        $request->validate([
            'kode_karyawan' => 'required|string|max:50|unique:users,kode_karyawan,' . $id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string',
            'jabatan' => 'nullable|string|max:100',
            'divisi' => 'nullable|string|max:100',
            'tanggal_masuk' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = [
            'kode_karyawan' => $request->kode_karyawan,
            'name' => $request->name,
            'email' => $request->email,
            'jabatan' => $request->jabatan,
            'divisi' => $request->divisi,
            'tanggal_masuk' => $request->tanggal_masuk,
            'status' => $request->status ?? 'active',
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $this->usersService->update($id, $data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        } else {
            // Jika dikosongkan, hapus semua role
            $user->syncRoles([]);
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
