<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class FreewalletPaymentSystemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' 						=> $this->id,
            'icon'						=> $this->icon,
            'name' 						=> $this->name,
            'freewallet_notification' 	=> $this->freewallet_notification,
            'requisite_notification'  	=> $this->requisite_notification
        ];
    }
}
