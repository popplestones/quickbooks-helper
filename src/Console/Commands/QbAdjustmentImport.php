<?php

namespace Popplestones\Quickbooks\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbAdjustmentImport extends Command
{
    use SyncsWithQuickbooks;

    protected $signature = 'qb:adjustment:import';

    protected $description = 'Import adjustments into quickbooks';

    public $modelName;
    public $adjustmentLineModel;
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.adjustment.model');
        $this->adjustmentLineModel = config('quickbooks.adjustmentLine.model');
        $this->mapping = config('quickbooks.adjustment.attributeMap');
        $this->lineMapping = config('quickbooks.adjustmentLine.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
    }

    public function handle()
    {
        $this->setup();
        if (!$this->checkConnection()) return 1;

        $this->newLine();
        $this->components->info("Importing adjustments to {$this->modelName}");

        $this->components->task("Importing adjustments to {$this->modelName}", function() {
            $this->importModels(
                modelName: $this->modelName,
                tableName: 'CreditMemo',
                callback: function ($row) {
                    $customer = Customer::where(config('quickbooks.customer.attributeMap.qb_customer_id'),
                        $row->CustomerRef)->first();

                    if (!$customer) {
                        $this->warn("Skipping adjustment, customer #{$row->CustomerRef} doesn't exist, try importing customer with qb:customer:import");
                        return;
                    }

                    $adjustment = app($this->modelName)::firstOrNew([$this->mapping['qb_adjustment_id'] => $row->Id]);

                    if ($adjustment->exists) {
                        $adjustment->timestamps = false;
                    }

                    $adjustment->fill($this->setDataMapping($row, $this->mapping, $customer));
                    $adjustment->save();

                    $adjustment->{config('quickbooks.adjustment.lineRelationship')}()->delete();

                    collect($row->Line)->each(function ($line) use ($adjustment) {
                        if ($line->DetailType === 'SalesItemLineDetail') {
                            $product = $this->getProduct($line->SalesItemLineDetail?->ItemRef);
                            if (!$product) {
                                $this->warn("Skipping invoice line, Item #{$line->SalesItemLineDetail->ItemRef} doesn't exist, try importing items with qb:item:import");
                                return;
                            }
                        }
                        $this->adjustmentLineModel::create($this->setLineMapping($line, $this->lineMapping,
                            $adjustment));

                    });
                    $adjustment->update([$this->mapping['synced_at'] => now()]);
                },
                activeFilter: false
            );
            return 0;
        });
    }

    private function getProduct($qb_id)
    {
        $model = config('quickbooks.item.model');
        $map = config('quickbooks.item.attributeMap');
        return $model::where($map['qb_id'], $qb_id)->first();
    }

    private function setLineMapping($line, $mapping, $adjustment)
    {
        return [
            $mapping['invoice_ref'] => $adjustment->id,
            $mapping['amount'] => $line->Amount,
            $mapping['detail_type'] => $line->DetailType,
            $mapping['description'] => $line->Description,
            $mapping['line_num'] => $line->LineNum,
            $mapping['item_ref'] => $this->getProduct($line->SalesItemLineDetail?->ItemRef)?->getKey(),
            $mapping['qty'] => $line->SalesItemLineDetail?->Qty,
            $mapping['unit_price'] => $line->SalesItemLineDetail?->UnitPrice
        ];
    }
    private function setDataMapping($row, $mapping, $customer)
    {
        return [
            $mapping['transaction_date'] => $row->TxnDate,
            $mapping['currency_ref'] => $row->CurrencyRef,
            $mapping['exchange_rate'] => $row->ExchangeRate,
            $mapping['bill_email'] => $row->BillEmail?->Address,
            $mapping['ship_date'] => $row->ShipDate,
            $mapping['tracking_num'] => $row->TrackingNum,
            $mapping['due_date'] => $row->DueDate,
            $mapping['private_note'] => $row->PrivateNote,
            $mapping['customer_memo'] => $row->CustomerMemo,
            $mapping['ship_method'] => $row->ShipMethodRef,
            $mapping['apply_tax_after_discount'] => $row->ApplyTaxAfterDiscount,
            $mapping['total_amount'] => $row->TotalAmt,
            $mapping['balance'] => $row->Balance,
            $mapping['customer_ref'] => $customer->id,
            $mapping['doc_number'] => $row->DocNumber,
            $mapping['transaction_type'] => 'adjustment',
            $mapping['line1'] => $row->BillAddr?->Line1,
            $mapping['line2'] => $row->BillAddr?->Line2,
            $mapping['line3'] => $row->BillAddr?->Line3,
            $mapping['line4'] => $row->BillAddr?->Line4,
            $mapping['line5'] => $row->BillAddr?->Line5,
            $mapping['city'] => $row->BillAddr?->City,
            $mapping['country'] => $row->BillAddr?->Country,
            $mapping['state'] => $row->BillAddr?->CountrySubDivisionCode,
            $mapping['postal_code'] => $row->BillAddr?->PostalCode,
            $mapping['postal_code_suffix'] => $row->BillAddr?->PostalCodeSuffix,
            $mapping['country_code'] => $row->BillAddr?->CountryCode,
            $mapping['synced_at'] => now()
        ];
    }
}
