<?php

declare(strict_types=1);

namespace Razorpay\Api;

class VirtualAccount extends Entity
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

    public function close()
    {
        $relativeUrl = $this->getEntityUrl().$this->id;

        $data = [
            'status' => 'closed',
        ];

        return $this->request('PATCH', $relativeUrl, $data);
    }

    public function payments()
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/payments';

        return $this->request('GET', $relativeUrl);
    }
}
