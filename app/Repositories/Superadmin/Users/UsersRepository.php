<?php

namespace App\Repositories\SuperAdmin\Users;

use App\Models\User;

class UsersRepository implements UsersRepositoryInterface
{
    public function all()
    {
        return User::latest()->get();
    }

    public function findById(int $id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = $this->findById($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id)
    {
        return User::destroy($id);
    }
}
