<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyOrion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'PCompany';
    public $timestamps = false;
}
