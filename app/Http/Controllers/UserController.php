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

        $users = $this->userService->getAllUsers();

        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->userService->createUser($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'User Dibuat!');
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        $this->userService->updateUser($id, $request->validated());

        return redirect()->route('users.index')
            ->with('success', 'Data user DiUpdate!');
    }

    public function destroy(int $id)
    {
        $this->userService->deleteUser($id);

        return redirect()->route('users.index')
            ->with('success', 'User Digusur!');
    }
}
