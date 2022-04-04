<?php

namespace Popplestones\Quickbooks\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Popplestones\Quickbooks\Console\Commands\QbAccountImport;
use Popplestones\Quickbooks\Console\Commands\QbAccountSync;
use Popplestones\Quickbooks\Console\Commands\QbCustomerImport;
use Popplestones\Quickbooks\Console\Commands\QbCustomerSync;
use Popplestones\Quickbooks\Console\Commands\QbInvoiceImport;
use Popplestones\Quickbooks\Console\Commands\QbInvoiceSync;
use Popplestones\Quickbooks\Console\Commands\QbInvoiceVoid;
use Popplestones\Quickbooks\Console\Commands\QbItemImport;
use Popplestones\Quickbooks\Console\Commands\QbItemSync;
use Popplestones\Quickbooks\Console\Commands\QbPaymentImport;
use Popplestones\Quickbooks\Console\Commands\QbPaymentMethodImport;
use Popplestones\Quickbooks\Console\Commands\QbPaymentSync;
use Popplestones\Quickbooks\Console\Commands\QbTaxCodeImport;
use Popplestones\Quickbooks\Facades\CallbackManager;
use Popplestones\Quickbooks\Services\QuickbooksClient;

class QuickbooksHelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this
            ->registerFacades()
            ->registerViews()
            ->registerBladeDirectives()
            ->registerRoutes()
            ->registerMigrations()
            ->registerCommands()
            ->registerPublishes();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/quickbooks.php', 'quickbooks'
        );
    }

    private function registerFacades(): self
    {
        $this->app->bind(CallbackManager::class, CallbackManager::class);

         return $this;
    }


    private function registerCommands(): self
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                QbAccountImport::class,
                QbAccountSync::class,
                QbCustomerImport::class,
                QbCustomerSync::class,
                QbInvoiceImport::class,
                QbInvoiceSync::class,
                QbInvoiceVoid::class,
                QbItemImport::class,
                QbItemSync::class,
                QbPaymentMethodImport::class,
                QbPaymentImport::class,
                QbPaymentSync::class,
                QbTaxCodeImport::class
            ]);
        }
        return $this;
    }

    private function registerViews(): self
    {
        $this->loadViewsFrom(__DIR__.'/../views', 'quickbooks');

        return $this;
    }

    public function registerBladeDirectives(): self
    {

        Blade::if('Connected', fn() => app('Quickbooks')->hasValidRefreshToken());


        return $this;
    }

    private function registerRoutes(): self
    {
        $config = $this->app->config->get('quickbooks.route');

        $this->app->router
        ->prefix($config['prefix'])
        ->as('quickbooks.')
        ->middleware($config['middleware']['default'])
        ->namespace('Popplestones\Quickbooks\Http\Controllers')
        ->group(function (Router $router) use ($config) {
            $router
                ->get($config['paths']['connect'], 'QuickbooksController@connect')
                ->middleware($config['middleware']['authenticated'])
                ->name('connect');

            $router
                ->delete($config['paths']['disconnect'], 'QuickbooksController@disconnect')
                ->middleware($config['middleware']['authenticated'])
                ->name('disconnect');

            $router
                ->get($config['paths']['token'], 'QuickbooksController@token')
                ->name('token');

        });

        return $this;
    }

    private function registerMigrations(): self
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }

    private function registerPublishes(): self
    {
        $this->publishes([
            __DIR__.'/../config/quickbooks.php' => config_path('quickbooks.php')
        ]);

        return $this;
    }
}
