<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;

class QbAccountSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:account:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync accounts with Quickbooks';

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
