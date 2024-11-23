<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimentController;

Route::get('/', [SentimentController::class, 'index'])->name('home');
Route::get('/analyze', [SentimentController::class, 'create'])->name('analyze');
Route::post('/analyze', [SentimentController::class, 'store'])->name('store');
Route::get('/history', [SentimentController::class, 'history'])->name('history');
Route::post('/delete/{id}', [SentimentController::class, 'softDelete'])->name('softDelete');
Route::get('/report/{id}', [SentimentController::class, 'generateReport'])->name('generateReport');

