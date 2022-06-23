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

        $this->info("Importing adjustments to {$this->modelName}");

        $this->importModels(
            modelName: $this->modelName,
            tableName: 'CreditMemo',
            callback: function($row) {
                info("Importing adjustment:");
                info(json_encode($row));
                $customer = Customer::where(config('quickbooks.customer.attributeMap.qb_customer_id'), $row->CustomerRef)->first();

                if (!$customer) {
                    $this->warn("Skipping adjustment, customer #{$row->CustomerRef} doesn't exist, try importing customer with qb:customer:import");
                    return;
                }

                $adjustment = app($this->modelName)::updateOrCreate([$this->mapping['qb_adjustment_id'] => $row->Id], $this->setDataMapping($row, $this->mapping, $customer));

                $adjustment->{config('quickbooks.adjustment.lineRelationship')}()->delete();

                collect($row->Line)->each(function($line) use ($adjustment) {
                    if ($line->DetailType === 'SalesItemLineDetail') {
                        $product = $this->getProduct($line->SalesItemLineDetail?->ItemRef);
                        if (!$product) {
                            $this->warn("Skipping invoice line, Item #{$line->SalesItemLineDetail->ItemRef} doesn't exist, try importing items with qb:item:import");
                            return;
                        }
                    }
                    $this->adjustmentLineModel::create($this->setLineMapping($line, $this->lineMapping, $adjustment));
                });
            },
            activeFilter: false
        );
    }
}
