<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JfBiswajit\PHPBigQuery\Facades\BigQuery;
use App\Models\Changwat;
use App\Models\Amphur;
use App\Models\Tambon;

class CheckinController extends Controller
{
    /** #API GET /checkins */
    public function getCheckins(Request $request)
    {
        $perPage = 10;
        $page = $request->get('page') ? $request->get('page') : 1;
        $offset = ($page - 1) * $perPage;
        $limit = $perPage;
        $changwat = $request->filled('changwat') ? explode('-', $request->get('changwat'))[1] : '';
        $amphur = $request->filled('amphur') ? explode('-', $request->get('amphur'))[1] : '';
        $tambon = $request->filled('tambon') ? explode('-', $request->get('tambon'))[1] : '';
        $sdate = $request->filled('sdate') ? $request->get('sdate') : date('Y-m').'-01';
        $edate = $request->filled('edate') ? $request->get('edate') : date('Y-m-t', strtotime($sdate));

        $sql = "SELECT *, 
                FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) as reg_date,
                FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', date_create_trace) as trace_date
                FROM `ecommerce-3ab6c.Covid.CheckIn`
                WHERE (FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) BETWEEN '$sdate' AND '$edate') ";
                // WHERE (province_id IN (19, 20, 21, 25))
                // AND (date_create_trace IS NOT NULL)

        $sql .= !empty($changwat) ? "AND (name_province LIKE '%$changwat%') " : "AND (name_province IN ('นครราชสีมา', 'บุรีรัมย์', 'สุรินทร์', 'ชัยภูมิ')) ";
        if (!empty($amphur)) { $sql .= "AND (name_amphure LIKE '%$amphur%') "; }
        if (!empty($tambon)) { $sql .= "AND (name_district LIKE '%$tambon%') "; }

        $sql .= "ORDER By id DESC LIMIT " .$limit. " OFFSET " .$offset;

        $jobConfig = BigQuery::query($sql);
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $total = $this->count($changwat, $amphur, $tambon, $sdate, $edate)[0]['num'];
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
    public function getCount(Request $request)
    {
        $changwat = $request->filled('changwat') ? explode('-', $request->get('changwat'))[1] : '';
        $amphur = $request->filled('amphur') ? explode('-', $request->get('amphur'))[1] : '';
        $tambon = $request->filled('tambon') ? explode('-', $request->get('tambon'))[1] : '';
        $sdate = $request->filled('sdate') ? $request->get('sdate') : date('Y-m').'-01';
        $edate = $request->filled('edate') ? $request->get('edate') : date('Y-m-t', strtotime($sdate));

        return response()->json($this->count($changwat, $amphur, $tambon, $sdate, $edate));
    }

    private function count($changwat, $amphur, $tambon, $sdate, $edate)
    {
        $sql = "SELECT COUNT(id) as num
                FROM `ecommerce-3ab6c.Covid.CheckIn`
                WHERE (FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) BETWEEN '$sdate' AND '$edate') ";
                // WHERE province_id IN (19, 20, 21, 25)
                // AND (date_create_trace IS NOT NULL)";

        $sql .= !empty($changwat) ? "AND (name_province LIKE '%$changwat%') " : "AND (name_province IN ('นครราชสีมา', 'บุรีรัมย์', 'สุรินทร์', 'ชัยภูมิ')) ";
        if (!empty($amphur)) { $sql .= "AND (name_amphure LIKE '%$amphur%') "; }
        if (!empty($tambon)) { $sql .= "AND (name_district LIKE '%$tambon%') "; }

        $jobConfig = BigQuery::query($sql);
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $data = [];
        foreach ($rows as $row) {
            array_push($data, $row);
        }

        return $data;
    }

    public function getInitialFormData()
    {
        return [
            'changwats'    => Changwat::whereIn('id', ['30', '31', '32', '36'])->get(),
            'amphurs'       => Amphur::all(),
            'tambons'       => Tambon::all()
        ];
    }

    public function getCountWithChangwats($sdate, $edate)
    {
        $sql = "SELECT name_province as area,
                COUNT(id) as total,
                COUNT(CASE WHEN (st_5 >= 8) THEN id END) as st5,
                COUNT(CASE WHEN (depression >= 7) THEN id END) as depression,
                COUNT(CASE WHEN (sucide >= 1) THEN id END) as sucide,
                COUNT(CASE WHEN (burnout1+burnout2+burnout3 >= 9) THEN id END) as burnout,
                COUNT(CASE WHEN (ok = '1') THEN id END) as helped
                FROM `ecommerce-3ab6c.Covid.CheckIn`
                WHERE (FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) BETWEEN '$sdate' AND '$edate')
                AND (name_province IN ('นครราชสีมา', 'บุรีรัมย์', 'สุรินทร์', 'ชัยภูมิ')) 
                GROUP BY name_province";

        $jobConfig = BigQuery::query($sql);
        $queryResults = BigQuery::runQuery($jobConfig);
        $rows = $queryResults->rows();

        $data = [];
        foreach ($rows as $row) {
            array_push($data, $row);
        }

        return $data;
    }

    public function getCountWithAmphurs($sdate, $edate, $province)
    {
        $sql = "SELECT name_province as province, name_amphure as area,
                COUNT(id) as total,
                COUNT(CASE WHEN (st_5 >= 8) THEN id END) as st5,
                COUNT(CASE WHEN (depression >= 7) THEN id END) as depression,
                COUNT(CASE WHEN (sucide >= 1) THEN id END) as sucide,
                COUNT(CASE WHEN (burnout1+burnout2+burnout3 >= 9) THEN id END) as burnout,
                COUNT(CASE WHEN (ok = '1') THEN id END) as helped
                FROM `ecommerce-3ab6c.Covid.CheckIn`
                WHERE (FORMAT_DATETIME('%Y-%m-%d %H:%M:%S', data_create) BETWEEN '$sdate' AND '$edate')
                AND (name_province = '$province') 
                GROUP BY name_province, name_amphure";

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
