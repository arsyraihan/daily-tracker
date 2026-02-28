<?php

namespace App\Repositories\SuperAdmin\Role;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function all(): Collection
    {
        return Role::all();
    }

    public function findById(int $id): ?Role
    {
        return Role::findById($id, 'web');
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $role = $this->findById($id);
        if ($role) {
            return $role->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $role = $this->findById($id);
        if ($role) {
            return $role->delete();
        }
        return false;
    }
}
