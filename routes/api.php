<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user_registration', [UserController::class, 'userRegistration']);
Route::post('/user_login', [UserController::class, 'userLogin']);
Route::get('/purchase/{client_token}/{receipt}/{expire_date?}', [UserController::class, 'purchaseRequest']);
Route::get('/logout_device/{client_token}', [UserController::class, 'logoutDevice']);
Route::get('/check_user_subscription/{client_token}', [UserController::class, 'checkUserSubscription']);