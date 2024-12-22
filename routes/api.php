<?php

//TODO temporary
error_reporting(E_ALL);
ini_set('display_errors', 1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\CampaignController;
//TODO temporary
use Illuminate\Support\Facades\DB;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('campaigns', CampaignController::class);
});

Route::get('/health', function () {
    return [
        'message' => 'Hello World',
    ];
});
