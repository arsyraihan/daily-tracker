<?php

namespace App\Services\Superadmin\GiveAccesTo;

use App\Repositories\Superadmin\GiveAccesTo\GiveAccesToRepositoryInterface;

class GiveAccesToService
{
    protected $repository;

    public function __construct(GiveAccesToRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function syncRoles(int $id, array $roles)
    {
        return $this->repository->syncRoles($id, $roles);
    }

    public function syncPermissions(int $id, array $permissions)
    {
        return $this->repository->syncPermissions($id, $permissions);
    }
}
