<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TechnicianController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/dashboard', function () {
    return view('layouts.app');
});
