<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (empty($this->seo_title)) {
            $this->seo_title = $this->category_title;
        }

        if (empty($this->seo_h1)) {
            $this->seo_h1 = $this->category_title;
        }

        if (empty($this->seo_description)) {
            $this->seo_description = $this->category_description;
        }

        return [
            'id' => $this->id,
            'h1' => $this->seo_h1,
            'name' => $this->category_title,
            'description' => $this->category_description,
            'icon' => '/'.$this->image,
            'valid_link' => (bool)$this->valid_link,
            'status' => (bool)$this->status,
            'domain_link' => $this->domain_link,
            'priority' => $this->priority,
            'slug' => $this->slug,
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ]
        ];
    }
}
