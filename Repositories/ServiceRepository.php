<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository extends BaseRepository
{
    public function model()
    {
        return Service::class;
    }

    public function getAll(): Collection|array
    {
        return $this->startConditions()::get();
    }

    public function getById(int $id): Service
    {
        return $this->startConditions()::findOrFail($id);
    }

    public function getUserRate(int $service): Service
    {
        return $this->startConditions()::userRate()->findOrFail($service);
    }


}
