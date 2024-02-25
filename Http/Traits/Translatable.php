<?php

declare(strict_types=1);

namespace App\Http\Traits;

use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Translatable
{
    public static function booted(): void
    {
        try {
            DB::connection()->getPdo();

            if ('admin' != Auth::getDefaultDriver()) {
                $lang = app()->getLocale();
//            if($lang == 'en'){
//                $lang ='us';
//            }

                $languageId = Language::where('short_name', $lang)->first();
                $defaultLang = Language::first();

                static::addGlobalScope('language', function (Builder $builder) use ($languageId): void {
                    if ($languageId) {
                        $builder->where('language_id', $languageId->id);
                    }
                });
            }
        } catch (\Exception $e) {
        }
    }
}
