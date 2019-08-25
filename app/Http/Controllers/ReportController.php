<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\PassOrion;
use App\PersonOrion;
use App\Service\Helper;
use Carbon\Carbon;
use Jenssegers\Date\Date;

class ReportController extends Controller
{
    public function index()
    {
        $dateBegin = Carbon::now('Europe/Moscow')->subDays(7)->format('Y-m-d');
        $dateEnd = Carbon::now('Europe/Moscow')->format('Y-m-d');

        return view('report', compact('dateBegin','dateEnd'));
    }

    public function show(ReportRequest $request)
    {
        $dateBegin = $request->date_begin;
        $dateEnd = $request->date_end;

        $dateHumanBegin = Date::parse($dateBegin)->format('j F Y');
        $dateHumanEnd = Date::parse($dateEnd)->format('j F Y');

        $persons = $this->getOverduePasses($dateBegin, $dateEnd);

        return view('report', compact(
            'persons',
            'dateHumanBegin',
            'dateHumanEnd',
            'dateBegin',
            'dateEnd'
        ));
    }

    public function getOverduePasses($date_begin, $date_end)
    {
        $dateBeginSQL = Helper::orionDateFormat($date_begin);
        $dateEndSQL = Helper::orionDateFormat($date_end);

        $passes = PassOrion::select('Owner', 'Finish')->whereBetween('Finish', [$dateBeginSQL, $dateEndSQL])->get();
        $owners = [];
        $passFinish =[];
        foreach ($passes as $pass) {
            $owners[] = $pass->Owner;
            $passFinish[$pass->Owner] = $pass->Finish;
        }

        $persons = PersonOrion::whereIn('ID', $owners)->with('companies')->get();

        foreach ($persons as $person) {
            $person->photoBlob = Helper::photoBlobToImage($person->Picture);
            $person->Finish = Date::parse($passFinish[$person->ID])->format('j F Y');
        }

        return $persons;
    }
}
