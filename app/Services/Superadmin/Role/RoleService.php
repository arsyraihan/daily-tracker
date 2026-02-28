<?php

namespace App\Services\SuperAdmin\Role;

use App\Repositories\SuperAdmin\Role\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    protected $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getAllRoles(): Collection
    {
        return $this->roleRepository->all();
    }

    public function createRole(array $data): Role
    {
        return $this->roleRepository->create($data);
    }

    public function updateRole(int $id, array $data): bool
    {
        return $this->roleRepository->update($id, $data);
    }

    public function deleteRole(int $id): bool
    {
        return $this->roleRepository->delete($id);
    }

    public function syncPermissions(int $roleId, array $permissions): bool
    {
        $role = $this->roleRepository->findById($roleId);
        if ($role) {
            $role->syncPermissions($permissions);
            return true;
        }
        return false;
    }
}
    