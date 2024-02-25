<?php

namespace App\Dto\Seller\OrderStatus;

class OrderStatusSellerSuccessResponseDto
{
    public int $id;
    public int $orderId;
    public float $charge;
    public int $startCount;
    public string $status;
    public int $remains;
    public string $currency;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->orderId = (int)$data['order_id'];
        $this->charge = (float)$data['charge'];
        $this->startCount = (int)$data['start_count'];
        $this->status = $data['status'];
        $this->remains = (int)$data['remains'];
        $this->currency = $data['currency'];
    }
}
