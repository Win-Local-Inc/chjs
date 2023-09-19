<?php

namespace App\Providers;

use App\Services\Chargify\Chargify;
use App\Services\Chargify\ChargifyConfig;
use App\Services\Chargify\ChargifyHttpClient;
use App\Services\Chargify\Factories\ChargifyServiceFactory;
use App\Services\Chargify\Factories\WebhookResolver;
use App\Services\Chargify\Interfaces\WebhookResolverInterface;
use Illuminate\Support\ServiceProvider;

class ChargifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(ChargifyConfig::class, function () {
            return new ChargifyConfig(
                hostname: rtrim(trim(config('chargify.hostname')), '/').'/',
                eventsHostname: rtrim(trim(config('chargify.eventsHostname')), '/').'/',
                subdomain: config('chargify.subdomain'),
                apiKey: config('chargify.apiKey'),
                publicKey: config('chargify.publicKey'),
                privateKey: config('chargify.privateKey'),
                sharedKey: config('chargify.sharedKey'),
                timeout: config('chargify.timeout'),
            );
        });

        $this->app->singleton(WebhookResolverInterface::class, function () {
            return new WebhookResolver();
        });

        $this->app->bind(Chargify::class, function () {
            $chargifyConfig = $this->app->make(ChargifyConfig::class);

            return new Chargify(
                $chargifyConfig,
                new ChargifyHttpClient($chargifyConfig),
                new ChargifyServiceFactory(),
            );
        });
    }

    public function register()
    {
    }
}
