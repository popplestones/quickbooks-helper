<?php

use function PHPSTORM_META\map;

return [
    'term'          => [
        'model'        => 'App\Models\Term',
        'attributeMap' => [
            'id'                    => 'id',
            'qb_term_id'            => 'qb_term_id',
            'name'                  => 'name',
            'discount_percent'      => 'discount_percent',
            'discount_days'         => 'discount_days',
            'active'                => 'active',
            'type'                  => 'type',
            'day_of_month_due'      => 'day_of_month_due',
            'discount_day_of_month' => 'discount_day_of_month',
            'due_next_month_days'   => 'due_next_month_days',
            'due_days'              => 'due_days',
        ]
    ],
    'account'       => [
        'model'        => 'App\Models\Account',
        'attributeMap' => [
            'id'                   => 'id',
            'name'                 => 'name',
            'description'          => 'description',
            'sub_account'          => 'sub_account',
            'fully_qualified_name' => 'fully_qualified_name',
            'active'               => 'active',
            'classification'       => 'classification',
            'account_type'         => 'account_type',
            'account_sub_type'     => 'account_sub_type',
            'currency_ref'         => 'currency_ref',
            'qb_account_id'        => 'qb_account_id',
            'sync_failed'          => 'sync_failed'
        ]
    ],
    'invoice'       => [
        'model'            => 'App\Models\Invoice',
        'lineRelationship' => 'invoice_lines',
        'settings'         => [
            'austax'         => '',
            'overseastax'    => '',
            'defaultitem'    => '',
            'paymentaccount' => '',
            'shippingtax'    => '',
            'shipitem'       => ''
        ],
        'attributeMap'     => [
            'currency_ref'             => 'currency_ref',
            'exchange_rate'            => 'exchange_rate',
            'bill_email'               => 'bill_email',
            'transaction_date'         => 'transaction_date',
            'ship_date'                => 'ship_date',
            'tracking_num'             => 'tracking_num',
            'due_date'                 => 'due_date',
            'private_note'             => 'private_note',
            'customer_memo'            => 'customer_memo',
            'ship_method'              => 'ship_method',
            'apply_tax_after_discount' => 'apply_tax_after_discount',
            'total_amount'             => 'total_amount',
            'qb_invoice_id'            => 'qb_invoice_id',
            'customer_ref'             => 'customer_id',
            'transaction_type'         => 'type',
            'line1'                    => 'line1',
            'line2'                    => 'line2',
            'line3'                    => 'line3',
            'line4'                    => 'line4',
            'line5'                    => 'line5',
            'city'                     => 'city',
            'country'                  => 'country',
            'state'                    => 'state',
            'postal_code'              => 'postal_code',
            'postal_code_suffix'       => 'postal_code_suffix',
            'country_code'             => 'country_code',
            'lat'                      => 'lat',
            'long'                     => 'long',
            'tag'                      => 'tag',
            'note'                     => 'note',
            'type'                     => 'type',
            'doc_number'               => 'doc_number'
        ],
    ],
    'invoiceLine'   => [
        'model'        => 'App\Models\InvoiceLine',
        'attributeMap' => [
            'invoice_ref' => 'invoice_id',
            'amount'      => 'amount',
            'detail_type' => 'detail_type',
            'description' => 'description',
            'line_num'    => 'line_num',
            'item_ref'    => 'product_id',
            'qty'         => 'qty',
            'unit_price'  => 'unit_price'
        ]
    ],
    'item'          => [
        'model'        => 'App\Models\Product',
        'attributeMap' => [
            'id'                    => 'id',
            'qb_id'                 => 'qb_product_id',
            'name'                  => 'name',
            'description'           => 'description',
            'active'                => 'active',
            'sub_item'              => 'sub_item',
            'parent_ref'            => 'parent_ref',
            'level'                 => 'level',
            'fully_qualified_name'  => 'fully_qualified_name',
            'taxable'               => 'taxable',
            'sales_tax_included'    => 'sales_tax_included',
            'unit_price'            => 'unit_price',
            'type'                  => 'type',
            'income_account_ref'    => 'income_account_ref',
            'purchase_tax_included' => 'purchase_tax_included',
            'purchase_cost'         => 'purchase_cost',
            'expense_account_ref'   => 'expense_account_ref',
            'track_qty_on_hand'     => 'track_qty_on_hand',
            'qty_on_hand'           => 'qty_on_hand',
            'sales_tax_code_ref'    => 'sales_tax_code_ref',
            'purchase_tax_code_ref' => 'purchase_tax_code_ref',
            'sync_failed'           => 'sync_failed'
        ]
    ],
    'customer'      => [
        'model'        => 'App\Models\Customer',
        'attributeMap' => [
            'id'                                 => 'id',
            'given_name'                         => 'given_name',
            'family_name'                        => 'family_name',
            'fully_qualified_name'               => 'fully_qualified_name',
            'company_name'                       => 'company_name',
            'display_name'                       => 'display_name',
            'print_on_check_name'                => 'print_on_check_name',
            'active'                             => 'active',
            'taxable'                            => 'taxable',
            'job'                                => 'job',
            'bill_with_parent'                   => 'bill_with_parent',
            'currency_ref'                       => 'currency_ref',
            'preferred_delivery_method'          => 'preferred_delivery_method',
            'is_project'                         => 'is_project',
            'primary_email_addr'                 => 'email',
            'primary_phone'                      => 'phone',
            'shipping_line1'                     => 'shipping_address.line1',
            'shipping_line2'                     => 'shipping_address.line2',
            'shipping_line3'                     => 'shipping_address.line3',
            'shipping_line4'                     => 'shipping_address.line4',
            'shipping_line5'                     => 'shipping_address.line5',
            'shipping_city'                      => 'shipping_address.city',
            'shipping_country'                   => 'shipping_address.country',
            'shipping_country_code'              => 'shipping_address.country_code',
            'shipping_county'                    => 'shipping_address.county',
            'shipping_country_sub_division_code' => 'shipping_address.state',
            'shipping_postal_code'               => 'shipping_address.postal_code',
            'shipping_postal_code_suffix'        => 'shipping_address.postal_code_suffix',
            'shipping_lattitude'                 => 'shipping_address.lattitude',
            'shipping_longitude'                 => 'shipping_address.longitude',
            'shipping_tag'                       => 'shipping_address.tag',
            'shipping_note'                      => 'shipping_address.note',
            'billing_line1'                      => 'billing_address.line1',
            'billing_line2'                      => 'billing_address.line2',
            'billing_line3'                      => 'billing_address.line3',
            'billing_line4'                      => 'billing_address.line4',
            'billing_line5'                      => 'billing_address.line5',
            'billing_city'                       => 'billing_address.city',
            'billing_country'                    => 'billing_address.country',
            'billing_country_code'               => 'billing_address.country_code',
            'billing_county'                     => 'billing_address.county',
            'billing_country_sub_division_code'  => 'billing_address.state',
            'billing_postal_code'                => 'billing_address.postal_code',
            'billing_postal_code_suffix'         => 'billing_address.postal_code_suffix',
            'billing_lattitude'                  => 'billing_address.lattitude',
            'billing_longitude'                  => 'billing_address.longitude',
            'billing_tag'                        => 'billing_address.tag',
            'billing_note'                       => 'billing_address.note',
            'term_id'                            => 'term_id',
            'qb_customer_id'                     => 'qb_customer_id',
            'sync_failed'                        => 'sync_failed',
        ],
        'address'      => [
            'model' => 'App\Models\Address',
        ]
    ],
    'paymentMethod' => [
        'model'        => 'App\Models\PaymentMethod',
        'attributeMap' => [
            'name'                 => 'name',
            'active'               => 'active',
            'type'                 => 'type',
            'qb_payment_method_id' => 'qb_payment_method_id'
        ]
    ],
    'taxCode'       => [
        'model'        => 'App\Models\TaxRate',
        'attributeMap' => [
            'name'           => 'name',
            'description'    => 'description',
            'taxable'        => 'taxable',
            'active'         => 'active',
            'hidden'         => 'hidden',
            'tax_group'      => 'tax_group',
            'qb_tax_code_id' => 'qb_tax_code_id'
        ]
    ],
    'payment'       => [
        'model'            => 'App\Models\Payment',
        'lineRelationship' => 'payment_lines',
        'attributeMap'     => [
            'id'                => 'id',
            'transaction_date'  => 'transaction_date',
            'currency_ref'      => 'currency_ref',
            'exchange_rate'     => 'exchange_rate',
            'total_amount'      => 'total_amount',
            'customer_ref'      => 'customer_id',
            'deposit_account'   => 'account_id',
            'payment_method'    => 'payment_method_id',
            'private_note'      => 'private_note',
            'qb_payment_id'     => 'qb_payment_id',
            'payment_ref'       => 'payment_ref',
            'sync_failed'       => 'sync_failed',
            'account_type'      => 'account.account_type',
            'account_id'        => 'account.qb_account_id',
            'customer_id'       => 'customer.qb_customer_id',
            'payment_method_id' => 'paymentMethod.qb_payment_method_id'
        ]
    ],
    'paymentLine'   => [
        'model'        => 'App\Models\PaymentLine',
        'attributeMap' => [
            'amount'      => 'amount',
            'invoice_ref' => 'invoice_id',
            'payment_ref' => 'payment_id'
        ]
    ],
    'data_service'  => [
        'auth_mode'     => 'oauth2',
        'base_url'      => env('QUICKBOOKS_API_URL', config('app.env') === 'production' ? 'Production' : 'Development'),
        'client_id'     => env('QUICKBOOKS_CLIENT_ID'),
        'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
        'scope'         => 'com.intuit.quickbooks.accounting'
    ],

    'logging' => [
        'enabled' => env('QUICKBOOKS_DEBUG', config('app.debug')),

        'location' => storage_path('logs')
    ],

    'route' => [
        'middleware' => [
            'authenticated' => 'auth',
            'default'       => 'web'
        ],

        'paths' => [
            'connect'    => 'connect',
            'disconnect' => 'disconnect',
            'token'      => 'token',
            'redirect'   => 'dashboard'
        ],

        'prefix' => 'quickbooks'
    ],

    'user' => [
        'keys'  => [
            'foreign' => 'user_id',
            'owner'   => 'id'
        ],
        'model' => 'App\Models\User'
    ]

];
