<?php

namespace App\Repositories\Superadmin\Jabatan;

use App\Models\Jabatan;

class JabatanRepository implements JabatanRepositoryInterface
{
    public function getAll()
    {
        return Jabatan::orderBy('level', 'asc')->get();
    }

    public function findById($id)
    {
        return Jabatan::findOrFail($id);
    }

    public function create(array $data)
    {
        return Jabatan::create($data);
    }

    public function update($id, array $data)
    {
        $jabatan = $this->findById($id);
        $jabatan->update($data);
        return $jabatan;
    }

    public function delete($id)
    {
        $jabatan = $this->findById($id);
        return $jabatan->delete();
    }
}
