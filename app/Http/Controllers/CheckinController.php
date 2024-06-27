<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JfBiswajit\PHPBigQuery\Facades\BigQuery;

class CheckinController extends Controller
{
    /** #API GET /checkins */
    public function getCheckins(Request $request)
    {
        $perPage = 10;
        $page = $request->get('page') ? $request->get('page') : 1;
        $offset = ($page - 1) * $perPage;
        $limit = $perPage;
        $sdate = $request->get('sdate');
        $edate = $request->get('edate');

        $sql = "SELECT *, 
                FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) as reg_date,
                FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', date_create_trace) as trace_date
                FROM `ecommerce-3ab6c.Covid.CheckIn`
                WHERE (FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) BETWEEN '$sdate' AND '$edate')
                AND (name_province LIKE '%นครราชสีมา%')
                ORDER By id DESC
                LIMIT " .$limit. " OFFSET " .$offset;
                // WHERE (province_id IN (19, 20, 21, 25))
                // AND (date_create_trace IS NOT NULL)

        $jobConfig = BigQuery::query($sql);
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $total = $this->count($sdate, $edate)[0]['num'];
        $data = [];
        foreach ($rows as $row) {
            array_push($data, $row);
        }

        return response()->json([
            "currentPage"   => $page,
            "data"          => $data,
            "total"         => $total,
            "from"          => $offset + 1,
            "to"            => $offset + $perPage,
            "lastPage"      => ceil($total / $perPage)
        ]);
    }

    /** #API GET /checkins/count */
    public function getCount()
    {
        $sdate = $request->filled('sdate') ? $request->get('sdate') : '2024-06-01';
        $edate = $request->filled('edate') ? $request->get('edate') : '2024-06-30';

        return response()->json($this->count($sdate, $edate));
    }

    private function count($sdate, $edate)
    {
        $sql = "SELECT COUNT(id) as num
                FROM `ecommerce-3ab6c.Covid.CheckIn`
                WHERE (FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) BETWEEN '$sdate' AND '$edate')
                AND (name_province LIKE '%นครราชสีมา%')";
                // WHERE province_id IN (19, 20, 21, 25)
                // AND (date_create_trace IS NOT NULL)";

        $jobConfig = BigQuery::query($sql);
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $data = [];
        foreach ($rows as $row) {
            array_push($data, $row);
        }

        return $data;
    }
}
