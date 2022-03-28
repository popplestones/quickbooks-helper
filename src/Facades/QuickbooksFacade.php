<?php
namespace Popplestones\Quickbooks\Facades;

use Illuminate\Support\Facades\Facade;

class QuickbooksFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'quickbooks'; }
}