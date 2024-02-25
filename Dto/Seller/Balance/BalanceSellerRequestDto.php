<?php

namespace App\Dto\Seller\Balance;

class BalanceSellerRequestDto
{
    public string $key;
    public string $action;

    public function __construct(array $data)
    {
        $this->key = $data['key'];
        $this->action = $data['action'];
    }
}
