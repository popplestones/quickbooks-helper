<?php

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
        'attributeMap' => [
            'qb_customer_id' => 'qb_customer_id',
            'fully_qualified_name' => 'name',
            'email_address' => 'email',
            'phone' => 'phone',
            'display_name' => 'name',
            'given_name' => 'client.firstName',
            'family_name' => 'client.lastName',
            'company_name' => 'businessName',
            'address_line_1' => 'address',
            'city' => 'city',
            'suburb' => 'suburb',
            'postcode' => 'postcode',
            'country' => 'country',
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