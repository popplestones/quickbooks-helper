<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;

class QbItemImport extends Command
{
    use ImportsFromQuickbooks;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modelName = config('quickbooks.item.model');
        $mapping = config('quickbooks.item.attributeMap');

        $this->importModels(
            modelName: $modelName,
            mapping: $mapping,
            idField: 'qb_id',
            tableName: 'Item',
            callback: fn($row) =>
                app($modelName)::updateOrCreate([$mapping['qb_id'] => $row->Id], $this->setDataMapping($row, $mapping))
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
