<?php

namespace Popplestones\Quickbooks\Traits;

use Closure;

trait QueriesAccounts
{

    private static ?Closure $accountQuery = null;
    private static ?Closure $accountFilter = null;    

    public static function accounts()
    {
        if (!static::$accountQuery) return null;

        return call_user_func(static::$accountQuery);
    }

    public static function setAccountsQuery(Closure $accountFunc)
    {
        static::$accountQuery = $accountFunc;
    }
    public static function setAccountsFilter(Closure $accountFilterFunc)
    {
        static::$accountFilter = $accountFilterFunc;
    }

    public static function applyAccountsFilter($query)
    {
        if (!static::$accountFilter) return null;
        
        return call_user_func(static::$accountFilter, $query);
    }
}