<?php
namespace Popplestones\Quickbooks\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Popplestones\Quickbooks\Traits\QueriesAccounts;

class QuickbooksHelper
{
    use QueriesAccounts;

    public $app;
    public $dataService;

    public function __construct()
    {
        $recentConnectedUser = DB::table('quickbooks_tokens')->latest('refresh_token_expires_at')->first();
        if (!$recentConnectedUser) return;

        $userIdField = config('quickbooks.user.keys.foreign');
        Auth::logInUsingId($recentConnectedUser->$userIdField);
        $this->app = app('Quickbooks');
        $this->dataService = $this->app->getDataService();
        $this->dataService->setMinorVersion('34');
    }

    public function find($tableName, $id, $primaryColumn = 'Id')
    {
        try {
            return $this->dsCall('Query', "SELECT * FROM {$tableName} WHERE {$primaryColumn}='{$id}'");
        } catch (\Exception $e) {
            Log::chanel('quickbooks')->error(__METHOD__ . $e->getMessage());
        }
    }

    public function dsCall($method, ...$args)
    {
        $result = $this->dataService->$method(...$args);

        if ($error = $this->dataService->getLastError()) {
            $message = '';
            if ($callable = debug_backtrace()[1]) {
                    $message .= "{$callable['class']}@{$callable['function']} ==> REQ ==> ".json_encode($callable['args']).PHP_EOL;
            }
            $message .= $error->getIntuitErrorDetail();
            $message .= " {$error->getHttpStatusCode()}; {$error->getOAuthHelperError()}; {$error->getResponseBody()}";

            Log::channel('quickbooks')->error($message);
        }

        return $result;
    }
}
