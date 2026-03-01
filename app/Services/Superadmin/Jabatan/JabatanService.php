<?php

namespace App\Services\Superadmin\Jabatan;

use App\Repositories\Superadmin\Jabatan\JabatanRepositoryInterface;

class JabatanService
{
    protected $jabatanRepository;

    public function __construct(JabatanRepositoryInterface $jabatanRepository)
    {
        $this->jabatanRepository = $jabatanRepository;
    }

    public function getAllJabatan()
    {
        return $this->jabatanRepository->getAll();
    }

    public function createJabatan(array $data)
    {
        return $this->jabatanRepository->create($data);
    }

    public function updateJabatan($id, array $data)
    {
        return $this->jabatanRepository->update($id, $data);
    }

    public function deleteJabatan($id)
    {
        return $this->jabatanRepository->delete($id);
    }
}
