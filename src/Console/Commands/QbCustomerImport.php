<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbCustomerImport extends Command
{
    use SyncsWithQuickbooks;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:customer:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import customers from Quickbooks';

    public $modelName;
    public $mapping;
    public $addressModel;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.customer.model');
        $this->mapping = config('quickbooks.customer.attributeMap');
        $this->addressModel = config('quickbooks.customer.address.model');
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
            tableName: 'Customer',
            callback: function ($row) {
                $customer = app($this->modelName)::updateOrCreate([$this->mapping['qb_customer_id'] => $row->Id], $this->setDataMapping($row, $this->mapping));
                $this->addressModel::updateOrCreate(['customer_id' => $customer->id, 'type' => 'billing'], $this->setAddressMapping($row, 'BillAddr', "billing_", $this->mapping));
                $this->addressModel::updateOrCreate(['customer_id' => $customer->id, 'type' => 'shipping'], $this->setAddressMapping($row, 'ShipAddr', "shipping_", $this->mapping));
            }
        );

        return 0;
    }

    protected function setAddressMapping($row, $type, $prefix, $mapping)
    {
        return [
            $mapping["{$prefix}line1"] => $row->$type?->Line1,
            $mapping["{$prefix}line2"] => $row->$type?->Line2,
            $mapping["{$prefix}line3"] => $row->$type?->Line3,
            $mapping["{$prefix}line4"] => $row->$type?->Line4,
            $mapping["{$prefix}line5"] => $row->$type?->Line5,
            $mapping["{$prefix}city"] => $row->$type?->City,
            $mapping["{$prefix}country"] => $row->$type?->Country,
            $mapping["{$prefix}country_code"] => $row->$type?->CountryCode,
            $mapping["{$prefix}country"] => $row->$type?->Country,
            $mapping["{$prefix}country_sub_division_code"] => $row->$type?->CountrySubDivisionCode,
            $mapping["{$prefix}postal_code"] => $row->$type?->PostalCode,
            $mapping["{$prefix}postal_code_suffix"] => $row->$type?->PostalCodeSuffix,
            $mapping["{$prefix}lattitude"] => $row->$type?->Lat,
            $mapping["{$prefix}longitude"] => $row->$type?->Long,
            $mapping["{$prefix}tag"] => $row->$type?->Tag,
            $mapping["{$prefix}note"] => $row->$type?->Note
        ];
    }

    protected function setDataMapping($row, $mapping)
    {
        return [
            $mapping['given_name'] => $row->GivenName,
            $mapping['family_name'] => $row->FamilyName,
            $mapping['fully_qualified_name'] => $row->FullyQualifiedName,
            $mapping['company_name'] => $row->CompanyName,
            $mapping['display_name'] => $row->DisplayName,
            $mapping['print_on_check_name'] => $row->PrintOnCheckName,
            $mapping['active'] => $row->Active === 'true',
            $mapping['taxable'] => $row->Taxable === 'true',
            $mapping['job'] => $row->Job === 'true',
            $mapping['bill_with_parent'] => $row->BillWithParent === 'true',
            $mapping['currency_ref'] => $row->CurrencyRef,
            $mapping['preferred_delivery_method'] => $row->PreferredDeliveryMethod,
            $mapping['is_project'] => $row->IsProject === 'true',
            $mapping['primary_email_addr'] => $row->PrimaryEmailAddr?->Address,
            $mapping['primary_phone'] => $row->PrimaryPhone?->FreeFormNumber
        ];
    }
}
