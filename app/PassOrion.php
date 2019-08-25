<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PassOrion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'pMark';
    public $timestamps = false;

    public function groups()
    {
        return $this->hasOne(GroupOrion::class,'ID','GroupID')->withDefault();
    }

}
