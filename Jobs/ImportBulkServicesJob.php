<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ApiProvider;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ixudra\Curl\Facades\Curl;

class ImportBulkServicesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /** Execute the job. */
    public function handle(): void
    {
        $req = $this->request;

        $provider = ApiProvider::find($req['provider']);
        $apiLiveData = Curl::to($provider['url'])
            ->withData(['key' => $provider['api_key'], 'action' => 'services'])->post();
        $apiServicesData = json_decode($apiLiveData);
        $count = 0;
        foreach ($apiServicesData as $apiService) {
            $all_category = Category::all();
            $services = Service::all();
            $insertCat = 1;
            $existService = 0;
            foreach ($all_category as $categories) {
                if ($categories->category_title == $apiService->category) {
                    $insertCat = 0;
                }
            }
            if (1 == $insertCat) {
                $cat = new Category();
                $cat->category_title = $apiService->category;
                $cat->status = 1;
                $cat->save();
            }
            foreach ($services as $service) {
                if ($service->api_service_id == $apiService->service) {
                    $existService = 1;
                }
            }
            if (1 != $existService) {
                $service = new Service();
                $idCat = Category::where('category_title', $apiService->category)->first()->id ?? null;
                $service->service_title = $apiService->name;
                $service->category_id = $idCat;
                $service->min_amount = $apiService->min;
                $service->max_amount = $apiService->max;
                $increased_price = ($apiService->rate / 100) * 10;
                $service->price = $apiService->rate + $increased_price;
                $service->service_status = 1;
                $service->api_provider_id = $req['provider'];
                $service->api_service_id = $apiService->service;
                $service->drip_feed = $apiService->dripfeed;
                $service->api_provider_price = $apiService->rate;
                $service->save();
            }
            ++$count;
            if ('all' == $req['import_quantity']) {
                continue;
            } elseif ($req['import_quantity'] == $count) {
                break;
            }
        }
    }
}
