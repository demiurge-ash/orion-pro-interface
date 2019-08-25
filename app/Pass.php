<?php

namespace App;

use App\Service\Helper;
use Illuminate\Database\Eloquent\Model;

class Pass extends Model
{
    public $fillable = [
            'card_id',
            'date_employment',
            'date_dismissal',
            'department',
            'organization',
            'status',
            'citizenship',
            'passport',
            'last_name',
            'first_name',
            'middle_name',
            'group',
            'position',
            'photo',
            'pass_valid_from',
            'pass_valid_to',
            'pass_color',
            'info',
            'comments',
     ];

    public static function getPassDataForEdit($pass, $tabNumber)
    {
        $person = Pass::whereId($tabNumber)->first();
        if($person) {
            $pass->photo = $person->photo;
            $pass->pass_color = $person->pass_color;
            $pass->citizenship = $person->citizenship;
            $pass->passport = $person->passport;
            $pass->date_employment = $person->date_employment;
            $pass->date_dismissal = $person->date_dismissal;
            $pass->file_link = Helper::fileLink($person->photo);
        }
        return $pass;
    }

}
