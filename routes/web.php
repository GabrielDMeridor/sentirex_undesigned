<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimentController;

Route::get('/', [SentimentController::class, 'index'])->name('home');
Route::get('/analyze', [SentimentController::class, 'create'])->name('analyze');
Route::post('/analyze', [SentimentController::class, 'store'])->name('store');
Route::get('/history', [SentimentController::class, 'history'])->name('history');
Route::post('/delete/{id}', [SentimentController::class, 'softDelete'])->name('softDelete');
Route::get('/report/{id}', [SentimentController::class, 'generateReport'])->name('generateReport');
Route::get('/report/{id}/download', [SentimentController::class, 'downloadReport'])->name('downloadReport');
Route::post('/export', [SentimentController::class, 'export'])->name('export');
Route::post('/set-theme', [ThemeController::class, 'setTheme'])->name('theme.set');

// Settings routes
Route::get('/settings', [SentimentController::class, 'settings'])->name('settings');
Route::post('/settings', [SentimentController::class, 'updateSettings'])->name('settings.update');

// Auth route
Route::post('/logout', [SentimentController::class, 'logout'])->name('logout');