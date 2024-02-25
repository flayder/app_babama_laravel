<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiProvider;
use App\Models\ContentDetails;
use App\Models\Language;
use App\Models\Notice;
use App\Models\Template;
use App\Models\Ticket;
use Bot\Service\TelegramBot;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Register any application services. */
    public function register(): void
    {
        require_once app_path('/Helper/helpers.php');
    }

    /** Bootstrap any application services. */
    public function boot(): void
    {
        if(!request()->is('api/*')) {
            //URL::forceScheme('http');

            $data['basic'] = (object)config('basic');
            $data['theme'] = 'themes.minimal.';
            $data['themeTrue'] = 'themes.minimal.';
            View::share($data);

            try {
                DB::connection()->getPdo();

                view()->composer(['admin.pages.ticket.nav', 'dashboard'], function ($view): void {
                    $view->with('pending', Ticket::whereIn('status', [0, 2])->latest()->with('user')->limit(10)->with('lastReply')->get());
                });

                view()->composer('user.layouts.side-notify', function ($view): void {
                    $view->with('notices', Notice::where('status', 1)->get());
                });

                view()->composer($data['theme'] . 'partials.footer', function ($view): void {
                    $view->with('languages', Language::orderBy('name')->where('is_active', 1)->get());

                    $templateSection = ['contact-us'];
                    $view->with('templates', Template::templateMedia()->whereIn('section_name', $templateSection)->get()->groupBy('section_name'));

                    $contentSection = ['support', 'social'];
                    $view->with('contentDetails', ContentDetails::select('id', 'content_id', 'description')
                        ->whereHas('content', fn($query) => $query->whereIn('name', $contentSection))
                        ->with(['content:id,name',
                            'content.contentMedia' => function ($q): void {
                                $q->select(['content_id', 'description']);
                            },])
                        ->get()->groupBy('content.name'));
                });

                Paginator::useBootstrap();
            } catch (\Exception $e) {
            }
        }

        $this->clientInitialization();
        $this->bindInitialization();


    }

    public function clientInitialization()
    {
        $apiProviders = ApiProvider::all();
        try {
            if (empty($apiProviders)) return ;

            foreach ($apiProviders as $apiProvider) {
                Http::macro($apiProvider->api_name, function () use ($apiProvider) {
                    return Http::baseUrl($apiProvider->url)
                        ->timeout((int)config('services.sellers.params.timeout'))
                        ->withOptions([
                            'key' => $apiProvider->api_key
                        ])
                        ->acceptJson();
                });
            }
        } catch (\Throwable $e) {
            Log::error('AppServiceProviderError', [
                'error' => $e->getMessage(),
                'providers' => $apiProviders
            ]);
        }

    }

    private function bindInitialization()
    {
        $this->app->bind(TelegramBot::class, function ($app) {
            return new TelegramBot(config('app.telegram.bot.token'));
        });
    }
}
