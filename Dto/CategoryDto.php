<?php

namespace App\Dto;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CategoryDto
{
    public string $title;
    public string $description = '';
    public ?string $image;
    public bool $status;
    public bool $priority;
    public ?Carbon $createdAt;
    public bool $validLink;
    public ?string $domainLink = '';

    public static function buildFromModel(Category $model): CategoryDto
    {
        $dto = new self();

        $dto->title = $model->category_title;
        $dto->description = $model->category_description;
        $dto->image = $model->image;
        $dto->status = (bool)$model->status;
        $dto->priority = (bool)$model->priority;
        $dto->createdAt = $model->createdAt;
        $dto->validLink = (bool)$model->validLink;
        $dto->domainLink = $model->domainLink;

        return $dto;

    }

    public static function build(array $data = []): CategoryDto
    {
        $dto = new self();

        $dto->title = $data['category_title'];
        $dto->description = !empty($data['category_description']) ? $data['category_description'] : '';
        $dto->image = !empty($data['image']) ? $data['image'] : '';
        $dto->status = (bool)$data['status'];
        $dto->priority = (bool)$data['priority'];
        $dto->validLink = (bool)$data['validLink'];
        $dto->domainLink = !empty($data['domainLink']) ? $data['domainLink'] : '';

        return $dto;
    }
}
