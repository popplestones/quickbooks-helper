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
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.invoice.model');
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

        $this->info("Import invoices...");
        $this->importModels(
            modelName:$this->modelName,
            tableName: 'Invoice',
            callback: function($row) {
                $customer = Customer::where(config('quickbooks.customer.attributeMap.qb_customer_id'), $row->CustomerRef)->first();

                if (!$customer) {
                    $this->warn("Skipping invoice, customer #{$row->CustomerRef} doesn't exist, try importing customer with qb:customer:import");
                    return;
                }
                app($this->modelName)::updateOrCreate([$this->mapping['qb_payment_id'] => $row->Id], $this->setDataMapping($row, $this->mapping, $customer));
        });

        return 0;
    }

    private function setDataMapping($row, $mapping, $customer)
    {
        $lines = [];

        return [
            'Line' => $lines,
            'BillAddr' => [
                'Line1' => '',
                'Line2' => '',
                'Line3' => '',
                'Line4' => '',
                'Line5' => '',
                'City' => '',
                'CountrySubDivisionCode' => '',
                'PostalCode' => '',
                'Lat' => '',
                'Long' => '',
            ],
            'ShipAddr' => [
                'Line1' => '',
                'Line2' => '',
                'Line3' => '',
                'Line4' => '',
                'Line5' => '',
                'City' => '',
                'CountrySubDivisionCode' => '',
                'PostalCode' => '',
                'Lat' => '',
                'Long' => '',
            ],
            'TxnDate' => '',
            'TotalAmt' => '',
            'CustomerRef' => [
                'value' => ''
            ],
            'DueDate' => '',
            'TxnTaxDetail' => [
                'TotalTax' => ''
            ],
            'ShipDate' => '',
            'ShipFromAddr' => [
                'Line1' => '',
                'Line2' => '',
                'Line3' => '',
                'Line4' => '',
                'Line5' => '',
                'City' => '',
                'CountrySubDivisionCode' => '',
                'PostalCode' => '',
                'Lat' => '',
                'Long' => '',
            ],
            'TrackingNum' => '',
            'GlobalTaxCalculation' => '',
            'PrivateNote' => '',
            'CustomerMemo' => [
                'value' => ''
            ]
        ];
    }
}
