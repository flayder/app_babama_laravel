<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LinkController extends Controller
{

    public function checkValid(Category $category, Request $request)
    {
        if (!$category->valid_link) {
            return response([], 200);
        }
        try {
            $request->validate([
                'link' => 'required|string'
            ]);
        } catch (\Throwable $e) {
            return response([
                'message' => 'Ссылка ' . $link . ' не доступна',
                'detail' => $e->getMessage,
                'code' => 400
            ], 400);
        }


        $link = $request->input('link');

        $client = new Client();
        try {
            $code = $client->get($link)->getStatusCode();

            return response([
                'message' => 'Ссылка доступна',
                'code' => $code
            ], 200);
        } catch (\Throwable $e) {
            return response([
                'message' => 'Ссылка ' . $link . ' не доступна',
                'detail' => $e->getMessage(),
                'code' => $e->getCode()
            ], $e->getCode());
        }


    }
}
