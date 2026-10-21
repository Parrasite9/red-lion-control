<?php

use App\Http\Controllers\ValveController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('valve');
});

Route::post('/submit', [ValveController::class, 'submit'])
    ->name('valve.submit');
