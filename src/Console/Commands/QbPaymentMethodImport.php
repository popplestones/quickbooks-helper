<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbPaymentMethodImport extends Command
{
    use SyncsWithQuickbooks;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:payment-method:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import payment methods from Quickbooks';

    public $qb_helper;
    public $mapping;
    public $modelName;

    private function setup()
    {
        $this->modelName = config('quickbooks.paymentMethod.model');
        $this->mapping = config('quickbooks.paymentMethod.attributeMap');
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

        $this->info("Import payment methods...");
        $this->importModels(
            modelName: $this->modelName,
            tableName: 'PaymentMethod',
            callback: fn($row) =>
                app($this->modelName)::updateOrCreate([$this->mapping['qb_payment_method_id'] => $row->Id], $this->setDataMapping($row, $this->mapping))
            );

        return 0;
    }

    public function setDataMapping($row, $mapping)
    {
        return [
            $mapping['name'] => $row->Name,
            $mapping['active'] => $row->Active === 'true',
            $mapping['type'] => $row->Type,
        ];
    }
}
