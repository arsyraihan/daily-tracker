<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function findAll(): Collection
    {
        return User::Latest()->get();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
    public function findById(int $id): ?user
    {
        return User::find($id);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id): bool
    {
        return User::destroy($id);
    }
}
