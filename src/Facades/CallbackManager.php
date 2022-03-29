<?php
namespace Popplestones\Quickbooks\Facades;

use Illuminate\Support\Facades\Facade;
use Popplestones\Quickbooks\Services\CallbackManager as CallbackManagerService;

class CallbackManager extends Facade
{
    protected static function getFacadeAccessor() { return CallbackManagerService::class; }
}