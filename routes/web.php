<?php

use Illuminate\Support\Facades\Route;

// Главная / Страница регистрации
Route::get('/', function () {
    return view('index_reg');
});

Route::get('/register', function () {
    return view('index_reg');
});

// Личные кабинеты
Route::get('/athlete', function () {
    return view('athete_lk');
});

Route::get('/coach', function () {
    return view('coach_lk');
});

Route::get('/judge', function () {
    return view('judge_lk');
});

Route::get('/federation', function () {
    return view('federation_member');
});

// Дополнительные разделы платформы (документы, уведомления, рейтинг, реестр, лог безопасности)
Route::get('/documents', function () {
    return view('documents');
});

Route::get('/notifications', function () {
    return view('notifications');
});

Route::get('/rating-team', function () {
    return view('rating_team');
});

Route::get('/reester', function () {
    return view('reester');
});

Route::get('/security-log', function () {
    return view('security_log');
});