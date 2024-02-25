<?php

namespace App\Dto\Seller\Services;

class ServicesSellerRequestDto
{
    public string $key;
    public string $action;

    public function __construct(array $data)
    {
        $this->key = $data['key'];
        $this->action = $data['action'];
    }
}
