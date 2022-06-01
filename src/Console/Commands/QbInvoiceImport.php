<?php

namespace Popplestones\Quickbooks\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbInvoiceImport extends Command
{
    use SyncsWithQuickbooks;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:invoice:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import invoices from Quickbooks';

    public $modelName;
    public $invoiceLineModel;
    public $lineMapping;
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.invoice.model');
        $this->invoiceLineModel = config('quickbooks.invoiceLine.model');
        $this->lineMapping = config('quickbooks.invoiceLine.attributeMap');
        $this->mapping = config('quickbooks.invoice.attributeMap');
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

        $this->info("Importing invoices to {$this->modelName}");
        $this->importModels(
            modelName:$this->modelName,
            tableName: 'Invoice',
            callback: function($row) {
                $customer = Customer::where(config('quickbooks.customer.attributeMap.qb_customer_id'), $row->CustomerRef)->first();

                if (!$customer) {
                    $this->warn("Skipping invoice, customer #{$row->CustomerRef} doesn't exist, try importing customer with qb:customer:import");
                    return;
                }

                $invoice = app($this->modelName)::updateOrCreate([$this->mapping['qb_invoice_id'] => $row->Id], $this->setDataMapping($row, $this->mapping, $customer));

                $invoice->{config('quickbooks.invoice.lineRelationship')}()->delete();

                collect($row->Line)->each(function($line) use ($invoice) {
                    if ($line->DetailType === 'SalesItemLineDetail') {
                        $product = $this->getProduct($line->SalesItemLineDetail?->ItemRef);
                        if (!$product) {
                            $this->warn("Skipping invoice line, Item #{$line->SalesItemLineDetail->ItemRef} doesn't exist, try importing items with qb:item:import");
                            return;
                        }
                    }
                    $this->invoiceLineModel::create($this->setLineMapping($line, $this->lineMapping, $invoice));
                });
            },
            activeFilter: false
        );

        return 0;
    }

    private function getProduct($qb_id)
    {
        $model = config('quickbooks.item.model');
        $map = config('quickbooks.item.attributeMap');
        return $model::where($map['qb_id'], $qb_id)->first();
    }

    private function setLineMapping($line, $mapping, $invoice)
    {
        return [
            $mapping['invoice_ref'] => $invoice->id,
            $mapping['amount'] => $line->Amount,
            $mapping['detail_type'] => $line->DetailType,
            $mapping['description'] => $line->Description,
            $mapping['line_num'] => $line->LineNum,
            $mapping['item_ref'] => $this->getProduct($line->SalesItemLineDetail?->ItemRef)?->getKey(),
            $mapping['qty'] => $line->SalesItemLineDetail?->Qty,
            $mapping['unit_price'] => $line->SalesItemLineDetail?->UnitPrice,
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
            $mapping['customer_ref'] => $customer->id,
            $mapping['doc_number'] => $row->DocNumber,
            $mapping['transaction_type'] => 'invoice',
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
        ];
    }
}
