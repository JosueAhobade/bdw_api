<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepenseController;
use App\Http\Controllers\Api\RecetteController;
use App\Http\Controllers\Api\FormationController;


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

Route::apiResource('depenses', DepenseController::class);

Route::apiResource('recettes', RecetteController::class);

Route::apiResource('formations', FormationController::class);

Route::get('/api/documentation', function () {
    return view('l5-swagger::index');
});


