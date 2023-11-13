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


Route::post("register",[\App\Http\Controllers\AuthController::class,"register"]);
Route::post("login",[\App\Http\Controllers\AuthController::class,"login"]);

Route::middleware('auth:sanctum')->group(function() {
	Route::post("user",[\App\Http\Controllers\AuthController::class,"user"]);
	Route::post("logout",[\App\Http\Controllers\AuthController::class,"logout"]);

	Route::get("vehicle/get",[\App\Http\Controllers\VehicleController::class,"get_data"]);
	Route::get("vehicle/get/{vehicle_id}",[\App\Http\Controllers\VehicleController::class,"get_by_id"]);
	Route::post("vehicle/insert",[\App\Http\Controllers\VehicleController::class,"insert"]);
	Route::post("vehicle/update/{vehicle_id}",[\App\Http\Controllers\VehicleController::class,"update"]);
	Route::post("vehicle/delete/{vehicle_id}",[\App\Http\Controllers\VehicleController::class,"delete"]);

	Route::get("fuel/get/{vehicle_id}",[\App\Http\Controllers\FuelLogsController::class,"get_data"]);
	Route::post("fuel/insert/{vehicle_id}",[\App\Http\Controllers\FuelLogsController::class,"insert"]);
	Route::post("fuel/update/{fuel_id}",[\App\Http\Controllers\FuelLogsController::class,"update"]);
	Route::post("fuel/delete/{fuel_id}",[\App\Http\Controllers\FuelLogsController::class,"delete"]);

	Route::get("maintenance/get/{vehicle_id}",[\App\Http\Controllers\MaintenanceController::class,"get_data"]);
	Route::get("maintenance/get_by_id/{maintenance_id}",[\App\Http\Controllers\MaintenanceController::class,"get_by_id"]);
	Route::get("maintenance/reminder/{vehicle_id}",[\App\Http\Controllers\MaintenanceController::class,"get_reminder"]);
	Route::post("maintenance/insert/{vehicle_id}",[\App\Http\Controllers\MaintenanceController::class,"insert"]);
	Route::post("maintenance/update/{maintenance_id}",[\App\Http\Controllers\MaintenanceController::class,"update"]);
	Route::post("maintenance/delete/{maintenance_id}",[\App\Http\Controllers\MaintenanceController::class,"delete"]);
});