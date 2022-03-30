<?php
namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Support\Facades\Log;

trait SyncsWithQuickbooks
{
    private function syncFailed($e, $model, $modelName)
    {
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
}