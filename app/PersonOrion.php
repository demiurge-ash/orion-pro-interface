<?php

namespace App;

use App\Service\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PersonOrion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'pList';
    public $timestamps = false;
    public $defaultName = ['Name' => '———'];

    public static $convertor = [
        //'id' => 'ID',
        'last_name' => 'Name',
        'first_name' => 'FirstName',
        'middle_name' => 'MidName',
        'status' => 'Status',
        'organization' => 'Company',
        'department' => 'Section',
        'position' => 'Post',
        'tab_id' => 'TabNumber',
    ];

    //
    // Prepare object for Orion
    //
    public static function personOrionInfo($data, $request)
    {
        return [
            'Id' => '',
            'LastName' => $data->last_name,
            'FirstName' => $data->first_name,
            'MiddleName' => $data->middle_name,
            'BirthDate' => Helper::orionDateFormat('1899-12-30'), // fake date
            'Company' => '',
            'CompanyId' => $data->organization,
            'Department' => '',
            'DepartmentId' => $data->department,
            'Position' => '',
            'PositionId' => $data->position,
            'TabNum' => $data->id, // Табельный номер — ID from MySQL
            'Phone' => '',
            'HomePhone' => '',
            'Address' => '',
            'Photo' =>  $data->orionPhoto,
            'AccessLevelId' => '',
            'Status' => $data->status, //
            'ContactIdIndex' => 0, //"xs:int"/>
            'IsLockedDayCrossing' => false, //"xs:boolean"/>
            'IsFreeShedule' => true, //"xs:boolean"/>
            'ExternalId' => $data->id, // ID of MySQL "xs:string"/>
            'IsInArchive' => false, //"xs:boolean"/>
            'ArchivingTimeStamp' => Carbon::now()->toDateTimeLocalString(), //"xs:dateTime"/>
            'DocumentType' => '', //"xs:int"/>
            'DocumentSerials' => '', //"xs:string"/>
            'DocumentNumber' => '', //"xs:string"/>
            'DocumentIssueDate' => Helper::orionDateFormat($data->pass_valid_from), //"xs:dateTime"/>
            'DocumentEndingDate' => Helper::orionDateFormat($data->pass_valid_to), //"xs:dateTime"/>
            'DocumentIsser' => '', //"xs:string"/>
            'DocumentIsserCode' => '', //"xs:string"/>
            'Sex' => 0, //"xs:int"/>
            'Birthplace' => '', //"xs:string"/>
            'EmailList' => '', //"xs:string"/>
            'IsInBlackList' => false, //"xs:boolean"/>
            'BlackListComment' => '', //"xs:string"/>
            'IsDismissed' => false, //"xs:boolean"/>,
            'DismissedComment' => '', //"xs:string"/>
            'ChangeTime' => Carbon::now()->toDateTimeLocalString(), //"xs:dateTime"/>
            'Itn' => '', //"xs:string"/>
        ];
    }

    public static function convertToGrandFormat($orionPerson)
    {
        $pass = new \stdClass();
        foreach (self::$convertor as $key => $value) {
            $pass->$key = $orionPerson->$value;
        }
        return $pass;
    }

    public static function getPhoto($request, $soap)
    {
        if (empty($request->file('photo')) &&  !empty($request->orion_photo)) {
            $personOrion = $soap->getPerson($request->orion_id);
            return $personOrion->Photo;

        } else if (empty($request->file('photo')) &&  !empty($request->restore_photo)){
            $file = $request->restore_photo;
            $path = Storage::url($file);
            $photo = file_get_contents($path);
            return $photo;

        } else if (!empty($request->file('photo'))) {
            $file = $request->file('photo');
            $path = $file->getRealPath();
            $photo = file_get_contents($path);
            return $photo;
        }

        return false;
    }

    public function companies()
    {
        return $this->belongsTo(CompanyOrion::class,'Company','ID')
            ->withDefault($this->defaultName);
    }

    public function departments()
    {
        return $this->belongsTo(DepartmentOrion::class,'Section','ID')
            ->withDefault($this->defaultName);
    }

    public function positions()
    {
        return $this->belongsTo(PositionOrion::class,'Post','ID')
            ->withDefault($this->defaultName);
    }
}
