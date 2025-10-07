<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);

Route::group(['middleware' => 'auth:api'], function() {
    // Route::get('/me', function (Request $request) {
    //     return $request->user();
    // });

    Route::get('/me', [App\Http\Controllers\AuthController::class, 'me']);

    /** Hospitals */
    Route::get('/hospitals', [App\Http\Controllers\HospitalController::class, 'getHospitals']);

    /** Checkins */
    Route::get('/checkins', [App\Http\Controllers\CheckinController::class, 'getCheckins']);
    Route::get('/checkins/count', [App\Http\Controllers\CheckinController::class, 'getCount']);
    Route::get('/checkins/init/form', [App\Http\Controllers\CheckinController::class, 'getInitialFormData']);
    Route::get('/checkins/{sdate}/{edate}/changwats', [App\Http\Controllers\CheckinController::class, 'getCountWithChangwats']);
    Route::get('/checkins/{sdate}/{edate}/{changwat}/amphurs', [App\Http\Controllers\CheckinController::class, 'getCountWithAmphurs']);
    Route::get('/checkins/{sdate}/{edate}/{changwat}/{amphurs}/tambons', [App\Http\Controllers\CheckinController::class, 'getCountWithTambons']);
});

/** ## Using Client Credentials Grant */
Route::get('/changwats', [App\Http\Controllers\HospitalController::class, 'getHospitals'])->middleware('client');

/** Check db connection */
Route::get('/db-connection', function () {
    try {
        $dbconnect = \DB::connection()->getPDO();
        $dbname = \DB::connection()->getDatabaseName();

        echo "Connected successfully to the database. Database name is :".$dbname;
    } catch(Exception $e) {
        echo $e->getMessage();
    }
});