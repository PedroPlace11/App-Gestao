<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return view('app');
    }

    return view('welcome');
});

Route::redirect('/dashboard', '/');
Route::redirect('/clientes', '/');

// SPA — todas as rotas autenticadas servem a app Vue
Route::middleware(['auth'])->group(function () {
    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '(?!api|login|register|logout|password|email|two-factor).*');
});
