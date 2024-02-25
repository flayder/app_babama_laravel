<?php

namespace App\Services;

use App\Models\Promocode;

class OrderDiscountService
{
    public function getPriceAfterDiscount(Promocode $promocode, $price)
    {
        if ($promocode->discount_in == '%') {
            $price -= ($price / 100) * $promocode->amount;
        } elseif ($promocode->discount_in == 'rub') {
            $price -= $promocode->amount;
        }

        return $price;
    }
}
