<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\OrganisationController;


Route::get('/', function () {
    return response()->json(['message' => 'Welcome to the API']);
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('users/{id}', [OrganisationController::class, 'getUser']);
    Route::get('organisations', [OrganisationController::class, 'getOrganisations']);
    Route::get('organisations/{orgId}', [OrganisationController::class, 'getOrganisation']);
    Route::post('organisations', [OrganisationController::class, 'createOrganisation']);
    Route::post('organisations/{orgId}/users', [OrganisationController::class, 'addUserToOrganisation']);
});
