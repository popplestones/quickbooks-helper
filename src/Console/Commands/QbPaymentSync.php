<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Facades\CallbackManager;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use QuickBooksOnline\API\Facades\Payment;

class QbPaymentSync extends Command
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
        $this->mapping   = config('quickbooks.payment.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
        $this->callbacks = CallbackManager::getCallbacks('payments');
        $this->max_failed = self::MAX_FAILED;
        $this->objMethod = '';
        $this->apiMethod = '';
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:payment:sync {--id=*} {--limit=20} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payments with quickbooks';

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

        $payments = $query->get();

        if ($payments->count() === 0)
            $this->info('No payments to sync');

        $payments->each(function ($payment) {
            try {
                $account_id = data_get($payment, $this->mapping['account_id']);
                if (!$account_id) {
                    $this->warn("Skipping payment #{$payment->id}. Account has not been synced.");
                    return true;
                }

                $account_type = data_get($payment, $this->mapping['account_type']);

                if (!in_array($account_type, ['Bank', 'Other Currrent Asset'])) {
                    $this->warn("Skipping payment #{$payment->id}. Account type must be either 'Bank' or 'Other Current Asset', '{$account_type}' is invalid.");
                    return true;
                }

                // Other current asset or bank
                $this->info("Processing Payment #{$payment->{$this->mapping['id']}}...");
                $payment_params[] = $this->prepareData($payment);
                $this->objMethod = 'create';
                $this->apiMethod = 'Add';

                $error = $this->getExistingRecord('Payment', 'qb_payment_id', $payment, $payment_params);
                if ($error) return true;

                $this->info("Creating local QBPayment object");
                $this->info(json_encode($payment_params));
                $qbPayment = Payment::{$this->objMethod}(...$payment_params);

                $this->info("Pushing local QBPayment to Quickbooks");
                $result = $this->qb_helper->dsCall($this->apiMethod, $qbPayment);

                if ($result) {
                    $this->info("Success! Quickbooks Payment ID #{$result->Id}");
                    $payment->{$this->mapping['qb_payment_id']} = $result->Id;
                }
                else {
                    $this->warn('Adding payment failed!');
                    $error = $this->qb_helper->dataService->getLastError();
                    $this->warn($error->getResponseBody());
                }
                $payment->save();
            } catch (\Exception $e) {
                $this->syncFailed($e, $payment, 'payment');
                return false;
            }
        });

        return 0;
    }


    private function prepareData($payment)
    {
        $this->info(json_encode($payment));
        $payment->payment_lines->each(fn($payment_line) => $this->info(json_encode($payment_line)));

        return array_filter([
            'TotalAmt' => data_get($payment, $this->mapping['total_amount']),
            'TxnDate' => data_get($payment, $this->mapping['transaction_date']),
            'PaymentMethodRef' => is_null(data_get($payment, $this->mapping['payment_method'])) ? null : [
                'value' => data_get($payment, $this->mapping['payment_method_id'])
            ],
            'CurrencyRef' => [
                'value' => data_get($payment, $this->mapping['currency_ref'])
            ],
            'ExchangeRate' => data_get($payment, $this->mapping['exchange_rate']),
            'CustomerRef' => [
                'value' => data_get($payment, $this->mapping['customer_id'])
            ],
            'DepositToAccountRef' => [
                'value' => data_get($payment, $this->mapping['account_id'])
            ],
        ], function ($val) {
            return ! is_null($val);
        });
    }
}
