<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MapController;
use App\Http\Controllers\API\RestaurantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// register and login
Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});
        
Route::middleware('auth:sanctum')->group( function () {
    // logout
    Route::post('logout', [AuthController::class, 'logout']);

    Route::resource('restaurants', RestaurantController::class);
});

// google map
Route::controller(MapController::class)->group(function(){
    Route::post('/get-distance', 'getDistance');
    Route::post('/get-nearby', 'getNearby')->name('get-nearby');
    Route::get('/search-places/{q}', 'searchPlaces')->name('search-places');
});
