<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\Seller\Services\ServicesSellerSuccessResponseDto;
use App\Dto\Service\ServiceCreateDto;
use App\Services\Integrations\PartnerSoc\PartnerSocService;
use App\Services\Integrations\SmmPanel\SmmPanelService;
use App\Services\Integrations\SmmPanelus\SmmPanelusService;
use App\Services\Integrations\SSmm\SSmmService;
use App\Services\Integrations\WebSmm\WebSmmService;
use App\Services\SellerService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DeleteDoobleServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:services-dooble:soc-prof';

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
        $webSmmSeller = new WebSmmService();
        $SSmmSeller = new SSmmService();
        $smmPanelSeller = new SmmPanelService();

        return [
            1 => new SellerService($socProfSeller),
            2 => new SellerService($smmPanelusSeller),
            3 => new SellerService($webSmmSeller),
            4 => new SellerService($SSmmSeller),
            5 => new SellerService($smmPanelSeller),
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
        foreach ($partners as $k => $seller) {
            $this->sync($seller, $k);
        }
    }

    public function sync(SellerService $seller, int $apiSellerID)
    {

        $services = $seller->getServices();

        $count = $services->count();

        $errors = new Collection();
        $copies = [];

        $this->info('Services delete start');
        $this->info('Count: ' . $count);
        $this->newLine();
        foreach ($services as $k => $service) {
            /**
             * @var ServicesSellerSuccessResponseDto $service
             */
            $this->comment(($k + 1) . " / " . $count . " Check..");
            $serviceDto = new ServiceCreateDto();

            $serviceDto->serviceTitle = $service->name;
            $serviceDto->maxAmount = $service->max;
            $serviceDto->minAmount = $service->min;
            $serviceDto->apiProviderPrice = $service->rate;
            $serviceDto->apiServiceId = $service->service;
            $serviceDto->apiProviderId = $apiSellerID;

            try {
                $serviceDto->sync($copies);
            }catch (\Throwable $e) {
                $this->error(($k + 1) . " / " . $count . " Failed");
                $errors->push($service);
            }
        }
        $this->newLine();
        $this->info('Services deleted done');
        $this->newLine();

        if (!empty($copies)) {
            $this->warn('Deleted: ' . count($copies) . ' / ' . $count);

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
