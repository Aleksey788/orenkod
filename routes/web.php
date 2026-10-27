<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index_reg');
});

// Главная / Страница регистрации
Route::get('/register', function () {
    return view('index_reg');
});

// Личный кабинет спортсмена
Route::get('/athlete', function () {
    return view('athete_lk');
});

// Личный кабинет тренера
Route::get('/coach', function () {
    return view('coach_lk');
});

// Личный кабинет судьи
Route::get('/judge', function () {
    return view('judge_lk');
});

// Кабинет члена федерации
Route::get('/federation', function () {
    return view('federation_member');
});