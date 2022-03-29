<?php

use function PHPSTORM_META\map;

return [
    'account' => [
        'model' => 'App\Models\Account',
        'attributeMap' => [
            'id' => 'id',
            'name' => 'name',
            'description' => 'description',
            'sub_account' => 'sub_account',
            'fully_qualified_name' => 'fully_qualified_name',
            'active' => 'active',
            'classification' => 'classification',
            'account_type' => 'account_type',
            'account_sub_type' => 'account_sub_type',
            'currency_ref' => 'currency_ref',
            'qb_account_id' => 'qb_account_id',
            'sync_failed' => 'sync_failed'
        ]
    ],
    'invoice' => [
        'settings' => [
            'austax' => '',
            'overseastax' => '',
            'defaultitem' => '',
            'paymentaccount' => '',
            'shippingtax' => '',
            'shipitem' => ''
        ],
        'attributeMap' => [
            'customer' => 'user',
            'billing_country' => 'billing_country',
            'qb_invoice_id' => 'qb_invoice_id',
            'qb_payment_id' => 'qb_payment_id',
            'qb_creditmemo_id' => 'qb_creditmemo_id',
            'sync' => 'sync'
        ],
    ],
    'item' => [
        'attributeMap' => [

        ]
    ],

    'customer' => [
        'model' => 'App\Models\Customer',
        'attributeMap' => [
            'given_name' => 'given_name',
            'family_name' => 'family_name',
            'fully_qualified_name' => 'fully_qualified_name',
            'company_name' => 'company_name',
            'display_name' => 'display_name',
            'print_on_check_name' => 'print_on_check_name',
            'active' => 'active',
            'taxable' => 'taxable',
            'job' => 'job',
            'bill_with_parent' => 'bill_with_parent',
            'currency_ref' => 'currency_ref',
            'preferred_delivery_method' => 'preferred_delivery_method',
            'is_project' => 'is_project',
            'primary_email_addr' => 'primary_email_addr',
            'qb_customer_id' => 'qb_customer_id'
        ],
        'address' => [
            'model' => 'App\Models\Address',
            'attributeMap' => [
                'line1' => 'line1',
                'line2' => 'line2',
                'line3' => 'line3',
                'line4' => 'line4',
                'line5' => 'line5',
                'city' => 'city',
                'country' => 'country',
                'country_code' => 'country_code',
                'country_sub_division_code' => 'state',
                'postal_code' => 'postal_code',
                'postal_code_suffix' => 'postal_code_suffix',
                'lattitude' => 'lattitude',
                'longitude' => 'longitude',
                'tag' => 'tag',
                'note' => 'note'
            ]
        ]
    ],

    'data_service' => [
        'auth_mode' => 'oauth2',
        'base_url' => env('QUICKBOOKS_API_URL', config('app.env') === 'production' ? 'Production' : 'Development'),
        'client_id' => env('QUICKBOOKS_CLIENT_ID'),
        'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
        'scope' => 'com.intuit.quickbooks.accounting'
    ],

    'logging' => [
        'enabled' => env('QUICKBOOKS_DEBUG', config('app.debug')),

        'location' => storage_path('logs')
    ],

    'route' => [
        'middleware' => [
            'authenticated' => 'auth',
            'default' => 'web'
        ],

        'paths' => [
            'connect' => 'connect',
            'disconnect' => 'disconnect',
            'token' => 'token'
        ],

        'prefix' => 'quickbooks'
    ],

    'user' => [
        'keys' => [
            'foreign' => 'user_id',
            'owner' => 'id'
        ],
        'model' => 'App\Models\User'
    ]

];