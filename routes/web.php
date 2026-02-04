<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| API-first application - Web routes are minimal.
| Most functionality should be in api.php
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Products API',
        'version' => '1.0.0',
        'status' => 'running',
    ]);
});
