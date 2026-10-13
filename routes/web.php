<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'user' => [
            'name' => 'Pelajar Ilmora',
            'email' => 'student@ilmora.id',
        ],
        'courses' => [
            [
                'id' => 1,
                'title' => 'Dasar-dasar Pemrograman',
                'level' => 'Menengah',
                'progress' => 75,
                'price' => 'Rp249.000',
                'category' => 'Pemrograman',
                'thumbnail' => '/placeholder.svg?height=300&width=500',
                'reviewCount' => 42,
            ],
            [
                'id' => 2,
                'title' => 'Desain UI/UX Modern',
                'level' => 'Dasar',
                'progress' => 40,
                'price' => 'Rp199.000',
                'category' => 'Desain',
                'thumbnail' => '/placeholder.svg?height=300&width=500',
                'reviewCount' => 31,
            ],
            [
                'id' => 3,
                'title' => 'Pemasaran Digital',
                'level' => 'Lanjutan',
                'progress' => 20,
                'price' => 'Rp349.000',
                'category' => 'Pemasaran',
                'thumbnail' => '/placeholder.svg?height=300&width=500',
                'reviewCount' => 18,
            ],
        ],
    ]);
});

// Dummy routes - placeholder untuk development masa depan
Route::get('/courses', function () {
    return view('dummy.courses');
});

Route::get('/profile', function () {
    return view('dummy.profile');
});

Route::get('/admin', function () {
    return view('dummy.admin');
});

Route::get('/settings', function () {
    return view('dummy.settings');
});

// 404 fallback
Route::get('/{any}', function () {
    abort(404);
})->where('any', '.*[^/]');
