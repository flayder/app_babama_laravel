<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Shaon
 * Date: 2/1/2021
 * Time: 2:00 PM.
 */

namespace App\Http\Traits;

trait ContentDelete
{
    public static function booted(): void
    {
        static::deleting(function ($model): void {
            if (isset($model->contentMedia->description->image)) {
                removeFile(config('location.content.path').'/'.$model->contentMedia->description->image);
            }
            $model->contentMedia()->delete();
            $model->contentDetails()->delete();
        });
    }
}
