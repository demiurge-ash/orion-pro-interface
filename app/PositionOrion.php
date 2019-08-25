<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PositionOrion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'PPost';
    public $timestamps = false;
}
