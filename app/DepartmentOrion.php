<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartmentOrion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'PDivision';
    public $timestamps = false;
}
