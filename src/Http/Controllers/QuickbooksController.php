<?php

namespace Popplestones\Quickbooks\Http\Controllers;

use App\Http\Controllers\Controller;

class QuickbooksController extends Controller
{

    public function connect()
    {
        return view('quickbooks::connect');
    }

    public function disconnect()
    {
        return view('quickbooks::connect');
    }
}