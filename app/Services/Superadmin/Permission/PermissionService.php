<?php

namespace App\Services\SuperAdmin\Permission;

use App\Repositories\SuperAdmin\Permission\PermissionRepositoryInterface;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    protected $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function getAllPermissions(): Collection
    {
        return $this->permissionRepository->all();
    }

    public function createPermission(array $data): Permission
    {
        return $this->permissionRepository->create($data);
    }

    public function deletePermission(int $id): bool
    {
        return $this->permissionRepository->delete($id);
    }

    public function updatePermission(int $id, array $data): bool
    {
        return $this->permissionRepository->update($id, $data);
    }
}
