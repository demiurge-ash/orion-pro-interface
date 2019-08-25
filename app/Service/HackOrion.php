<?php

namespace App\Service;

use App\PassOrion;
use App\PersonOrion;

// due bug in Integration Module
// we force this fields manually
class HackOrion
{
    public static function pass($id, $personInfo)
    {
        $initials = Helper::initials($personInfo);
        $data = [
            'OwnerName' => $initials, // convert FIO to Initials
            'Status' => '768', // access to System Shell & to Personal Card
            'CodePAdd' => 'ю' // FE01 — additional Code ID
        ];
        self::hackPass($id, $data);
    }

    public static function passInitials($id, $personInfo)
    {
        $initials = Helper::initials($personInfo);
        $data['OwnerName'] = $initials; // convert FIO to Initials
        self::hackPass($id, $data);
    }

    public static function hackPass($id, $data)
    {
        PassOrion::where('Id',  $id)->update($data);
    }

}