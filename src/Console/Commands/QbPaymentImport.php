<?php

namespace Popplestones\Quickbooks\Console\Commands;

use App\Models\Account;
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
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.payment.model');
        $this->mapping = config('quickbooks.payment.attributeMap');
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

        $this->info("Import payments...");
        $this->info("ModelName: {$this->modelName}");
        // $this->importModels(
        //     modelName: $this->modelName,
        //     tableName: 'Payment',
        //     callback: fn($row) =>
        //         app($this->modelName)::updateOrCreate([$this->mapping['qb_payment_id'] => $row->Id], $this->setDataMapping($row, $this->mapping))
        //     );

        return 0;
    }
    public function setDataMapping($row, $mapping)
    {
        return [
            $mapping['transaction_date'] => $row->TxnDate,
            $mapping['currency_ref'] => $row->CurrencyRef,
            $mapping['exchange_rate'] => $row->ExchangeRate,
            $mapping['total_amount'] => $row->TotalAmt,
            $mapping['customer_ref'] => $row->CustomerRef,
            $mapping['deposit_account'] => Account::find(intval($row->DepositToAccountRef))?->$mapping['qb_account_id'],
            $mapping['payment_method'] => $row->PaymentMethodRef,
            $mapping['private_note'] => $row->PrivateNote
        ];
    }
}
