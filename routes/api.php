<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AssocieController;
use App\Http\Controllers\Api\AssocieMouvementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepenseController;
use App\Http\Controllers\Api\RecetteController;
use App\Http\Controllers\Api\FormationController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\Api\PreuveController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rôles :
|  - superadmin : tout + gestion des admins et comptables
|  - admin      : CRUD des opérations + gestion des comptables
|  - accountant : consultation, téléchargement des preuves et export uniquement
|
*/

// Pas de route d'inscription : les comptes sont créés par un superadmin / admin.
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/password', [AuthController::class, 'updatePassword']);

    // Lecture : tous les rôles
    Route::get('/depenses/export', [DepenseController::class, 'export']);
    Route::get('/depenses/options', [DepenseController::class, 'options']);
    Route::apiResource('depenses', DepenseController::class)->only(['index', 'show']);

    Route::get('/recettes/export', [RecetteController::class, 'export']);
    Route::get('/recettes/options', [RecetteController::class, 'options']);
    Route::apiResource('recettes', RecetteController::class)->only(['index', 'show']);

    Route::get('/preuves/{preuve}/download', [PreuveController::class, 'download']);

    // Comptes courants d'associés
    Route::get('/associes', [AssocieController::class, 'index']);
    Route::get('/associes/{associe}', [AssocieController::class, 'show']);
    Route::get('/associes/{associe}/mouvements', [AssocieMouvementController::class, 'index']);
    Route::get('/associes/{associe}/mouvements/export', [AssocieMouvementController::class, 'export']);
    Route::get('/mouvements-associes/{mouvement}', [AssocieMouvementController::class, 'show']);

    Route::apiResource('formations', FormationController::class)->only(['index', 'show']);
    Route::apiResource('events', EventsController::class)->only(['index', 'show']);
    Route::get('/statistics', [StatisticsController::class, 'index']);

    // Écriture : superadmin et admin
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('depenses', DepenseController::class)->only(['store', 'update', 'destroy']);
        Route::post('/depenses/{depense}/preuves', [PreuveController::class, 'storeForDepense']);

        Route::apiResource('recettes', RecetteController::class)->only(['store', 'update', 'destroy']);
        Route::post('/recettes/{recette}/preuves', [PreuveController::class, 'storeForRecette']);

        Route::delete('/preuves/{preuve}', [PreuveController::class, 'destroy']);

        Route::post('/associes', [AssocieController::class, 'store']);
        Route::put('/associes/{associe}', [AssocieController::class, 'update']);
        Route::delete('/associes/{associe}', [AssocieController::class, 'destroy']);
        Route::post('/associes/{associe}/mouvements', [AssocieMouvementController::class, 'store']);
        Route::put('/mouvements-associes/{mouvement}', [AssocieMouvementController::class, 'update']);
        Route::delete('/mouvements-associes/{mouvement}', [AssocieMouvementController::class, 'destroy']);
        Route::post('/mouvements-associes/{mouvement}/preuves', [PreuveController::class, 'storeForMouvement']);

        Route::apiResource('formations', FormationController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('events', EventsController::class)->only(['store', 'update', 'destroy']);

        // Gestion des utilisateurs (les droits fins sont dans UserController)
        Route::apiResource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

Route::get('/api/documentation', function () {
    return view('l5-swagger::index');
});
