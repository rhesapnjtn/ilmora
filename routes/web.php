<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Vue SPA shell)
|--------------------------------------------------------------------------
|
| The frontend is a Vue single-page application. Every non-API route returns
| the SPA shell so client-side routing (vue-router) can take over.
|
*/

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!up$|storage|api).*')->name('spa');
