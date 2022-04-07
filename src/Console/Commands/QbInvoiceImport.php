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
        $this->invoiceLineMapping = config('quickbooks.invoiceLine.attributeMap');
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
                $this->info("Executing import callback.");
                $customer = Customer::where(config('quickbooks.customer.attributeMap.qb_customer_id'), $row->CustomerRef)->first();

                if (!$customer) {
                    $this->warn("Skipping invoice, customer #{$row->CustomerRef} doesn't exist, try importing customer with qb:customer:import");
                    return;
                }

                $this->info(json_encode($this->setDataMapping($row, $this->mapping, $customer)));

                $this->warn("Creating invoice:");
                $this->warn(json_encode($this->setDataMapping($row, $this->mapping)));
                $invoice = app($this->modelName)::updateOrCreate([$this->mapping['qb_invoice_id'] => $row->Id], $this->setDataMapping($row, $this->mapping));                $this->addressModel::updateOrCreate(['customer_id' => $customer->id, 'type' => 'billing'], $this->setAddressMapping($row, 'BillAddr', "billing_", $this->mapping));

                $this->warn("Clearing existing invoice lines");
                $invoice->{config('quickbooks.invoice.lineRelationship')}()->delete();

                collect($row->Line)->each(function($line) use ($invoice) {
                    $this->warn("Creating invoice line:");
                    $this->warn(json_encode($this->setLineMapping($line, $this->lineMapping)));
                    $this->invoiceLineModel::create($this->setLineMapping($line, $this->lineMapping, $invoice));
                });
            },
            activeFilter: false
        );

        return 0;
    }

    private function setLineMapping($line, $mapping, $invoice)
    {
        return [
            
        ];
    }
    private function setDataMapping($row, $mapping)
    {
        return [
            $mapping['transaction_date'] => $row->TxnDate,
            $mapping['currency_ref'] => $row->CurrencyRef,
            $mapping['exchange_rate'] => $row->ExchangeRate,
            $mapping['bill_email'] => $row->BillEmail,
            $mapping['ship_date'] => $row->ShipDate,
            $mapping['tracking_num'] => $row->TrackingNum,
            $mapping['due_date'] => $row->DueDate,
            $mapping['private_note'] => $row->PrivateNote,
            $mapping['customer_memo'] => $row->CustomerMemo,
            $mapping['ship_method'] => $row->ShipMethodRef,
            $mapping['apply_tax_after_discount'] => $row->ApplyTaxAfterDiscount,
            $mapping['total_amount'] => $row->TotalAmt
        ];
    }
}
