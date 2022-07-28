<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Facades\CallbackManager;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use QuickBooksOnline\API\Facades\Customer;

class QbCustomerSync extends Command
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
        $this->mapping   = config('quickbooks.customer.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
        $this->callbacks = CallbackManager::getCallbacks('customers');
        $this->max_failed = self::MAX_FAILED;
        $this->objMethod = '';
        $this->apiMethod = '';
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:customer:sync {--id=*} {--limit=20} {--force} {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync customers with Quickbooks';

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

        $customers = $query->get();

        if ($customers->count() === 0)
            $this->info('No customers to sync');

        $customers->each(function ($customer) {
            try {
                $this->info("Processing Customer #{$customer->{$this->mapping['id']}}...");
                $customer_params[] = $this->prepareData($customer);
                $this->objMethod = 'create';
                $this->apiMethod = 'Add';

                $error = $this->getExistingRecord('Customer', 'qb_customer_id', $customer, $customer_params);
                if ($error) return true;


                if ($this->option('pretend')) return true;

                $qbCustomer = Customer::{$this->objMethod}(...$customer_params);
                $result = $this->qb_helper->dsCall($this->apiMethod, $qbCustomer);

                if ($result) {
                    $this->info("Success! Quickbooks Customer ID #{$result->Id}");
                    $customer->{$this->mapping['qb_customer_id']} = $result->Id;
                } else {
                    $this->warn('Adding customer failed!');
                    $this->warn(json_encode($customer));
                }
                $customer->{$this->mapping['synced_at']} = now();
                $customer->save();

            } catch (\Exception $e) {
                $this->syncFailed($e, $customer, 'customer');
                return false;
            }
        });

        return 0;
    }



    private function prepareData($customer)
    {
        return array_filter([
            'FullyQualifiedName' => data_get($customer, $this->mapping['fully_qualified_name']),
            'PrimaryEmailAddr' => [
                'Address' => data_get($customer, $this->mapping['primary_email_addr']),
            ],
            'PrimaryPhone' => [
                'FreeFormNumber' => data_get($customer, $this->mapping['primary_phone']),
            ],
            'DisplayName' => data_get($customer, $this->mapping['display_name']),
            'GivenName' => data_get($customer, $this->mapping['given_name']),
            'FamilyName' => data_get($customer, $this->mapping['family_name']),
            'CompanyName' => data_get($customer, $this->mapping['company_name']),
            'PrintOnCheckName' => data_get($customer, $this->mapping['print_on_check_name']),
            'Active' => data_get($customer, $this->mapping['active']),
            'Taxable' => data_get($customer, $this->mapping['taxable']),
            'Job' => data_get($customer, $this->mapping['job']),
            'BillWithParent' => data_get($customer, $this->mapping['bill_with_parent']),
            'CurrencyRef' => data_get($customer, $this->mapping['currency_ref']),
            'PreferredDeliveryMethod' => data_get($customer, $this->mapping['preferred_delivery_method']),
            'IsProject' => data_get($customer, $this->mapping['is_project']),
            'ShipAddr' => [
                'Line1' => data_get($customer, $this->mapping['shipping_line1']),
                'Line2' => data_get($customer, $this->mapping['shipping_line2']),
                'Line3' => data_get($customer, $this->mapping['shipping_line3']),
                'Line4' => data_get($customer, $this->mapping['shipping_line4']),
                'Line5' => data_get($customer, $this->mapping['shipping_line5']),
                'City' => data_get($customer, $this->mapping['shipping_city']),
                'Country' => data_get($customer, $this->mapping['shipping_country']),
                'CountryCode' => data_get($customer, $this->mapping['shipping_country_code']),
                'County' => data_get($customer, $this->mapping['shipping_county']),
                'CountrySubDivisionCode' => data_get($customer, $this->mapping['shipping_country_sub_division_code']),
                'PostalCode' => data_get($customer, $this->mapping['shipping_postal_code']),
                'PostalCodeSuffix' => data_get($customer, $this->mapping['shipping_postal_code_suffix']),
                'Lat' => data_get($customer, $this->mapping['shipping_lattitude']),
                'Long' => data_get($customer, $this->mapping['shipping_longitude']),
                'Tag' => data_get($customer, $this->mapping['shipping_tag']),
                'Note' => data_get($customer, $this->mapping['shipping_note']),
            ],
            'BillAddr' => [
                'Line1' => data_get($customer, $this->mapping['billing_line1']),
                'Line2' => data_get($customer, $this->mapping['billing_line2']),
                'Line3' => data_get($customer, $this->mapping['billing_line3']),
                'Line4' => data_get($customer, $this->mapping['billing_line4']),
                'Line5' => data_get($customer, $this->mapping['billing_line5']),
                'City' => data_get($customer, $this->mapping['billing_city']),
                'Country' => data_get($customer, $this->mapping['billing_country']),
                'CountryCode' => data_get($customer, $this->mapping['billing_country_code']),
                'County' => data_get($customer, $this->mapping['billing_county']),
                'CountrySubDivisionCode' => data_get($customer, $this->mapping['billing_country_sub_division_code']),
                'PostalCode' => data_get($customer, $this->mapping['billing_postal_code']),
                'PostalCodeSuffix' => data_get($customer, $this->mapping['billing_postal_code_suffix']),
                'Lat' => data_get($customer, $this->mapping['billing_lattitude']),
                'Long' => data_get($customer, $this->mapping['billing_longitude']),
                'Tag' => data_get($customer, $this->mapping['billing_tag']),
                'Note' => data_get($customer, $this->mapping['billing_note']),
            ],
        ], function ($val) {
            return ! is_null($val);
        });
    }
}
