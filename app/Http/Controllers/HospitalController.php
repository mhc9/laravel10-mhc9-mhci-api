<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JfBiswajit\PHPBigQuery\Facades\BigQuery;

class HospitalController extends Controller
{
    public function index()
    {
        $jobConfig = BigQuery::query('SELECT * FROM `custom-frame-138223.simple_dataset.primary_care_hospital` LIMIT 10');
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $i = 0;
        $data = [];
        foreach ($rows as $row) {
            if ($i > 0) {
                array_push($data, $row);
            }

            $i++;
        }

        return view('hospitals.index', [
            "test" => "Hello World!!",
            "data" => $data
        ]);
    }

    /** #API GET /hospitals */
    public function getHospitals()
    {
        $jobConfig = BigQuery::query('SELECT * FROM `custom-frame-138223.simple_dataset.primary_care_hospital` LIMIT 10');
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $i = 0;
        $data = [];
        foreach ($rows as $row) {
            if ($i > 0) {
                array_push($data, $row);
            }

            $i++;
        }

        return response()->json($data);
    }
}
