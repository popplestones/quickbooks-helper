<?php

namespace Popplestones\Quickbooks\Providers;

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
use Popplestones\Quickbooks\Console\Commands\QbPaymentMethodImport;
use Popplestones\Quickbooks\Console\Commands\QbTaxCodeImport;

class QuickbooksHelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this
            ->registerViews()
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

    private function registerRoutes(): self
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

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
