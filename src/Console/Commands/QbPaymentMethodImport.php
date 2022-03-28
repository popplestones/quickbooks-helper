<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;

class QbPaymentMethodImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:payment-method:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import payment methods from Quickbooks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
