<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [UserController::class,'login']);
Route::post('postLogin', [UserController::class,'postLogin']);
Route::get('register',[UserController::class,'register']);
Route::post('store',[UserController::class,'store']);
Route::post('logout',[UserController::class,'logout']);

Route::group(['middleware' => ['auth']], function() {
    Route::get('dashboard',[UserController::class,'index'])->name('dashboard');
    Route::get('allUsers',[UserController::class,'allUsers']);
    Route::post('editUser',[UserController::class,'editUser']);
    Route::post('update',[UserController::class,'update']);
    Route::post('deleteUser',[UserController::class,'delete']);
});