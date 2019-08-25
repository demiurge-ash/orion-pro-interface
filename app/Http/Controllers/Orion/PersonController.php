<?php

namespace App\Http\Controllers\Orion;


use App\Http\Controllers\SoapController;
use App\PersonOrion;
use App\Service\Sanitize;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PersonController
{
    public function index()
    {
        return view('passes');
    }

    public function ajax(Request $request)
    {
        $model = PersonOrion::with('companies', 'departments', 'positions')->select('pList.*');
        return DataTables::eloquent($model)->make(true);
    }

    public function delete($id, $tabnumber)
    {
        Sanitize::numberOrError($id);
        Sanitize::numberOrError($tabnumber);

        $soap = new SoapController();
        $soap->checkConnection();

        $soap->deletePerson($id);

        return redirect("/edit/{$tabnumber}")->with('status', 'Пропуск удалён!');
    }

}