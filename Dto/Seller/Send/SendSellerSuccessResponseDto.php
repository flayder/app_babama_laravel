<?php

namespace App\Dto\Seller\Send;

class SendSellerSuccessResponseDto
{
    public int $order;

    public function __construct(array $data)
    {
        $this->order = (int)$data['order'];
    }
}
