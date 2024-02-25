<?php

declare(strict_types=1);

namespace Razorpay\Api;

class Subscription extends Entity
{
    public function create($attributes = [])
    {
        return parent::create($attributes);
    }

    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = [])
    {
        return parent::all($options);
    }

    public function cancel($attributes = [])
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/cancel';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function createAddon($attributes = [])
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/addons';

        return $this->request('POST', $relativeUrl, $attributes);
    }
}
