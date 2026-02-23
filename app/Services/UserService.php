<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAllUsers(): Collection
    {
        return $this->userRepository->findAll();
    }

    public function createUser(array $data): User
    {
        $data['password'] = bcrypt($data['password']);
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, array $data): User
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }
}
