<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /** @OA\Post ( path="/api/promo-code/checkout",
     *     @OA\Parameter(in="path", name="name", required=false, @OA\Schema(type="string"),
     *     @OA\Examples(example="string", value="Jon", summary="An string value.") ),
     *     @OA\Parameter(in="path", name="email", required=false, @OA\Schema(type="string"),
     *     @OA\Examples(example="string", value="jon_doe@mail.com", summary="An string value.") ),
     *     @OA\Response( response=200, description="OK")
     * ) */
    public function update(): void
    {
    }
}
