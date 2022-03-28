<?php

namespace Popplestones\Quickbooks\Services;

use Exception;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\ReportService\ReportService;
use Popplestones\Quickbooks\Models\Token;

class QuickbooksClient
{
    protected array $configs;
    protected DataService $data_service;
    protected ReportService $report_service;
    protected Token $token;

    protected static $customerQuery;
    protected static $invoiceQuery;

    public function __construct($configs, $token)
    {
        $this->configs = $configs;
        $this->setToken($token);
    }

    public function authorizationUri(): string
    {
        return $this
            ->getDataService()
            ->getOAuth2LoginHelper()
            ->getAuthorizationcodeURL();
    }

    public function configureLogging(): DataService
    {
        try {
            if ($this->configs['logging']['enabled'] && dir($this->configs['logging']['location']))
            {
                $this->data_service->setLogLocation($this->configs['logging']['location']);

                return $this->data_service->enableLog();
            }
        }
        catch (Exception $e)
        {
            //TODO: do something with exception
        }

        return $this->data_service->disableLog();
    }

    public function setToken(Token $token): Self
    {
        $this->token = $token;
        unset($this->data_service);

        return $this;
    }

    public function deleteToken(): Self
    {
        return $this->setToken($this->token->remove());
    }

    public function hasValidRefreshToken(): bool
    {
        return $this->token->hasValidRefreshToken;
    }

    public function hasValidAccessToken(): bool
    {
        return $this->token->hasValidAccessToken;
    }

    public function getReportService(): ReportService
    {
        if (!$this->hasValidAccessToken() || !isset($this->report_service))
        {
            $this->report_service = new ReportService(
                $this->getDataService()
                ->getServiceContext()
            );
        }

        return $this->report_service;
    }

    public function getDataService(): DataService
    {
        if (!$this->hasValidAccessToken() || !isset($this->data_service))
        {
            $this->data_service = $this->makeDataService();
            $this->configureLogging();
        }

        return $this->data_service;
    }

    public function exchangeCodeForToken($code, $realm_id): Self
    {
        $oauth_token = $this
            ->getDataService()
            ->getOAuth2LoginHelper()
            ->exchangeAuthorizationCodeForToken($code, $realm_id);

        $this->getDataService()
            ->updateOAuth2Token($oauth_token);

        $this->token
            ->parseOauthToken($oauth_token)
            ->save();

        return $this;
    }

    protected function parseDataConfigs()
    {
        return [
            'auth_mode' => $this->configs['data_service']['auth_mode'],
            'baseUrl' => $this->configs['data_service']['base_url'],
            'ClientID' => $this->configs['data_service']['client_id'],
            'ClientSecret' => $this->configs['data_service']['client_secret'],
            'RedirectURI' => route('quickbooks.token'),
            'scope' => $this->configs['data_service']['scope']
        ];
    }

    protected function makeDataService()
    {
        $existing_keys = [
            'auth_mode' => null,
            'baseUrl' => null,
            'ClientID' => null,
            'ClientSecret' => null
        ];

        if ($this->hasValidAccessToken())
        {
            return DataService::configure(
                array_merge(
                    array_intersect_key($this->parseDataConfigs(), $existing_keys),
                    [
                        'accessTokenKey' => $this->token->access_token,
                        'QBORealmID' => $this->token->realm_id,
                        'refreshTokenKey' => $this->token->refresh_token
                    ]
                )
            );
        }

        if ($this->hasValidRefreshToken())
        {
            $data_service = DataService::Configure(
                array_merge(
                    array_intersect_key($this->parseDataConfigs(), $existing_keys),
                    [
                        'QBORealmID' => $this->token->realm_id,
                        'refreshTokenKey' => $this->token->refresh_token
                    ]
                )
            );

            $oauth_token = $data_service
                ->getOAuth2LoginHelper()
                ->refreshToken();

            $data_service->updateOAuth2Token($oauth_token);

            $this->token
                ->parseOauthToken($oauth_token)
                ->save();

            return $data_service;
        }

        return DataService::Configure($this->parseDataConfigs());
    }
}