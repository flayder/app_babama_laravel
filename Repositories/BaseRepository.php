<?php


namespace App\Repositories;

use App\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements Repository
{

    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->model());
    }

    /**
     * @return Model
     */
    protected function startConditions(): Model
    {
        return clone $this->model;
    }
}
