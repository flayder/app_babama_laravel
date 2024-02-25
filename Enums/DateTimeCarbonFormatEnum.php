<?php

namespace App\Enums;

enum DateTimeCarbonFormatEnum: string
{
    case TIME = 'H:i:s';
    case DATE = 'Y-m-d';
    case DATETIME = 'Y-m-d H:i:s';
}
