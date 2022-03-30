<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Contracts\Container\BindingResolutionException;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

trait ImportsFromQuickbooks
{
    protected function importModels($modelName, $mapping, $idField, $tableName, $callback)
    {
        $startPosition = 1;
        $maxResults = 100;
        $noOfRows = 0;

        $qb_helper = new QuickbooksHelper();

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
            $rows = collect($qb_helper->dsCall('Query', "SELECT * FROM {$tableName} WHERE Active=true STARTPOSITION {$startPosition} MAXRESULTS {$maxResults}"));
            $this->info(json_encode($rows));
            $rows->each($callback);
            $noOfRows = $rows->count();

            $this->info("Query from {$startPosition} & max {$maxResults}. No of rows: {$noOfRows}");
            $startPosition += $maxResults;
        } while (!is_null($rows) && is_array($rows) && $noOfRows > $maxResults);
    }
}