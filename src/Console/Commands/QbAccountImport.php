<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbAccountImport extends Command
{
    use SyncsWithQuickbooks;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:account:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import accounts from Quickbooks';

    public $modelName;
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.account.model');
        $this->mapping = config('quickbooks.account.attributeMap');
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

        $this->newLine();

        $this->components->task("Importing accounts to {$this->modelName}", function() {
            $this->importModels(
                modelName: $this->modelName,
                tableName: 'Account',
                callback: fn($row) =>
                    app($this->modelName)::updateOrCreate(
                        [$this->mapping['qb_account_id'] => $row->Id],
                        $this->setDataMapping($row, $this->mapping))
                );
        });

        return 0;
    }

    protected function setDataMapping($row, $mapping)
    {
        return [
            $mapping['name'] => $row->Name,
            $mapping['description'] => $row->Description,
            $mapping['sub_account'] => $row->SubAccount === 'true',
            $mapping['fully_qualified_name'] => $row->FullyQualifiedName,
            $mapping['active'] => $row->Active === 'true',
            $mapping['classification'] => $row->Classification,
            $mapping['account_type'] => $row->AccountType,
            $mapping['account_sub_type'] => $row->AccountSubType,
            $mapping['currency_ref'] => $row->CurrencyRef
        ];
    }
}
