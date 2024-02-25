<?php

namespace App\Dto\Order;

use App\Http\Requests\Api\OrderCreateRequest;
use App\Models\User;

class OrderCreateDto
{
    public User $user;
    public int $category;
    public int $service;
    public ?int $activity;
    public string $link;
    public int $quantity;
    public ?int $country;
    public ?string $gender;
    public ?string $promocode;
    public ?int $runs;
    public ?int $interval;
    public ?int $newUser;


    public function __construct(OrderCreateRequest $request, User $user, bool $isNew = false)
    {
        $this->user = $user;
        $this->category = $request->input('category');
        $this->service = $request->input('service');
        $this->activity = $request->input('activity');
        $this->link = $request->input('link');
        $this->quantity = $request->input('quantity');
        $this->country = $request->input('country');
        $this->gender = $request->input('gender');
        $this->promocode = $request->input('promocode_code');
        $this->runs = $request->input('runs');
        $this->interval = $request->input('interval');
        $this->newUser = $isNew;
    }
}
