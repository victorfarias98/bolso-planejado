<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing');
Route::view('/app/{any?}', 'app')->where('any', '.*');
