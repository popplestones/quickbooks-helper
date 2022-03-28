<?php

namespace Popplestones\Quickbooks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $table = "quickbooks_tokens";
}
