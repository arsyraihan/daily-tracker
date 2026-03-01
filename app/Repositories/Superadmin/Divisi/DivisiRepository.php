<?php

namespace App\Repositories\Superadmin\Divisi;

use App\Models\Divisi;

class DivisiRepository implements DivisiRepositoryInterface
{
    public function getAll()
    {
        return Divisi::latest()->get();
    }

    public function findById($id)
    {
        return Divisi::findOrFail($id);
    }

    public function create(array $data)
    {
        return Divisi::create($data);
    }

    public function update($id, array $data)
    {
        $divisi = $this->findById($id);
        $divisi->update($data);
        return $divisi;
    }

    public function delete($id)
    {
        $divisi = $this->findById($id);
        return $divisi->delete();
    }
}
