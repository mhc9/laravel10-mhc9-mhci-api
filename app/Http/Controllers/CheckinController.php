<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JfBiswajit\PHPBigQuery\Facades\BigQuery;

class CheckinController extends Controller
{
    /** #API GET /checkins */
    public function getCheckins()
    {
        $sql = 'SELECT * FROM `ecommerce-3ab6c.Covid.CheckIn`
                WHERE province_id IN (19, 20, 21, 25)
                ORDER By id DESC LIMIT 10';
        $jobConfig = BigQuery::query($sql);
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $data = [];
        foreach ($rows as $row) {
            array_push($data, $row);
        }

        return response()->json($data);
    }

    /** #API GET /checkins/count */
    public function getCount()
    {
        $sql = 'SELECT COUNT(id) as num FROM `ecommerce-3ab6c.Covid.CheckIn` WHERE province_id IN (19, 20, 21, 25)';
        $jobConfig = BigQuery::query($sql);
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $data = [];
        foreach ($rows as $row) {
            array_push($data, $row);
        }

        return response()->json($data);
    }
}
