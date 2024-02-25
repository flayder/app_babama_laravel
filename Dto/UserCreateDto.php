<?php

namespace App\Dto;

use App\Helper\UserHelper;

class UserCreateDto
{
    public ?string $firstName = '';
    public ?string $lastName = '';
    public ?string $userName = '';
    public string $email;
    public ?string $phoneCode = '';
    public ?string $phone = '';
    public ?string $address = '';
    public string $password;
    public bool $isNew = false;

    public function create()
    {
        return UserHelper::userCreate($this);
    }
}
