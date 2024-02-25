<?php

namespace App\Dto\Service;

use App\Enums\CurrencyEnum;
use App\Enums\ServiceCreateStatusEnum;
use App\Models\Service;

class ServiceCreateDto
{
    public string $serviceTitle;
    public int $minAmount;
    public int $maxAmount;
    public bool $serviceStatus = true;
    public int $apiProviderId;
    public int $apiServiceId;
    public float $apiProviderPrice;
    public CurrencyEnum $currency;

    public function create(&$copies): ServiceCreateStatusEnum
    {
        $status = ServiceCreateStatusEnum::CREATE;
        $service = new Service();
        $serviceCheck =  Service::where('api_service_id', $this->apiServiceId)->where('api_provider_id', $this->apiProviderId)->get();

        if ($serviceCheck->count() > 0) {
            $status = ServiceCreateStatusEnum::UPDATE;
            foreach ($serviceCheck as $service) {
                /**
                 * @var Service $service
                 */
                if ($serviceCheck->count() > 1) {
                    $copies[$service->api_service_id][] = [
                        'id' => $service->id,
                        'api_service_id' => $service->api_service_id,
                        'name' => $service->service_title
                    ];
                }
                $this->save($service);
            }
        } else {
            $this->save($service);
        }

        if (!$service->save()) {
            $status = ServiceCreateStatusEnum::FAILED;
        }

        return $status;
    }

    private function save(Service $service): Service
    {
        if (empty($service->service_title)) {
            $service->service_title = $this->serviceTitle;
            $service->api_provider_id = $this->apiProviderId;
            $service->api_service_id = $this->apiServiceId;
        }
        $service->service_status = $this->serviceStatus;
        $providerPrice = $this->apiProviderPrice;
        $percent = $service->amount_percent;
        $fullPrice = $providerPrice;
        if ($this->currency->value == CurrencyEnum::USD->value) {
            $fullPrice = $fullPrice * 85;
        }

        $service->price = !empty($percent) && $percent > 0 ? $fullPrice * ($percent / 100) : $fullPrice * 3;
        $service->min_amount = $this->minAmount;
        $service->max_amount = $this->maxAmount;
        $service->api_provider_price = $this->apiProviderPrice;

        return $service;
    }

    public function sync(&$copies)
    {
        $serviceCheck =  Service::where('api_service_id', $this->apiServiceId)->where('api_provider_id', $this->apiProviderId)->get();

        if ($serviceCheck->count() > 1) {
            foreach ($serviceCheck as $service) {
                /**
                 * @var Service $service
                 */
                if ($serviceCheck->count() > 1) {
                    $copies[$service->api_service_id][] = [
                        'id' => $service->id,
                        'api_service_id' => $service->api_service_id,
                        'name' => $service->service_title
                    ];
                }
                $service->delete();
                break;
            }
        }
    }
}


