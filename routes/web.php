<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing');
// Evita RouteNotFoundException (route "login") quando middleware de auth tenta redirecionar guests.
Route::view('/app/login', 'app')->name('login');
Route::view('/app/{any?}', 'app')->where('any', '.*');
