<?php

namespace App\Dto\Seller\Services;

class ServicesSellerSuccessResponseDto
{
    public int $service;
    public string $name;
    public string $type;
    public string $category;
    public float $rate;
    public int $min;
    public int $max;
    public bool $refill;
    public bool $cancel;

    public function __construct(array $data)
    {
        $this->service = (int)$data['service'];
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->category = $data['category'];
        $this->rate = (float)$data['rate'];
        $this->min = (int)$data['min'];
        $this->max = (int)$data['max'];
        $this->refill = (bool)$data['refill'];
        $this->cancel = (bool)$data['cancel'];
    }
}
