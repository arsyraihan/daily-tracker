<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index()
    {
        return Inertia::render('Users/index', [
            'users' => $this->userService->getAllUsers(),
            'roles' => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $userData = $request->validated();
        $user = $this->userService->createUser($userData);
        
        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->back()->with('success', 'User Berhasil Ditambahkan!');
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        $userData = $request->validated();
        $this->userService->updateUser($id, $userData);
        
        $user = \App\Models\User::find($id);
        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->back()->with('success', 'User Berhasil Diperbarui!');
    }

    public function destroy(int $id)
    {
        $this->userService->deleteUser($id);

        return redirect()->back()->with('success', 'User Berhasil Dihapus!');
    }
}
