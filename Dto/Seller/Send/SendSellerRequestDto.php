<?php

namespace App\Dto\Seller\Send;

class SendSellerRequestDto
{
    public string $key;
    public string $action;
    public int $service;
    public string $link;
    public ?string $comments;
    public ?int $quantity;
    public ?int $interval;
    public ?int $runs;
    public ?string $usernames;

    public function __construct(array $data)
    {
        $this->key = $data['key'];
        $this->action = $data['action'];
        $this->service = (int)$data['service'];
        $this->link = $data['link'];

        if (isset($data['comments']) && !empty($data['comments'])) {
            $this->comments = $data['comments'];
        }

        if (isset($data['quantity']) && !empty($data['quantity'])) {
            $this->quantity = (int)$data['quantity'];
        }

        if (isset($data['interval']) && !empty($data['interval'])) {
            $this->interval = (int)$data['interval'];
        }

        if (isset($data['runs']) && !empty($data['runs'])) {
            $this->runs = (int)$data['runs'];
        }

        if (isset($data['usernames']) && !empty($data['usernames'])) {
            $this->usernames = (int)$data['usernames'];
        }
    }
}
