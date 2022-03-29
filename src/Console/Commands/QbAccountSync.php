<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\Facades\Account;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbAccountSync extends Command
{
    const MAX_FAILED = 3;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accountMapping = config('quickbooks.account.attributeMap');
        $qb_helper = new QuickbooksHelper();

        $query = $qb_helper->accounts();

        if (!$query)
        {
            $this->error("NOT CONFIGURED: You must define a closure for querying your accounts with QuickbooksHelper::setAccountsQuery");
            return 1;
        }

        if ($ids = $this->option('id'))
            $query->whereIn($accountMapping['id'], $ids);
        else {
            QuickbooksHelper::applyAccountsFilter($query);
            $query
                ->where($accountMapping['sync_failed'], '<', self::MAX_FAILED)
                ->limit($this->option('limit'));
        }

        
        $accounts = $query->get();

        if ($accounts->count() == 0)
            $this->info('No accounts to sync');


        $accounts->each(function($account) use ($accountMapping, $qb_helper) {
            try
            {
                $this->info("Account #{$account->{$accountMapping['id']}}...");
                $account_params[] = $this->prepareData($account, $accountMapping);
                $objMethod = 'create';
                $apiMethod = 'Add';

                if ($account->{$accountMapping['qb_account_id']}) {
                    $targetAccountArray = $qb_helper->find('Account', $account->{$accountMapping['qb_account_id']});
                    if (!empty($targetAccountArray) && sizeof($targetAccountArray) === 1) {
                        $theAccount = current($targetAccountArray);
                        $objMethod = 'update';
                        $apiMethod = 'Update';
                        array_unshift($account_params, $theAccount);
                    } else {
                        if (!$this->option('force')) {
                            $message = "Account Not Exists #{$account->{$accountMapping['qb_invoice_id']}} for account #{$account->{$accountMapping['id']}}";
                            $this->info("Error: {$message}");
                            Log::channel('quickbooks')->error($message);
                            return true;
                        }
                        $account->$accountMapping['qb_account_id'] = null;
                    }
                }
                $QBAccount = Account::$objMethod(...$account_params);
                $result = $qb_helper->dsCall($apiMethod, $QBAccount);

                if ($result) {
                    $this->info("Account Id #{$result->Id}");
                    $account->{$accountMapping['qb_account_id']} = $result->Id;                    
                }
                $account->save();
            }
            catch (\Exception $e) {
                $account->increment($accountMapping['sync_failed']);
                $message = "{$e->getFile()}@{$e->getLine()} ==> {$e->getMessage()} for order #{$account->{$accountMapping['id']}}";
                $this->info("Error: {$message}");                
                Log::channel('quickbooks')->error($message);
                return false;
            }

        });

        return 0;
    }

    private function prepareData($account, $accountMapping)
    {
        return [
            'Name' => data_get($account, $accountMapping['name']),
            'Description' => data_get($account, $accountMapping['description']),
            'SubAccount' => data_get($account, $accountMapping['sub_account']),
            'FullyQualifiedName' => data_get($account, $accountMapping['fully_qualified_name']),
            'Active' => data_get($account, $accountMapping['active']),
            'Classification' => data_get($account, $accountMapping['classification']),
            'AccountType' => data_get($account, $accountMapping['account_type']),
            'AccountSubType' => data_get($account, $accountMapping['account_sub_type']),
            'CurrencyRef' => data_get($account, $accountMapping['currency_ref'])
        ];
    }
}
