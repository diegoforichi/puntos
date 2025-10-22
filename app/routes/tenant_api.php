<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteApiController;

Route::get('/clientes/{documento}', [ClienteApiController::class, 'show']);
Route::post('/clientes/{documento}/canjes', [ClienteApiController::class, 'canjear']);

