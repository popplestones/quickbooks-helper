<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;

class QbCustomerSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:customer:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync customers with Quickbooks';

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
