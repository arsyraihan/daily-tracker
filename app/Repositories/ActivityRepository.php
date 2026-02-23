<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Support\Collection;

class ActivityRepository implements ActivityRepositoryInterface
{
    public function getAllWithUser(): Collection
    {
        return Activity::with('user')->get();
    }

    public function getByUser(int $userId): Collection
    {
        return Activity::where('user_id', $userId)->orderBy('tanggal', 'desc')->get();
    }

    public function create(array $data): Activity
    {
        return Activity::create($data);
    }

    public function update(int $id, array $data): Activity
    {
        $activity = Activity::findOrFail($id);
        $activity->update($data);
        return $activity;
    }

    public function delete(int $id): bool
    {
        $activity = Activity::findOrFail($id);
        return (bool) Activity::destroy($id);
    }
}
