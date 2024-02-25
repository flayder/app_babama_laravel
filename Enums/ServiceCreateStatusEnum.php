<?php

namespace App\Enums;

enum ServiceCreateStatusEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case FAILED = 'failed';
}
