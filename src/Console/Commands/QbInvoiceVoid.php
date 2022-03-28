<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;

class QbInvoiceVoid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:invoice:void';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Void quickbooks invoices that have been deleted';

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
