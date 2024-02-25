<?php

declare(strict_types=1);

namespace Razorpay\Api;

use Countable;

class Collection extends Entity implements Countable
{
    public function count()
    {
        $count = 0;

        if (isset($this->attributes['count'])) {
            return $this->attributes['count'];
        }

        return $count;
    }
}
