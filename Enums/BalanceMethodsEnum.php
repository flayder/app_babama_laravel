<?php

namespace App\Enums;

enum BalanceMethodsEnum: string
{
    case ADD = 'add';
    case RESET = 'reset';
    case DEDUCT = 'deduct';
    case UPDATE = 'update';
}
