<?php

namespace App\Helper;

use App\Contracts\Seller;
use App\Models\ApiProvider;
use Illuminate\Support\Facades\Log;

class SellerHelper
{

    public static function getSellerClass(ApiProvider $apiProvider): Seller
    {

        $sellerServiceClass = config('services.sellers.classes.' . str_replace('.', '_', $apiProvider->api_name));

        return new $sellerServiceClass();

    }
}
