<?php

namespace App\Repositories\Superadmin\GiveAccesTo;

interface GiveAccesToRepositoryInterface
{
    public function all();
    public function findById(int $id);
    public function assignRole(int $userId, string $role);
    public function syncRoles(int $userId, array $roles);
    public function givePermission(int $userId, string $permission);
    public function syncPermissions(int $userId, array $permissions);
    public function delete(int $id);
}
