<?php

namespace App\Repositories\SuperAdmin\Permission;

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function all(): Collection
    {
        return Permission::all();
    }

    public function findById(int $id): ?Permission
    {
        return Permission::findById($id, 'web');
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $permission = $this->findById($id);
        if ($permission) {
            return $permission->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $permission = $this->findById($id);
        if ($permission) {
            return $permission->delete();
        }
        return false;
    }
}
