<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     */
    public function toArray($request): array
    {
        if (empty($this->seo_title)) {
            $this->seo_title = $this->title;
        }

        if (empty($this->seo_h1)) {
            $this->seo_h1 = $this->title;
        }

        if (empty($this->seo_description)) {
            $this->seo_description = $this->description;
        }

        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'h1' => $this->seo_h1,
            'name' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'link_demo' => $this->link_demo,
            'activity_description' => $this->activity_description,
            'has_comment' => (bool)$this->has_comment,
            'status' => (bool)$this->status,
            'priority' => $this->priority,
            'slug' => $this->slug,
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ]
        ];
    }
}
