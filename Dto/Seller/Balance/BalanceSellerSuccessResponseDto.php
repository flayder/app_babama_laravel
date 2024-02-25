<?php

namespace App\Dto\Seller\Balance;

class BalanceSellerSuccessResponseDto
{
    public float $balance;
    public string $currency;

    public function __construct(array $data)
    {
        $this->balance = (float)$data['balance'];
        $this->currency = $data['currency'];
    }
}
