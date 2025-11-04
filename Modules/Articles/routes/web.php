<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\Http\Controllers\ArticlesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('articles', ArticlesController::class)->names('articles');
});
