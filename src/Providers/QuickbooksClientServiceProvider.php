<?php

namespace Popplestones\Quickbooks\Providers;

use Illuminate\Support\ServiceProvider;

use Popplestones\Quickbooks\Services\QuickbooksClient;

/**
 * Class QuickbooksClientServiceProvider
 *
 * @package Popplestones\Quickbooks\Providers
*/
class QuickbooksClientServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function provides()
    {
        return [QuickbooksClient::class];
    }

    public function register()
    {

        $this->app->bind(QuickbooksClient::class, function ($app) {
            $token = ($app->auth->user()->quickbooksToken)
                ?: $app->auth->user()
                    ->quickbooksToken()
                    ->make();

            return new QuickbooksClient($app['config']['quickbooks'], $token);
        });

        $this->app->alias(QuickbooksClient::class, 'Quickbooks');
    }


}