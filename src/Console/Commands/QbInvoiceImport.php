<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;

class QbInvoiceImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:invoice:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import invoices from Quickbooks';

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
