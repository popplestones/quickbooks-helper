<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use Illuminate\Contracts\Container\BindingResolutionException;

class QbAccountImport extends Command
{
    use ImportsFromQuickbooks;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modelName = config('quickbooks.account.model');
        $mapping = config('quickbooks.account.attributeMap');

        $this->importModels(
            modelName: $modelName,
            mapping: $mapping,
            idField: 'qb_account_id',
            tableName: 'Account',
            callback: fn($row) =>
                app($modelName)::updateOrCreate([$mapping['qb_account_id'] => $row->Id], $this->setDataMapping($row, $mapping))
            );

            return 0;
    }

    protected function setDataMapping($row, $mapping)
    {
        return [
            $mapping['id'] => $row->Id,
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
