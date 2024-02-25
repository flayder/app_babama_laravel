<?php

namespace App\Dto;

use App\Services\Singleton;
use Illuminate\Database\Eloquent\Model;

abstract class Dto extends Singleton
{
    abstract public static function build(array $data = []);

    abstract public static function buildFromModel(Model $model);
}
