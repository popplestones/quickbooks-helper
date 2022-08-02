<?php

namespace Popplestones\Quickbooks\Console\Commands;

use App\Models\Account;
use App\Models\Customer;
use App\Models\PaymentMethod;
use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbPaymentImport extends Command
{
    use SyncsWithQuickbooks;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:payment:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import payments from Quickbooks';

    public $modelName;
    public $paymentLineModel;
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.payment.model');
        $this->paymentLineModel = config('quickbooks.paymentLine.model');
        $this->mapping = config('quickbooks.payment.attributeMap');
        $this->lineMapping = config('quickbooks.paymentLine.attributeMap');
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
        if (!$this->checkConnection()) {
            return 1;
        }

        $this->newLine();
        $this->components->info("Importing payments to {$this->modelName}");

        $this->components->task("Importing payments to {$this->modelName}", function () {
            $this->importModels(
                modelName: $this->modelName,
                tableName: 'Payment',
                callback: function ($row) {
                    $account = Account::where(config('quickbooks.account.attributeMap.qb_account_id'),
                        $row->DepositToAccountRef)->first();
                    $customer = Customer::where(config('quickbooks.customer.attributeMap.qb_customer_id'),
                        $row->CustomerRef)->first();
                    $paymentMethod = PaymentMethod::where(config('quickbooks.paymentMethod.attributeMap.qb_payment_method_id'),
                        $row->PaymentMethodRef)->first();

                    if (!$customer) {
                        $this->warn("Skipping payment, customer #{$row->CustomerRef} doesn't exist, try importing customer with qb:customer:import");
                        return true;
                    }

                    $payment = app($this->modelName)::firstOrNew([$this->mapping['qb_payment_id'] => $row->Id]);

                    if ($payment->exists) {
                        $payment->timestamps = false;
                    }

                    $payment->fill($this->setDataMapping($row, $this->mapping, $account, $customer));
                    $payment->save();

                    $payment->{config('quickbooks.payment.lineRelationship')}()->delete();

                    if (!is_array($row->Line) && $row->Line !== null) {
                        $this->createPaymentLine($payment, $row->Line);
                        return true;
                    }

                    collect($row->Line)->each(fn($line) => $this->createPaymentLine($payment, $line));

                    $payment->update([$this->mapping['synced_at'] => now()]);
                    return 0;
                },
                activeFilter: false
            );
            return 0;
        });

        return 0;
    }

    private function createPaymentLine($payment, $line)
    {
        $invoice = null;

        if ($line->LinkedTxn->TxnType === 'Invoice') {
            $invoice = $this->getInvoice($line->LinkedTxn->TxnId);
            if (!$invoice) {
                $this->warn("Skipping payment line, Invoice #{$line->LinkedTxn->TxnId} doesn't exist, try importing items with qb:invoice:import");
                return;
            }
        }
        $this->paymentLineModel::create($this->setLineMapping($line, $this->lineMapping, $payment));

        if ($invoice)
            $invoice->update([config('quickbooks.invoice.attributeMap.synced_at') => now()]);
    }

    private function setDataMapping($row, $mapping, $account, $customer)
    {
        return [
            $mapping['transaction_date'] => $row->TxnDate,
            $mapping['currency_ref']     => $row->CurrencyRef,
            $mapping['exchange_rate']    => $row->ExchangeRate,
            $mapping['total_amount']     => $row->TotalAmt,
            $mapping['customer_ref']     => $customer->id,
            $mapping['deposit_account']  => $account?->id,
            $mapping['payment_method']   => $row->PaymentMethodRef,
            $mapping['private_note']     => $row->PrivateNote,
            $mapping['payment_ref']      => $row->PaymentRefNum,
            'type'                       => 'payment',
            'synced_at'                  => now()
        ];
    }

    private function setLineMapping($row, $mapping, $payment)
    {
        return [
            $mapping['amount']      => $row->Amount,
            $mapping['invoice_ref'] => $this->getInvoice($row->LinkedTxn->TxnId)?->getKey(),
            $mapping['payment_ref'] => $payment->getKey()
        ];
    }

    private function getInvoice($qb_invoice_id)
    {
        $model = config('quickbooks.invoice.model');
        $map = config('quickbooks.invoice.attributeMap');

        return $model::where($map['qb_invoice_id'], $qb_invoice_id)->first();
    }
}
