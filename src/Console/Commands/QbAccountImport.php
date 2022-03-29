<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use Illuminate\Contracts\Container\BindingResolutionException;

class QbAccountImport extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:account:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import accounts from Quickbooks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $startPosition = 1;
        $maxResults = 100;
        $noOfRows = 0;

        $qb_helper = new QuickbooksHelper();
        $modelName =config('quickbooks.account.model');
        $accountMapping = config('quickbooks.account.attributeMap');

        $model = "";

        try
        {
            $model = app($modelName);

        } catch (BindingResolutionException $ex)
        {
            $this->error("Invalid model '{$modelName}'. Setup the model in the quickbooks.php config file.");
            return 1;
        }

        do
        {
            $rows = collect($qb_helper->dsCall('Query', "SELECT * FROM Account WHERE Active=true STARTPOSITION {$startPosition} MAXRESULTS {$maxResults}"));

            $rows->each(function($row) use ($model, $accountMapping) {
                $model::updateOrCreate([$accountMapping['qb_account_id'] => $row->Id], [
                    $accountMapping['id'] => $row->Id,
                    $accountMapping['name'] => $row->Name,
                    $accountMapping['description'] => $row->Description,
                    $accountMapping['sub_account'] => $row->SubAccount === 'true',
                    $accountMapping['fully_qualified_name'] => $row->FullyQualifiedName,
                    $accountMapping['active'] => $row->Active === 'true',
                    $accountMapping['classification'] => $row->Classification,
                    $accountMapping['account_type'] => $row->AccountType,
                    $accountMapping['account_sub_type'] => $row->AccountSubType,
                    $accountMapping['currency_ref'] => $row->CurrencyRef
                ]);
            });

            $noOfRows = $rows->count();

            $this->info("Query from {$startPosition} & max {$maxResults}. No of rows: {$noOfRows}");
            $startPosition += $maxResults;
        } while (!is_null($rows) && is_array($rows) && $noOfRows > $maxResults);

        return 0;
    }
}
