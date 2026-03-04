<?php

namespace App\Repositories\Manager\Absensi;

interface SesiAbsensiRepositoryInterface
{
    public function getAllByDivision(int $divisionId, ?string $date = null);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function closeSession(int $id);
}
