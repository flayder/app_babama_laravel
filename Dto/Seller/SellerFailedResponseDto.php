<?php

namespace App\Dto\Seller;

class SellerFailedResponseDto
{
    public string $error;

    public function __construct(string $error)
    {
        $this->error = $error;
    }
}
