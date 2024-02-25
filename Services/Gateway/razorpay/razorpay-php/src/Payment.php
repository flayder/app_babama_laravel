<?php

declare(strict_types=1);

namespace Razorpay\Api;

use Requests;

class Payment extends Entity
{
    /** @param $id Payment id */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = [])
    {
        return parent::all($options);
    }

    /**
     * Patches given payment with new attributes.
     *
     * @param array $attributes
     *
     * @return Payment
     */
    public function edit($attributes = [])
    {
        $url = $this->getEntityUrl().$this->id;

        return $this->request(Requests::PATCH, $url, $attributes);
    }

    /** @param $id Payment id */
    public function refund($attributes = [])
    {
        $refund = new Refund();

        $attributes = array_merge($attributes, ['payment_id' => $this->id]);

        return $refund->create($attributes);
    }

    /** @param $id Payment id */
    public function capture($attributes = [])
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/capture';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function transfer($attributes = [])
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/transfers';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function refunds()
    {
        $refund = new Refund();

        $options = ['payment_id' => $this->id];

        return $refund->all($options);
    }

    public function transfers()
    {
        $transfer = new Transfer();

        $transfer->payment_id = $this->id;

        return $transfer->all();
    }

    public function bankTransfer()
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/bank_transfer';

        return $this->request('GET', $relativeUrl);
    }
}
