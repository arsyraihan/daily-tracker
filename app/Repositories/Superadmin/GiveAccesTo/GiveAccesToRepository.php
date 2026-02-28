<?php

namespace App\Repositories\Superadmin\GiveAccesTo;

class GiveAccesToRepository implements GiveAccesToRepositoryInterface
{
    public function all()
    {
        return \App\Models\User::with(['roles', 'permissions'])->get();
    }

    public function findById(int $id)
    {
        return \App\Models\User::with(['roles', 'permissions'])->findOrFail($id);
    }

    public function assignRole(int $userId, string $role)
    {
        $user = $this->findById($userId);
        return $user->assignRole($role);
    }

    public function syncRoles(int $userId, array $roles)
    {
        $user = $this->findById($userId);
        return $user->syncRoles($roles);
    }

    public function givePermission(int $userId, string $permission)
    {
        $user = $this->findById($userId);
        return $user->givePermissionTo($permission);
    }

    public function syncPermissions(int $userId, array $permissions)
    {
        $user = $this->findById($userId);
        return $user->syncPermissions($permissions);
    }

    public function delete(int $id)
    {
        $user = $this->findById($id);
        return $user->delete();
    }
}
