<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;
use Illuminate\Contracts\Container\BindingResolutionException;

class QbCustomerImport extends Command
{
    use ImportsModels;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modelName = config('quickbooks.customer.model');
        $mapping = config('quickbooks.customer.attributeMap');
        $addressModel = config('quickbooks.customer.address.model');
        $addressMapping = config('quickbooks.customer.address.attributeMap');

        $this->importModels(
            modelName: $modelName,
            mapping: $mapping,
            idField: 'qb_customer_id',
            tableName: 'Customer',
            callback: function($row) use ($modelName, $mapping, $addressModel, $addressMapping) {
                $customer = app($modelName)::updateOrCreate([$mapping['qb_customer_id'] => $row->Id], $this->setDataMapping($row, $mapping));
                $addressModel::updateOrCreate(['customer_id' => $customer->id, 'type' => 'billing'], $this->setAddressMapping($row, 'BillAddr', $addressMapping));
                $addressModel::updateOrCreate(['customer_id' => $customer->id, 'type' => 'shipping'], $this->setAddressMapping($row, 'ShipAddr', $addressMapping));
            }
        );
            
        return 0;
    }

    protected function setAddressMapping($row, $type, $mapping)
    {
        return [
            $mapping['line1'] => $row->$type?->Line1,
            $mapping['line2'] => $row->$type?->Line2,
            $mapping['line3'] => $row->$type?->Line3,
            $mapping['line4'] => $row->$type?->Line4,
            $mapping['line5'] => $row->$type?->Line5,
            $mapping['city'] => $row->$type?->City,
            $mapping['country'] => $row->$type?->Country,
            $mapping['country_code'] => $row->$type?->CountryCode,
            $mapping['country'] => $row->$type?->Country,
            $mapping['country_sub_division_code'] => $row->$type?->CountrySubDivisionCode,
            $mapping['postal_code'] => $row->$type?->PostalCode,
            $mapping['postal_code_suffix'] => $row->$type?->PostalCodeSuffix,
            $mapping['lattitude'] => $row->$type?->Lat,
            $mapping['longitude'] => $row->$type?->Long,
            $mapping['tag'] => $row->$type?->Tag,
            $mapping['note'] => $row->$type?->Note
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
            $mapping['primary_email_addr'] => $row->PrimaryEmailAddr
        ];
    }
}
