<?php

use App\Http\Controllers\Api\UserController;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('verify', [UserController::class, 'verify']);
Route::post('login', [UserController::class, 'login']);

Route::group(['middleware' => ['auth:api', 'check_role:1'], 'prefix' => 'admin'], function () {
    Route::post('send-invitation', [UserController::class, 'sendInvitation']);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'user'], function () {
    Route::post('update-profile', [UserController::class, 'UpdateProfile']);
});
