<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use Illuminate\Contracts\Container\BindingResolutionException;

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
        $startPosition = 1;
        $maxResults = 100;
        $noOfRows = 0;

        $qb_helper = new QuickbooksHelper();
        $modelName =config('quickbooks.customer.model');
        $customerMapping = config('quickbooks.customer.attributeMap');

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
            $rows = collect($qb_helper->dsCall('Query', "SELECT * FROM Customer WHERE Active=true STARTPOSITION {$startPosition} MAXRESULTS {$maxResults}"));
            $this->info(json_encode($rows));
            return 1;
            $rows->each(function($row) use ($model, $customerMapping) {
                $model::updateOrCreate([$customerMapping['qb_customer_id'] => $row->Id], [
                    $customerMapping['id'] => $row->Id,
                    $customerMapping['name'] => $row->Name,
                    $customerMapping['description'] => $row->Description,
                    $customerMapping['sub_customer'] => $row->Subcustomer === 'true',
                    $customerMapping['fully_qualified_name'] => $row->FullyQualifiedName,
                    $customerMapping['active'] => $row->Active === 'true',
                    $customerMapping['classification'] => $row->Classification,
                    $customerMapping['customer_type'] => $row->customerType,
                    $customerMapping['customer_sub_type'] => $row->customerSubType,
                    $customerMapping['currency_ref'] => $row->CurrencyRef
                ]);
            });

            $noOfRows = $rows->count();

            $this->info("Query from {$startPosition} & max {$maxResults}. No of rows: {$noOfRows}");
            $startPosition += $maxResults;
        } while (!is_null($rows) && is_array($rows) && $noOfRows > $maxResults);

        return 0;
    }
}
