<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupOrion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Groups';
    public $timestamps = false;

    // maybe bug in Integration Module
    // u need shift group for pMark (cards table) field: GroupID
    const ACCESS_LEVELS_SHIFT = 2;
}
