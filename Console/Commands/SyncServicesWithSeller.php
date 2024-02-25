<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\Seller\Services\ServicesSellerSuccessResponseDto;
use App\Dto\Service\ServiceCreateDto;
use App\Enums\CurrencyEnum;
use App\Enums\ServiceCreateStatusEnum;
use App\Jobs\RefundOrder;
use App\Models\ApiProvider;
use App\Models\Order;
use App\Models\Service;
use App\Services\Integrations\PartnerSoc\PartnerSocService;
use App\Services\Integrations\SmmPanel\SmmPanelService;
use App\Services\Integrations\SmmPanelus\SmmPanelusService;
use App\Services\Integrations\SocRocket\SocRocketService;
use App\Services\Integrations\SSmm\SSmmService;
use App\Services\Integrations\WebSmm\WebSmmService;
use App\Services\SellerService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Ixudra\Curl\Facades\Curl;

class SyncServicesWithSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:services:soc-prof';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync for Seller Services';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array<SellerService>
     */
    private function getPartners(): array
    {
        $socProfSeller = new PartnerSocService();
        $smmPanelusSeller = new SmmPanelusService();
        $SSmmSeller = new SSmmService();
        $smmPanelSeller = new SmmPanelService();
        $socRocketSeller = new SocRocketService();

        return [
            1 => new SellerService($socProfSeller),
            2 => new SellerService($smmPanelusSeller),
            4 => new SellerService($SSmmSeller),
            5 => new SellerService($smmPanelSeller),
            6 => new SellerService($socRocketSeller),
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $partners = $this->getPartners();
        Service::where('service_status', 1)->update(['service_status' => 0]);

        foreach ($partners as $k => $seller) {
            $this->sync($seller, $k);
        }
    }

    private function sync(SellerService $seller, int $apiSellerID)
    {
        $sellerSettings = ApiProvider::find($apiSellerID);
        $currency = CurrencyEnum::tryFrom($sellerSettings->currency);
        $services = $seller->getServices();

        $count = $services->count();

        $errors = new Collection();
        $created = new Collection();
        $updated = new Collection();

        $copies = [];

        $this->info('Services update start');
        $this->info('Count: ' . $count);
        $this->newLine();
        foreach ($services as $k => $service) {
            /**
             * @var ServicesSellerSuccessResponseDto $service
             */
            $this->comment(($k + 1) . " / " . $count . " Start..");
            $serviceDto = new ServiceCreateDto();
            $serviceDto->serviceTitle = $service->name;
            $serviceDto->maxAmount = $service->max;
            $serviceDto->minAmount = $service->min;
            $serviceDto->apiProviderPrice = $service->rate;
            $serviceDto->apiServiceId = $service->service;
            $serviceDto->apiProviderId = $apiSellerID;
            $serviceDto->currency = $currency;

            $statusCreate = $serviceDto->create($copies);

            if ($statusCreate->value == ServiceCreateStatusEnum::CREATE->value) {
                $this->info(($k + 1) . " / " . $count . " Created");
                $created->push($service);
            } elseif ($statusCreate->value == ServiceCreateStatusEnum::UPDATE->value) {
                $this->info(($k + 1) . " / " . $count . " Updated");
                $updated->push($service);
            } else {
                $this->error(($k + 1) . " / " . $count . " Failed");
                $errors->push($service);
            }
        }
        $this->newLine();
        $this->info('Services updated done');
        $this->newLine();

        $this->info('Created: ' . $created->count() . ' / ' . $count);
        $this->info('Updated: ' . $updated->count() . ' / ' . $count);
        $this->error('Errors: ' . $errors->count() . ' / ' . $count);


        if (!empty($copies)) {
            $this->warn('Copies: ' . count($copies) . ' / ' . $count);

            $this->warn(json_encode($copies, JSON_UNESCAPED_UNICODE));
        }
        if (!empty($errors)) {
            foreach ($errors as $key => $error) {
                $errorsArray = [];
                $errorsArray[] = ($key + 1) . ": [";
                $errorsArray[] = "\tID: " . $error->service;
                $errorsArray[] = "\tName: " . $error->name;
                $errorsArray[] = "\tPrice: " . $error->rate;
                $errorsArray[] = "]";


                $errorsStr = implode("\n", $errorsArray);
                $this->warn($errorsStr);
            }
        }
    }
}
