<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('postLogin', [UserController::class,'postLogin']);
Route::post('store',[UserController::class,'store']);
Route::post('logout',[UserController::class,'logout']);

Route::group(['middleware' => ['auth:api']], function() {
    Route::get('dashboard',[UserController::class,'index'])->name('dashboard');
    Route::get('allUsers',[UserController::class,'allUsers']);
    Route::post('editUser',[UserController::class,'editUser']);
    Route::post('update',[UserController::class,'update']);
    Route::post('deleteUser',[UserController::class,'delete']);
});