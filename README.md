# Quickbooks Helper

Helper wrapping the [Quickbooks PHP SDK](https://github.com/intuit/Quickbooks-V3-PHP-SDK).

## Installation

1. Install the package

```bash
composer require popplestones/quickbooks-helper
```

3. Run our migrations to install the `quickbooks_tokens` table:

``bash
php artisan migrate --package=popplestones/quickbooks-helper

The package uses [auto registration](https://laravel.com/docs/packages#package-discovery) of Laravel.

## Configuration

1. You will need a `quickbooksToken` relationship on your `user` model, or optionally another model. There is a trait named `Poppletstones\Quickbooks\Traits\HasQuickbooksToken`, which you can iunclude on your `user` model, which will setup the relationship. To do this implement the following:

Add `use Popplestones\Quickbooks\Traits\HasQuickbooksToken;` to the top of `User.php` and also add the trait to the class. For example:

```php
use Popplestones\Quickbooks\Traits\HasQuickbooksToken;

class User extends Authenticateable
{
    use Notifiable, HasQuickbooksToken;
```

**Note: If your `User` model is not `App\models\User`, then you will need to publish the config and modify it as documented below.**

### Change model

1. Publish the config
```bash
php artisan vendor:publish --tag=quickbooks.config
```

2. Modify the model and fields as required
```bash
...
'user' => [
    'keys' => [
        'foreign' => 'user_id',
        'owner' => 'id'
    ],
    'model' => 'App\Models\User'
]
...
```

### Quickbooks API Keys

1. Add your client_id and client_secret to your .env file

### Minimal keys
```bash
QUICKBOOKS_CLIENT_ID=<client id provided by quickbooks>
QUICKBOOKS_CLIENT_SECRET=<client secret provided by quickbooks>
```

### Optional keys
```bash
QUICKBOOKS_API_URL=<Development|Production> # defaults to App's env value
QUICKBOOKS_DEBUG=<true|false>               # defaults to App's debug value
```

### Views

View files can be published by running

```bash
php artisan vendor:publish --tag=quickbooks-views
```

## Usage

Here is an example of getting the company information from Quickbooks using tinker:

### Note: Before doing these commands, go to your connect route (default /quickbooks/connect) to get a quickbooks token for your user.

```php
Auth::logInUsingId(1);
$quickbooks = app('Quickbooks')
$quickbooks->getDataService()->getCompanyInfo();
```

You can call any of the resources as documented [in the SDK](https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html).

## Using the included artisan commands

If you want to use the included artisan commands, you will need to provide the query to use to retrieve your data.
In your AppServiceProvider's boot method add your customer queries.
```php
QuickbooksHelper::setCustomerQuery(function() {
	return User::query()
	    ->with('client')
	    ->role(User::ROLE_APPROVED);
});

QuickbooksHelper::setCustomerFilter(function($query) {
	$query
	    ->has('orders')
	    ->whereNull('qb_customer_id')
	    ->where('sync_failed', '<', 3);
});
```
Once you have set the customerQuery and the customerFilter, you can then run the artisan command to sync customers with quickbooks.
```bash
php artisan qb:customer
```

In this provided example only customers that have orders and have not failed syncing more than 3 times and have not already been synced with quickbooks will be synced.
If you specify a customer to sync by ID like this:
```bash
php artisan qb:customer --id=123
```
The customer filter will be ignored, this enables you to update an existing customer that has already been synced.

Similarly to use the qb:invoice command you will also need to set the invoiceQuery and invoiceFilter, e.g.
```php
QuickbooksHelper::setInvoiceQuery(function() {
	return Order::query()
	    ->with(['user', 'items'])
	    ->whereHas('user', function ($q) {
	        $q->whereNotNull('qb_customer_id');
         })
        ->whereNotNull('paymentid');
});

QuickbooksHelper::setInvoiceFilter(function(&$query) {
	$query->where(function ($q) {
	    $q->whereNull('qb_invoice_id')
	    ->orWhereNull('qb_payment_id')
	    ->orWhere('sync', 1);
     })
     ->where('sync_failed', '<', 3)
});

```
## Middleware

If you have routes that will be dependent on the user's account having a usable QuickBooks OAuth token, there is an included middleware ```Codemonkey76\Quickbooks\Http\Middleware\QuickbooksConnected``` that gets registered as ```quickbooks``` that will ensure the account is linked and redirect them to the `connect` route if needed.

Here is an example route definition:

```php
Route::view('some/route/needing/quickbooks/token/before/using', 'some.view')
     ->middleware('quickbooks');
```
