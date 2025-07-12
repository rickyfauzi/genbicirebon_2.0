<?php

use App\Http\Controllers\Api\KataMutiaraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\WebhookController;

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

// //api untuk menampilkan kata mutiara
// Route::get('katamutiara', [KataMutiaraController::class,'index']);
// Route::get('katamutiara/{id}', [KataMutiaraController::class,'show']);
// Route::post('katamutiara', [KataMutiaraController::class,'store']);
// Route::put('katamutiara/{id}', [KataMutiaraController::class,'update']);
// Route::delete('katamutiara/{id}', [KataMutiaraController::class,'destroy']);

Route::apiResource('katamutiara', KataMutiaraController::class);

// Kalau pakai routes/api.php
Route::post('/webhook-dialogflow', [WebhookController::class, 'handle']);
