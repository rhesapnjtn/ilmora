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
| Compiled assets under /build are excluded so a request for a missing or
| stale chunk returns a real 404 instead of the SPA HTML (which would break
| strict module MIME-type checks in the browser).
|
*/

Route::get('/{any?}', function () {
    return response()
        ->view('app')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
})->where('any', '^(?!up$|storage|api|build|docs).*')->name('spa');
