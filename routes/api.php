<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\api\CarsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/test1', function () {
    return 'salom';
});
Route::post('register',[AuthController::class,'register']);
Route::post('cars/add',[CarsController::class,'add']);
Route::get('cars',[CarsController::class,'get']);
Route::get('cars/{id}',[CarsController::class,'get']);
Route::post('cars/edit/{id}',[CarsController::class,'edit']);
Route::post('cars/delete/{id}',[CarsController::class,'delete']);
Route::post('login',[AuthController::class,'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user',[AuthController::class,'user']);
    Route::post('user/delete',[AuthController::class,'deleteaccount']);
    Route::post('user/edit/',[AuthController::class,'useredit']);
    Route::post('user/password-change/',[AuthController::class,'passwordChange']);
    Route::post('logout',[AuthController::class,'logout']);
});
