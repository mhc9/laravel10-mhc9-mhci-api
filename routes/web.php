<?php

use Illuminate\Support\Facades\Route;
use JfBiswajit\PHPBigQuery\Facades\BigQuery;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/big-query', function() {
    $bqConf = BigQuery::query('SELECT * FROM simple_dataset.primary_care_hospital');

    return view('bigquery', [
        "test" => "Hello World!!",
        "data" => $bigQuery
    ]);
});
