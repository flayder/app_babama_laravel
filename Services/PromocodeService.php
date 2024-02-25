<?php

namespace App\Services;

use App\Enums\PromocodeDiscountTypeEnum;
use App\Models\Promocode;
use App\Models\PromocodeUse;
use App\Models\User;
use App\Repositories\PromocodeRepository;

class PromocodeService
{
    private ?Promocode $promocode;
    private PromocodeRepository $promocodeRepository;

    public function __construct(?string $code = null)
    {
        $this->promocodeRepository = app(PromocodeRepository::class);
        try {
            if (!empty($code)) {
                $this->promocode = $this->promocodeRepository->getByCode($code);
            } else {
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            $this->promocode = null;
        }

    }
    public static function check($code): bool
    {
        /**
         * @var PromocodeRepository $promocodeRepository
         */
        $promocodeRepository = app(PromocodeRepository::class);

        try {
            $promocodeRepository->getByCode($code);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function use(User $user)
    {
        $promocodeUse = new PromocodeUse([
            'user_id' => $user->id,
            'promocode_id' => $this->promocode->id,
        ]);

        $promocodeUse->save();

        $this->promocode->count_remains -= 1;
        $this->promocode->save();
    }


    public function checkByUser(User $user): bool
    {
        $promocodeUses = PromocodeUse::where('user_id', $user->id)->where('promocode_id', $this->promocode?->id)->first();

        return !empty($promocodeUses);

    }

    public function get(): ?Promocode
    {
       return $this->promocode;
    }

    public function getDiscount(float $price): float
    {
        if ($this->promocode?->discount_in == PromocodeDiscountTypeEnum::PERCENT->value) {
            $discount = ($price / 100) * $this->promocode->amount;
        } elseif ($this->promocode?->discount_in == PromocodeDiscountTypeEnum::SUM->value) {
            $discount = $this->promocode->amount;
        } else {
            $discount = '0.0';
        }


        return $discount;
    }
}
