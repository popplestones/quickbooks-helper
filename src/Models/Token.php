<?php

namespace Popplestones\Quickbooks\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

class Token extends Model
{
    protected $guarded = [];
    public $table = "quickbooks_tokens";
    protected $dates = [
        'access_token_expires_at',
        'refresh_token_expires_at'
    ];

    public function hasValidAccessToken(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->access_token_expires_at && Carbon::now()->lt($this->access_token_expires_at)
        );
    }

    public function hasValidRefreshToken(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->refresh_token_expires_at && Carbon::now()->lt($this->refresh_token_expires_at)
        );
    }

    public function parseOauthToken(OAuth2AccessToken $oauth_token): Self
    {
        $this->access_token = $oauth_token->getAccessToken();
        $this->access_token_expires_at = Carbon::parse($oauth_token->getAccessTokenExpiresAt());
        $this->realm_id = $oauth_token->getRealmID();
        $this->refresh_token = $oauth_token->getRefreshToken();
        $this->refresh_token_expires_at = Carbon::parse($oauth_token->getRefreshTokenExpiresAt());

        return $this;
    }

    public function remove(): Token
    {
        $user = $this->user;
        $this->delete();

        return $user->quickbooksToken()->make();
    }

    public function user()
    {
        $config = config('quickbooks.user');

        return $this->belongsTo($config['model'], $config['keys']['foreign'], $config['keys']['owner']);
    }
}
