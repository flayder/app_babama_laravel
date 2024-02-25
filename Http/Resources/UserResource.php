<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'username' => $this->username,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'phone' => $this->phone,
            'balance' => $this->balance,
            'image' => $this->image,
            'address' => $this->address,
            'email_verified' => (bool)$this->email_verified_at,
            'created_at' => $this->created_at,
        ];
    }
}
