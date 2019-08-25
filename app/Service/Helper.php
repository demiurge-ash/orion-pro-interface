<?php

namespace App\Service;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Helper
{
    public static function orionDateFormat($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date)->toDateTimeLocalString();
    }

    public static function convertDate($date)
    {
        if (! empty($date)){
            $date = \DateTime::createFromFormat('d.m.Y', $date)->format('Y-m-d');
        }
        return $date;
    }

    public static function deconvertDate($date)
    {
        if (! empty($date)){
            $date = \DateTime::createFromFormat('Y-m-d', $date)->format('d.m.Y');
        }
        return $date;
    }

    public static function parseDate($date)
    {
        if (! empty($date)){
            $date = Carbon::parse($date)->format('Y-m-d');
        }
        return $date;
    }

    public static function fileLink($file)
    {
        if ($file) {
            $filePath = Storage::url($file);
            return '<a href="' . $filePath . '" target="_blank" download>скачать</a>';
        }
    }

    public static function savePhoto($request)
    {
        $file = $request->file('photo');
        $restore = $request->restore_photo;

        if ( ! empty($restore) && (!$file) ) return $restore;

        if ( ! empty($file)) return $file->store('pass');
    }

    public static function photoBlobToImage($photo)
    {
        if( ! empty($photo))
            return '<img height="250" id="current_photo" src="data:image/jpeg;base64,' . base64_encode($photo) . '"/>';
    }

    public static function initials($person)
    {
        // UTF-8 hack
        mb_internal_encoding("UTF-8");

        if ( ! empty($person['FirstName'])) {
            $firstNameInitial = mb_substr($person['FirstName'], 0, 1) . '.';
        }

        if ( ! empty($person['MiddleName'])) {
            $middleNameInitial = mb_substr($person['MiddleName'], 0, 1) . '.';
        }

        if ( ! empty($person['LastName'])) {
            $lastName = self::mb_ucfirst(mb_strtolower($person['LastName']));
        }

        return ($lastName ?? '') .' '. ($firstNameInitial ?? '') .' '. ($middleNameInitial ?? '');
    }

    // UTF-8 version of ucfirst method
    public static function mb_ucfirst($text) {
        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    /**
     * Convert string in UTF if is not in UTF
     * @param $string
     * @return string
     */
    public static function convertUTF($string)
    {
        if (self::checkUTF($string)) return $string;
        $string = iconv('windows-1251','UTF-8',$string);
        return $string;
    }

    /**
     * Check if string in UTF
     * @param $string
     * @return bool
     */
    public static function checkUTF($string)
    {
        if (preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $string))
            return true;
    }

    public static function sortAndAddNull($array)
    {
        usort($array, array('self','sortByName'));
        array_unshift($array,  (object)['Id' => 0,'Name' => '———']);
        return $array;
    }

    public static function sortByName($a, $b)
    {
        return strcmp($a->Name, $b->Name);
    }

}