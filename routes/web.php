<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TranslationController;




Route::get('/translate', [TranslationController::class, 'showForm'])->name('translate.form');
Route::post('/translate', [TranslationController::class, 'translate'])->name('translate');




