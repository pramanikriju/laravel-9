<?php

use App\Http\Controllers\EmailController;
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
//API route to send emails
Route::middleware('auth:sanctum')->post('/{user}/send', [EmailController::class,'send']);

//Define API route to get tokens
Route::post('/sanctum/token', [EmailController::class,'getToken']);

