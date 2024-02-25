<?php

declare(strict_types=1);

namespace Razorpay\Api;

class Refund extends Entity
{
    /** @param $id Refund id */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function create($attributes = [])
    {
        return parent::create($attributes);
    }

    public function all($options = [])
    {
        return parent::all($options);
    }
}
