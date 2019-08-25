<?php

namespace App\Http\Controllers;

use App\Http\Requests\PassRequest;
use App\Pass;
use App\PassColor;
use App\PersonOrion;
use App\Service\CardID;
use App\Service\Helper;
use App\Service\JsonResponse;
use App\Service\Sanitize;
use App\Status;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $soap = new SoapController();
        $soap->checkConnection();

        $organizations = $soap->getCompanies();
        $departments = $soap->getDepartments();
        $positions = $soap->getPositions();

        return view('employees', compact('organizations', 'departments', 'positions'));
    }

    public function ajax(Request $request)
    {
        $model = Pass::query();

        if(intval($request->company) > 0) $model->whereOrganization($request->company);
        if(intval($request->department) > 0) $model->whereDepartment($request->department);
        if(intval($request->position) > 0) $model->wherePosition($request->position);

        return DataTables::eloquent($model)->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $soap = new SoapController();
        $soap->checkConnection();

        // from MySQL
        $passColors = PassColor::get();
        $statuses = Status::get();

        // MsSQL pCompany
        $organizations = $soap->getCompanies();
        // MsSQL pDivision
        $departments = $soap->getDepartments();
        // MsSQL pPost
        $positions = $soap->getPositions();
        // MsSQL Groups
        $groups = $soap->getAccessLevels();

        return view('create', compact(
            'passColors',
            'groups',
            'statuses',
            'organizations',
            'departments',
            'positions'
        ));
    }

    public function success()
    {
        $resultText = 'Операция завершена успешно!';
        return view('result', compact('resultText'));
    }

    public function successUpdate($id)
    {
        return redirect("/edit/{$id}")->with('status', 'Информация обновлена!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Pass  $pass
     * @return \Illuminate\Http\Response
     */
    public function show(Pass $pass)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $soap = new SoapController();
        $soap->checkConnection();

        $grandPerson = Pass::whereId($id)->firstOrFail();
        $orionPerson = PersonOrion::where('TabNumber',$grandPerson->id)->first();

        if (empty($orionPerson->ID)) {
            $pass = $grandPerson;
            $card =[];
        }else{
            $pass = PersonOrion::convertToGrandFormat($orionPerson);
            $pass->photoBlob = Helper::photoBlobToImage($orionPerson->Picture);
            $pass = Pass::getPassDataForEdit($pass, $orionPerson->TabNumber);
            $card = $soap->getCardsByPersonId($orionPerson->ID);
            $pass->orionID = $orionPerson->ID;
            $pass->id = $grandPerson->id;
        }

        // from MySQL
        $passColors = PassColor::get();
        $statuses = Status::get();

        // MsSQL pCompany
        $organizations = $soap->getCompanies();
        // MsSQL pDivision
        $departments = $soap->getDepartments();
        // MsSQL pPost
        $positions = $soap->getPositions();
        // MsSQL Groups
        $groups = $soap->getAccessLevels();

        return view('edit', compact('pass',
            'passColors',
            'passColors',
            'statuses',
            'organizations',
            'departments',
            'positions',
            'groups',
            'card'
        ));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PassRequest $request, Guard $auth)
    {
        $soap = new SoapController();
        $soap->checkConnection();

        $data = $request->all();
        $data['created_by'] = $auth->user()->id;
        $data['photo'] = Helper::savePhoto($request);

        $new = Pass::find($request->id);

        $new->update($data);

        $new->orionPhoto = PersonOrion::getPhoto($request, $soap);

        if ($request->orion_id || $request->card_id) {
            $personInfo = PersonOrion::personOrionInfo($new, $request);
            $personInfo['Id'] = $request->orion_id;
            $new->orion_id = $request->orion_id;
            $new->current_card_id = $request->current_card_id;
            $soap->updateOrion($personInfo, $new);
        }

        return JsonResponse::successOrError($soap->error);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PassRequest $request, Guard $auth)
    {
        $soap = new SoapController();
        $soap->checkConnection();

        $data = $request->all();
        $data['created_by'] = $auth->user()->id;
        $data['photo'] = Helper::savePhoto($request);

        $new = new Pass($data);
        $new->save();

        if(CardID::isNew($new)) {
            $personInfo = PersonOrion::personOrionInfo($new, $request);
            $soap->putCard($new, $personInfo);
        }

        return JsonResponse::successOrError($soap->error, $new->id);
    }

    public function delete($id, $orionnumber)
    {
        Sanitize::numberOrError($id);
        Sanitize::numberOrError($orionnumber);

        if ($orionnumber > 0)
        {
            $soap = new SoapController();
            $soap->checkConnection();

            $soap->deletePerson($orionnumber);
        }

        Pass::findOrFail($id)->delete();

        return redirect("/")->with('status', 'Сотрудник удалён!');
    }

}
