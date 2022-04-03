<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbTaxCodeImport extends Command
{
    use SyncsWithQuickbooks;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:tax-code:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import tax codes from Quickbooks';

    public $qb_helper;
    public $mapping;
    public $modelName;

    private function setup()
    {
        $this->modelName = config('quickbooks.taxCode.model');
        $this->mapping = config('quickbooks.taxCode.attributeMap');
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

        $this->importModels(
            modelName: $this->modelName,
            tableName: 'TaxCode',
            callback: fn($row) =>
                app($this->modelName)::updateOrCreate([$this->mapping['qb_tax_code_id'] => $row->Id], $this->setDataMapping($row, $this->mapping))
            );

        return 0;
    }

    public function setDataMapping($row, $mapping)
    {
        return [
            $mapping['name'] => $row->Name,
            $mapping['active'] => $row->Active === 'true',
            $mapping['description'] => $row->Description,
            $mapping['tax_group'] => $row->TaxGroup === 'true',
            $mapping['taxable'] => $row->Taxable === 'true',
            $mapping['hidden'] => $row->Hidden === 'true',
        ];
    }
}
