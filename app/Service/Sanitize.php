<?php

namespace App\Service;

class Sanitize
{
    public static function numberOrError($number)
    {
        if (is_numeric($number)) return true;
        else abort(500, 'Код сотрудника должен быть только числом');
    }
}