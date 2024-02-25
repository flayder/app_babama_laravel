<?php

declare(strict_types=1);

namespace Razorpay\Api;

interface ArrayableInterface
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
