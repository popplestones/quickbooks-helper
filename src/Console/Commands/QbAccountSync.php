<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Popplestones\Quickbooks\Facades\CallbackManager;
use QuickBooksOnline\API\Facades\Account;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbAccountSync extends Command
{
    use SyncsWithQuickbooks;

    const MAX_FAILED = 3;

    public $callbacks;
    public $mapping;
    public $qb_helper;
    public $max_failed;
    public $objMethod;
    public $apiMethod;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:account:sync {--id=*} {--limit=20} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync accounts with Quickbooks';

    private function setup()
    {
        $this->mapping = config('quickbooks.account.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
        $this->callbacks = CallbackManager::getCallbacks('accounts');
        $this->max_failed = self::MAX_FAILED;
        $this->objMethod = '';
        $this->apiMethod = '';
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setup();

        $query = ($this->callbacks->query)();

        $this->applyIdOption($query);

        $query->limit($this->option('limit'));

        $accounts = $query->get();

        if ($accounts->count() === 0)
            $this->info('No accounts to sync');


        $accounts->each(function($account) {
            try
            {
                $this->info("Account #{$account->{$this->mapping['id']}}...");
                $account_params[] = $this->prepareData($account);
                $this->objMethod = 'create';
                $this->apiMethod = 'Add';

                $error = $this->getExistingRecord('Account', 'qb_account_id', $account, $account_params);
                if ($error) return true;

                $this->info(json_encode($account_params));

                $QBAccount = Account::{$this->objMethod}(...$account_params);
                $result = $this->qb_helper->dsCall($this->apiMethod, $QBAccount);

                if ($result) {
                    $this->info("Account Id #{$result->Id}");
                    $account->{$this->mapping['qb_account_id']} = $result->Id;
                }
                $account->save();
            }
            catch (\Exception $e) {
                $this->syncFailed($e, $account, 'account');
                return false;
            }

        });

        return 0;
    }

    private function prepareData($account)
    {
        return [
            'Name' => data_get($account, $this->mapping['name']),
            'Description' => data_get($account, $this->mapping['description']),
            'SubAccount' => data_get($account, $this->mapping['sub_account'])? 'true': 'false',
            'FullyQualifiedName' => data_get($account, $this->mapping['fully_qualified_name']),
            'Active' => data_get($account, $this->mapping['active'])? 'true' : 'false',
            'Classification' => data_get($account, $this->mapping['classification']),
            'AccountType' => data_get($account, $this->mapping['account_type']),
            'AccountSubType' => data_get($account, $this->mapping['account_sub_type']),
            'CurrencyRef' => data_get($account, $this->mapping['currency_ref'])
        ];
    }
}
