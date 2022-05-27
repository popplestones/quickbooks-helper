<?php
namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

trait SyncsWithQuickbooks
{
    private function checkConnection()
    {
        if (!$this->qb_helper->dataService)
        {
            $this->error("Connect a user to quickbooks online first");
            return false;
        }
        return true;
    }
    private function syncFailed($e, $model, $modelName)
    {
        $this->info('Exception:');
        $this->error(json_encode($e));
        $this->info('Model:');
        $this->error(json_encode($model));
        $this->info('ModelName: ');
        $this->error(json_encode($modelName));

        $model->increment($this->mapping['sync_failed']);
        $message = "{$e->getFile()}@{$e->getLine()} ==> {$e->getMessage()} for {$modelName} #{$model->{$this->mapping['id']}}";
        $this->info("Error: {$message}");
        Log::channel('quickbooks')->error($message);
    }

    private function getExistingRecord($qbTable, $idField, &$model, &$params)
    {
        if ($model->{$this->mapping[$idField]})
        {
            $targetArray = $this->qb_helper->find($qbTable, $model->{$this->mapping[$idField]});

            if (!empty($targetArray) && sizeof($targetArray) === 1) {
                $theRecord = current($targetArray);
                $this->objMethod = 'update';
                $this->apiMethod = 'Update';
                array_unshift($params, $theRecord);
            } else {
                if (!$this->option('force')) {
                    $message = "{$qbTable} not exists #{$model->{$this->mapping['$idField']}} for {$qbTable} #{$model->{$this->mapping['id']}}";
                    $this->error("Error: {$message}");
                    Log::channel('quickbooks')->error($message);
                    return true;
                }
                $model->{$this->mapping[$idField]} = null;
            }
        }

        return false;
    }

    private function applyIdOption($query)
    {
        if ($ids = $this->option('id'))
            $query->whereIn($this->mapping['id'], $ids);
        else {
            ($this->callbacks->filter)($query);
            $this->applySyncFailedFilter($query);
        }
    }

    private function applySyncFailedFilter($query)
    {
        $query->where($this->mapping['sync_failed'], '<', $this->max_failed);
    }

    private function importModels($modelName, $tableName, $callback, $activeFilter = true)
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
            $additionalOption = $activeFilter ? "WHERE Active=true" : "";
            $rows = collect($qb_helper->dsCall('Query', "SELECT * FROM {$tableName} {$additionalOption} STARTPOSITION {$startPosition} MAXRESULTS {$maxResults}"));
            $rows->each($callback);
            $noOfRows = $rows->count();

            $this->info("Imported {$noOfRows} records.");
            $startPosition += $maxResults;
        } while ($rows->isNotEmpty() && $noOfRows >= $maxResults);
    }
}
