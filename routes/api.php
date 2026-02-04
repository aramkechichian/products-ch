<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Legacy/Non-versioned)
|--------------------------------------------------------------------------
|
| These routes are kept for backward compatibility.
| New routes should be added to routes/api/v1.php
|
*/

// Redirect to v1 or keep legacy endpoints here
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
