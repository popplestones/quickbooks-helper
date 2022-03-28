<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;

class QbCustomerImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:customer:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import customers from Quickbooks';

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
