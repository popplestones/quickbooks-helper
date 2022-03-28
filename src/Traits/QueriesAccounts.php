<?php

namespace Popplestones\Quickbooks\Traits;

use Closure;

trait QueriesAccounts
{

    private static Closure $accountQuery;
    private static Closure $accountFilter;


    public static function accounts()
    {
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
        return call_user_func(static::$accountFilter, $query);
    }
}