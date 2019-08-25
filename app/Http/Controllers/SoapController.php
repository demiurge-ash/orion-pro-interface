<?php

namespace App\Http\Controllers;

use App\GroupOrion;
use App\Service\CardID;
use App\Service\HackOrion;
use App\Service\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

/*
    +"ServiceVersion": "1.2.0.1236"
    +"SoftwareVersion": "1.12"
    +"SoftwareName": "АРМ Орион Про"
    +"DatabaseVersion": ""
*/

class SoapController
{
    public $client;
    public $error;

    public function __construct()
    {
        $opts = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $context = stream_context_create($opts);

        try {
            $this->client = new SoapClient(
                env('WDSL'), //server
                array(
                    'stream_context' => $context,
                    'trace' => true,
                    'exceptions' => true,
                )
            );
            return $this->client;
        }
        catch ( SoapFault $error) {
            abort('503',$error->getMessage());
        }

    }

    public function checkError($result, $message="Отсутствуют данные")
    {
        if ($result->Success == false) {
            $this->error = $result->ServiceError->InnerExceptionMessage;
            return true;
        } elseif (empty($result->OperationResult)) {
            $this->error = $message;
            return true;
        }
        return false;
    }

    // checking connection through getting service info
    public function checkConnection()
    {
        try {
            return $this->client->GetServiceInfo();
        }
        catch ( SoapFault $error) {
            abort('503',$error->getMessage());
        }
    }

    public function addPerson($personInfo)
    {
        $person = $this->client->AddPerson($personInfo);
        return $person->OperationResult;
    }

    public function getPerson($personID)
    {
        $person = $this->client->GetPersonById($personID);
        return $person->OperationResult;
    }

    public function deletePerson($personID)
    {
        $person = $this->getPerson($personID);
        $result = $this->client->DeletePerson($person);
        return $result;
    }

    public function GetPersonPassList($personID)
    {
        $person = $this->client->GetPersonPassList($personID);
        return $person->OperationResult;
    }

    // Return Array with Card ID of specific Person
    public function getCardID($personID)
    {
        $person = $this->client->GetPersonById($personID);
        $result = $this->client->GetPersonPassList($person->OperationResult);
        return $result->OperationResult;
    }

    public function getAccessLevels($offset='', $count='')
    {
        $result = $this->client->GetAccessLevels($offset, $count);
        return $result->OperationResult;
    }

    public function getAccessLevelById($id)
    {
        $result = $this->client->GetAccessLevelById($id);
        if ($this->checkError($result, "Отсутствуют данные по текущей группе допуска")) return false;
        return $result->OperationResult;
    }

    public function convertGroupToAccessLevel($group)
    {
        $accessOffset = $group - GroupOrion::ACCESS_LEVELS_SHIFT;
        $accessLevel = $this->getAccessLevels( $accessOffset,1); // Get GroupID for pMark
        return $accessLevel;
    }

    public function GetKeyData($cardID)
    {
        $result = $this->client->GetKeyData($cardID);
        return $result->OperationResult;
    }

    public function getCompanies($filter="")
    {
        $result = $this->client->SearchCompany($filter);
        return  Helper::sortAndAddNull($result->OperationResult);
    }

    public function getDepartments($filter="")
    {
        $result = $this->client->SearchDepartment($filter);
        return  Helper::sortAndAddNull($result->OperationResult);
    }

    public function getPositions()
    {
        $result = $this->client->GetPositions();
        return  Helper::sortAndAddNull($result->OperationResult);
    }

    public function getCardsByPersonId($id)
    {
        $cards = [];
        $person = $this->client->GetPersonById($id);
        $cardNumbers = $this->client->GetPersonPassList($person->OperationResult);
        foreach ($cardNumbers->OperationResult as $item) {
            $cardsDataRaw = $this->client->GetKeyData($item);
            $cardsData = $cardsDataRaw->OperationResult;
            $cardsData->StartDate = Helper::parseDate($cardsData->StartDate);
            $cardsData->EndDate = Helper::parseDate($cardsData->EndDate);
            $cards[$item] = $cardsData;
        }
        // return only first card. 1 employee = 1 card
        return array_shift ($cards);
    }

    public function deleteCardsByPersonID($id)
    {
        $person = $this->GetPerson($id);
        $cards = $this->GetPersonPassList($person);
        foreach ($cards as $card) {
            $this->client->DeletePass($card);
        }
        return;
    }

    public function putCard($data, $personInfo)
    {
        $result = $this->client->PutPassWithAccLevels(
            $data->card_id,                                     //CardNo "xs:string"/>
            $personInfo,                                        //PersonData "ns1:TPersonData"/>
            $this->convertGroupToAccessLevel($data->group),     //AccessLevels "ns1:TAccessLevels"/>
            Helper::orionDateFormat($data->pass_valid_from),    //DateBegin "xs:dateTime"/>
            Helper::orionDateFormat($data->pass_valid_to),      //DateEnd "xs:dateTime"/>
            ''                                                  //Token "xs:string"/>
        );

        if ($this->checkError($result)) return false;

        $newCard = $this->GetKeyData($data->card_id);
        HackOrion::pass($newCard->Id, $personInfo);

        return true;
    }

    public function updateCard($data)
    {
        $keyData = $this->GetKeyData($data->card_id);

        $keyData->Code = $data->card_id;
        $accessData = $this->getAccessLevelById($data->group);
        if ( empty($accessData->Id)) return;
        $keyData->AccessLevelId = $accessData->Id;
        $keyData->StartDate = Helper::orionDateFormat($data->pass_valid_from);
        $keyData->EndDate = Helper::orionDateFormat($data->pass_valid_to);

        $this->client->UpdateKeyData($keyData);

        return $keyData->Id;
    }

    public function updateOrion($personInfo, $data)
    {
        if (CardID::isCurrent($data)) {
            $keyID = $this->updateCard($data);
            $this->client->UpdatePerson($personInfo);
            HackOrion::passInitials($keyID, $personInfo);

        } else if (CardID::isChanged($data)) {
            $this->client->DeletePersonByPass($data->current_card_id);
            $this->putCard($data, $personInfo);

        } else if (CardID::isDeleted($data)) {
            $this->client->DeletePersonByPass($data->current_card_id);

        } else if (CardID::isNew($data)) {
            $this->putCard($data, $personInfo);

        }

        return;
    }

}