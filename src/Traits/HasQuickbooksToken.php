<?php

namespace Popplestones\Quickbooks\Traits;

use Popplestones\Quickbooks\Models\Token;

trait HasQuickbooksToken
{
    public function quickbooksToken()
    {
        return $this->hasOne(Token::class);
    }
}