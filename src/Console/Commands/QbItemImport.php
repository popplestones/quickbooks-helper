<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbItemImport extends Command
{
    use SyncsWithQuickbooks;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:item:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import items from Quickbooks';

    public $modelName;
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.item.model');
        $this->mapping = config('quickbooks.item.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setup();
        if (!$this->checkConnection()) return 1;

        $this->importModels(
            modelName: $this->modelName,
            mapping: $this->mapping,
            idField: 'qb_id',
            tableName: 'Item',
            callback: fn($row) =>
                app($this->modelName)::updateOrCreate([$this->mapping['qb_id'] => $row->Id], $this->setDataMapping($row, $this->mapping))
            );

        return 0;
    }

    public function setDataMapping($row, $mapping)
    {
        return [
            $mapping['name'] => $row->Name,
            $mapping['description'] => $row->Description,
            $mapping['active'] => $row->Active === 'true',
            $mapping['sub_item'] => $row->SubItem  === 'true',
            $mapping['parent_ref'] => $row->ParentRef,
            $mapping['level'] => $row->Level,
            $mapping['fully_qualified_name'] => $row->FullyQualifiedName,
            $mapping['taxable'] => $row->Taxable === 'true',
            $mapping['sales_tax_included'] => $row->SalesTaxIncluded === 'true',
            $mapping['unit_price'] => $row->UnitPrice,
            $mapping['type'] => $row->Type,
            $mapping['income_account_ref'] => $row->IncomeAccountRef,
            $mapping['purchase_tax_included'] => $row->PurchaseTaxIncluded === 'true',
            $mapping['purchase_cost'] => $row->PurchaseCost,
            $mapping['expense_account_ref'] => $row->ExpenseAccountRef,
            $mapping['track_qty_on_hand'] => $row->TrackQtyOnHand === 'true',
            $mapping['qty_on_hand'] => $row->QtyOnHand,
            $mapping['sales_tax_code_ref'] => $row->SalesTaxCodeRef,
            $mapping['purchase_tax_code_ref'] => $row->PurchaseTaxCodeRef,
        ];
    }
}
