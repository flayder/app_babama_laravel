<?php


namespace App\Contracts;


use App\Dto\Order\OrderCreateDto;
use App\Enums\OrderStatusEnum;
use App\Models\User;
use App\Services\SellerService;

interface Order
{

    public function store(OrderCreateDto $createDto);

    public function show();

    public function changeStatus(OrderStatusEnum $status);

    public function pay();

    public function copy();

    public function getOrdersByUser(User $user);

    public function get();

    public function send(SellerService $sellerService);
}
