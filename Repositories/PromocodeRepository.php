<?php

namespace App\Repositories;

use App\Models\Promocode;
use Illuminate\Database\Eloquent\Collection;

class PromocodeRepository extends BaseRepository
{
    public function model()
    {
        return Promocode::class;
    }

    public function getAll(): Collection|array
    {
        return $this->startConditions()::get();
    }

    public function getById(int $id): Promocode
    {
        return $this->startConditions()::findOrFail($id);
    }

    public function getByCode(string $code): Promocode
    {
        return $this->startConditions()::where('code', $code)->firstOrFail();
    }


}
