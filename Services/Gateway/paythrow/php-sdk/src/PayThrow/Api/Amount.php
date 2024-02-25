<?php

declare(strict_types=1);

namespace PayThrow\Api;

use PayThrow\Common\PayThrowModel;

/**
 * Class Amount.
 *
 * @property float totalAmount
 * @property string currency
 */
class Amount extends PayThrowModel
{
    /**
     * @param float $amount
     *
     * @return $this
     */
    public function setTotal($amount)
    {
        $this->totalAmount = $amount;

        return $this;
    }

    public function getTotal()
    {
        return $this->totalAmount;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}
