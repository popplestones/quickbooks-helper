<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Facades\CallbackManager;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use QuickBooksOnline\API\Facades\Item;

class QbItemSync extends Command
{
    use SyncsWithQuickbooks;

    const MAX_FAILED = 3;

    public $model;
    public $mapping;
    public $callbacks;
    public $qb_helper;
    public $objMethod;
    public $apiMethod;
    public $max_failed;

    private function setup()
    {
        $this->mapping   = config('quickbooks.item.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
        $this->callbacks = CallbackManager::getCallbacks('items');
        $this->max_failed = self::MAX_FAILED;
        $this->objMethod = '';
        $this->apiMethod = '';
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:item:sync {--id=*} {--limit=20} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync items with Quickbooks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setup();
        if (!$this->checkConnection()) return 1;

        $query = ($this->callbacks->query)();
        $this->applyIdOption($query);
        $query->limit($this->option('limit'));

        $items = $query->get();

        if ($items->count() === 0)
            $this->info('No items to sync');

        $items->each(function ($item) {
            try {
                $this->info("Processing Item #{$item->{$this->mapping['id']}}...");
                $item_params[] = $this->prepareData($item);
                $this->objMethod = 'create';
                $this->apiMethod = 'Add';

                $error = $this->getExistingRecord('Item', 'qb_id', $item, $item_params);
                if ($error) return true;

                $qbItem = Item::{$this->objMethod}(...$item_params);
                
                $result = $this->qb_helper->dsCall($this->apiMethod, $qbItem);                

                if ($result) {
                    $this->info("Success! Quickbooks Item ID #{$result->Id}");
                    $item->{$this->mapping['qb_id']} = $result->Id;
                }
                else {
                    $this->warn('Adding item failed!');
                    $error = $this->qb_helper->dataService->getLastError();
                    $this->warn($error->getResponseBody());                    
                }
                $item->save();
            } catch (\Exception $e) {
                $this->syncFailed($e, $item, 'item');
                return false;
            }
        });

        return 0;
    }

    private function prepareData($item)
    {
        return array_filter([
            'Name' => data_get($item, $this->mapping['name']),
            'Description' => data_get($item, $this->mapping['description']),
            'Active' => data_get($item, $this->mapping['active'])? 'true':'false',
            'SubItem' => data_get($item, $this->mapping['sub_item'])? 'true':'false',
            'ParentRef' => data_get($item, $this->mapping['parent_ref']),
            'Level' => data_get($item, $this->mapping['level']),
            'FullyQualifiedName' => data_get($item, $this->mapping['fully_qualified_name']),
            'Taxable' => data_get($item, $this->mapping['taxable'])? 'true':'false',
            'SalesTaxIncluded' => data_get($item, $this->mapping['sales_tax_included'])? 'true':'false',
            'UnitPrice' => data_get($item, $this->mapping['unit_price']),
            'Type' => data_get($item, $this->mapping['type']),
            'IncomeAccountRef' => data_get($item, $this->mapping['income_account_ref']),
            'PurchaseTaxIncluded' => data_get($item, $this->mapping['purchase_tax_included'])? 'true':'false',
            'PurchaseCost' => data_get($item, $this->mapping['purchase_cost']),
            'ExpenseAccountRef' => data_get($item, $this->mapping['expense_account_ref']),
            'TrackQtyOnHand' => data_get($item, $this->mapping['track_qty_on_hand'])? 'true':'false',
            'QtyOnHand' => data_get($item, $this->mapping['qty_on_hand']),
            'SalesTaxCodeRef' => data_get($item, $this->mapping['sales_tax_code_ref']),
            'PurchaseTaxCodeRef' => data_get($item, $this->mapping['purchase_tax_code_ref'])
        ], function ($val) {
            return ! is_null($val);
        });
    }
}
