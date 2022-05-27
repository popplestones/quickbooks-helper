<?php

namespace Popplestones\Quickbooks\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Popplestones\Quickbooks\Services\QuickbooksClient;

class QuickbooksController extends Controller
{

    public function connect(QuickbooksClient $quickbooks, ViewFactory $view_factory)
    {
        if ($quickbooks->hasValidRefreshToken())
        {
            return $view_factory
            ->make('quickbooks::disconnect')
            ->with('company', $quickbooks->getDataService()->getCompanyInfo());
        }

        return $view_factory
        ->make('quickbooks::connect')
        ->with('authorization_uri', $quickbooks->authorizationUri());

    }

    public function disconnect(Redirector $redirector, Request $request, QuickbooksClient $quickbooks)
    {
        $quickbooks->deleteToken();

        return $redirector->back();
    }

    public function token(Redirector $redirector, Request $request, QuickbooksClient $quickbooks, UrlGenerator $url_generator)
    {
        $quickbooks->exchangeCodeForToken($request->get('code'), $request->get('realmId'));

        return $redirector->intended($url_generator->route(config('quickbooks.route.paths.redirect')));
    }
}
