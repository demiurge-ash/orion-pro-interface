<?php

namespace App\Http\Controllers\Orion;

use App\Service\JsonResponse;

class CardConverterController
{
    public function convert($card)
    {
        $card = trim($card);
        $card = $this->convertRusToEng($card);

        if (strlen($card) != 10)
            return JsonResponse::successOrError('Неверный код карты');
        if (!preg_match('/^[a-zA-Z0-9]+$/', $card))
            return JsonResponse::successOrError('Неверный код карты');

        $card = $this->convertWiegandToTouchMemory($card);
        $card = strtoupper($card);

        return JsonResponse::successOrError('', $card);
    }

    // Convert Card ID
    // from Wiegand to Dallas Touch Memory
    public function convertWiegandToTouchMemory($card)
    {
        $code = $this->prepareCode($card);
        $keyCode = $this->bitDivision($code);
        $crc8 = $this->crc8($keyCode);
        return $crc8.substr($code,-14);
    }

    // Prepare full Wiegand code format
    // IN: EXAMPLE 0D001ED3E5
    // OUT: 0000001ED3E501
    public function prepareCode($card)
    {
        $code = substr($card,-8);
        $code = "000000" . $code . "01";
        return $code;
    }

    public function bitDivision($code)
    {
        $keyCode = array();

        for ( $i=1; $i<>(strlen($code)/2)+1; $i++) {
            $keyCode[$i] = hexdec(substr($code,-$i*2,2));
        }

        return $keyCode;
    }

    public function crc8($keyCode)
    {
        $crcTable = array (0,94,188,226,97,63,221,131,194,156,126,32,163,253,31,65,157,195,33,127,252,162,64,30,95,1,
            227,189,62,96,130,220,35,125,159,193,66,28,254,160,225,191,93,3,128,222,60,98,190,224,2,92,223,129,99,61,
            124,34,192,158,29,67,161,255,70,24,250,164,39,121,155,197,132,218,56,102,229,187,89,7,219,133,103,57,186,
            228,6,88,25,71,165,251,120,38,196,154,101,59,217,135,4,90,184,230,167,249,27,69,198,152,122,36,248,166,68,
            26,153,199,37,123,58,100,134,216,91,5,231,185,140,210,48,110,237,179,81,15,78,16,242,172,47,113,147,205,17,
            79,173,243,112,46,204,146,211,141,111,49,178,236,14,80,175,241,19,77,206,144,114,44,109,51,209,143,12,82,
            176,238,50,108,142,208,83,13,239,177,240,174,76,18,145,207,45,115,202,148,118,40,171,245,23,73,8,86,180,
            234,105,55,213,139,87,9,235,181,54,104,138,212,149,203,41,119,244,170,72,22,233,183,85,11,136,214,52,106,
            43,117,151,201,74,20,246,168,116,42,200,150,21,75,169,247,182,232,10,84,215,137,107,53);

        for ( $i=1; $i<>8; $i++) {
            $keyCode[8] = $crcTable[$keyCode[8] ^ $keyCode[$i]];
        }

        $crc8 = dechex($keyCode[8]);

        return $crc8;
    }

    function convertRusToEng($text, $arrow=0)
    {
        $str[0] = array(
            'й' => 'q', 'ц' => 'w', 'у' => 'e', 'к' => 'r', 'е' => 't', 'н' => 'y', 'г' => 'u', 'ш' => 'i', 'щ' => 'o',
            'з' => 'p', 'х' => '[', 'ъ' => ']', 'ф' => 'a', 'ы' => 's', 'в' => 'd', 'а' => 'f', 'п' => 'g', 'р' => 'h',
            'о' => 'j', 'л' => 'k', 'д' => 'l', 'ж' => ';', 'э' => '\'', 'я' => 'z', 'ч' => 'x', 'с' => 'c', 'м' => 'v',
            'и' => 'b', 'т' => 'n', 'ь' => 'm', 'б' => ',', 'ю' => '.','Й' => 'Q', 'Ц' => 'W', 'У' => 'E', 'К' => 'R',
            'Е' => 'T', 'Н' => 'Y', 'Г' => 'U', 'Ш' => 'I', 'Щ' => 'O', 'З' => 'P', 'Х' => '[', 'Ъ' => ']', 'Ф' => 'A',
            'Ы' => 'S', 'В' => 'D', 'А' => 'F', 'П' => 'G', 'Р' => 'H', 'О' => 'J', 'Л' => 'K', 'Д' => 'L', 'Ж' => ';',
            'Э' => '\'', '?' => 'Z', 'ч' => 'X', 'С' => 'C', 'М' => 'V', 'И' => 'B', 'Т' => 'N', 'Ь' => 'M', 'Б' => ',',
            'Ю' => '.',);
        $str[1] = array (
            'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г', 'i' => 'ш', 'o' => 'щ',
            'p' => 'з', '[' => 'х', ']' => 'ъ', 'a' => 'ф', 's' => 'ы', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р',
            'j' => 'о', 'k' => 'л', 'l' => 'д', ';' => 'ж', '\'' => 'э', 'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м',
            'b' => 'и', 'n' => 'т', 'm' => 'ь', ',' => 'б', '.' => 'ю','Q' => 'Й', 'W' => 'Ц', 'E' => 'У', 'R' => 'К',
            'T' => 'Е', 'Y' => 'Н', 'U' => 'Г', 'I' => 'Ш', 'O' => 'Щ', 'P' => 'З', '[' => 'Х', ']' => 'Ъ', 'A' => 'Ф',
            'S' => 'Ы', 'D' => 'В', 'F' => 'А', 'G' => 'П', 'H' => 'Р', 'J' => 'О', 'K' => 'Л', 'L' => 'Д', ';' => 'Ж',
            '\'' => 'Э', 'Z' => '?', 'X' => 'ч', 'C' => 'С', 'V' => 'М', 'B' => 'И', 'N' => 'Т', 'M' => 'Ь', ',' => 'Б',
            '.' => 'Ю', );
        return strtr($text,isset( $str[$arrow] )? $str[$arrow] :array_merge($str[0],$str[1]));
    }

}