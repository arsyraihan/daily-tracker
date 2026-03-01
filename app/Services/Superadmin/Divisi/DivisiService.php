<?php

namespace App\Services\Superadmin\Divisi;

use App\Repositories\Superadmin\Divisi\DivisiRepositoryInterface;

class DivisiService
{
    protected $divisiRepository;

    public function __construct(DivisiRepositoryInterface $divisiRepository)
    {
        $this->divisiRepository = $divisiRepository;
    }

    public function getAllDivisi()
    {
        return $this->divisiRepository->getAll();
    }

    public function createDivisi(array $data)
    {
        return $this->divisiRepository->create($data);
    }

    public function updateDivisi($id, array $data)
    {
        return $this->divisiRepository->update($id, $data);
    }

    public function deleteDivisi($id)
    {
        return $this->divisiRepository->delete($id);
    }
}
