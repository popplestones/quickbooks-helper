<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Facades\CallbackManager;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Payment;

class QbInvoiceSync extends Command
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
        $this->mapping   = config('quickbooks.invoice.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
        $this->callbacks = CallbackManager::getCallbacks('invoices');
        $this->max_failed = self::MAX_FAILED;
        $this->objMethod = '';
        $this->apiMethod = '';
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:invoice:sync {--id=*} {--limit=20} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync invoices with quickbooks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setup();
        if (!$this->checkConnection()) return 1;

        $this->newLine();
        $this->components->info("Syncing invoices to quickbooks.");

        $query = ($this->callbacks->query)();
        $this->applyIdOption($query);
        $query->limit($this->option('limit'));

        $invoices = $query->get();

        if ($invoices->count() === 0)
            $this->info('No invoices to sync');

        $this->output->progressStart($invoices->count());

        $invoices->each(function ($invoice) {
            try {
                $invoice_params[] = $this->prepareData($invoice);
                $this->objMethod = 'create';
                $this->apiMethod = 'Add';

                $error = $this->getExistingRecord('Invoice', 'qb_invoice_id', $invoice, $invoice_params);
                if ($error) return true;

                $this->newLine();
                $this->info("Invoice Params used to create local QbInvoice Object:");
                $this->info(json_encode($invoice_params));

                $qbInvoice = Invoice::{$this->objMethod}(...$invoice_params);

                $this->info("Local QbInvoice object:");
                $this->info(json_encode($qbInvoice));


                $this->info("Calling {$this->apiMethod}");
                $result = $this->qb_helper->dsCall($this->apiMethod, $qbInvoice);

                if ($result) {
                    $invoice->{$this->mapping['qb_invoice_id']} = $result->Id;
                }
                else {
                    $this->warn('Adding invoice failed!');
                    $error = $this->qb_helper->dataService->getLastError();
                    $this->warn($error->getResponseBody());
                }

                $this->info("Updating synced_at");
                $invoice->timestamps = false;
                $invoice->synced_at = now();

                $invoice->save();
            } catch (\Exception $e) {
                $this->info("Some exception occurred");
                $this->syncFailed($e, $invoice, 'invoice');
                return false;
            }
            $this->output->progressAdvance();
        });
        $this->output->progressFinish();

        return 0;
    }

    private function prepareLineData($invoice)
    {
        return $invoice->invoiceLines->map(fn($line) => (object)[
            'Description' => $line->description,
            'DetailType' => $line->detail_type,
            'SalesItemLineDetail' => [
                'ItemRef' => (object)[
                    'name' => $line->item->name,
                    'value' => $line->item->qb_item_id],
                'Qty' => $line->qty,
            ],
            'Amount' => $line->amount,
            'LineNum' => $line->line_num,
            'UnitPrice' => $line->unit_price
        ])->toArray();
    }
    private function prepareData($invoice)
    {
        return array_filter([
            'TxnDate' => data_get($invoice, $this->mapping['transaction_date']),
            'TotalAmt' => data_get($invoice, $this->mapping['total_amount']),
            'Line' => $this->prepareLineData($invoice),
            'DueDate' => data_get($invoice, $this->mapping['due_date']),
            'CustomerMemo' => [
                'value' => data_get($invoice, $this->mapping['customer_memo']),
            ],
            'Balance' => data_get($invoice, $this->mapping['balance']),
            'CustomerRef' => [
                'name' => $invoice->customer->company_name,
                'value' => $invoice->customer->qb_customer_id
            ],
            'BillEmail' => [
                'Address' => $invoice->bill_email
            ],
            'BillAddr' => [
                'Line4' => data_get($invoice, $this->mapping['line4']),
                'Line3' => data_get($invoice, $this->mapping['line3']),
                'Line2' => data_get($invoice, $this->mapping['line2']),
                'Line1' => data_get($invoice, $this->mapping['line1'])
            ],
        ], function ($val) {
            return ! is_null($val);
        });
    }
}
