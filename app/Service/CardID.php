<?php

namespace App\Service;

class CardID
{
    public static function isCurrent($data)
    {
        return ( !empty($data->current_card_id) && ($data->card_id == $data->current_card_id) );
    }

    public static function isChanged($data)
    {
        return ( !empty($data->current_card_id) && !empty($data->card_id) && ($data->card_id != $data->current_card_id) );
    }

    public static function isDeleted($data)
    {
        return ( !empty($data->current_card_id) && empty($data->card_id) );
    }

    public static function isNew($data)
    {
        return ( empty($data->current_card_id) && !empty($data->card_id) );
    }

}