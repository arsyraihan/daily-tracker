<?php

namespace App\Repositories\Contracts;

use app\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findAll(): Collection;
    public function create(array $data): User;
    public function findById(int $id): ?User;
    public function update(int $id, array $data): User;
    public function delete(int $id): bool;
}
