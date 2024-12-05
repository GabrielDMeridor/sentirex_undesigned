<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimentController;
use App\Http\Controllers\ThemeController;

// Main routes
Route::get('/', [SentimentController::class, 'index'])->name('home');
Route::get('/analyze', [SentimentController::class, 'create'])->name('analyze');
Route::post('/analyze', [SentimentController::class, 'store'])->name('store');

// History and reports
Route::get('/history', [SentimentController::class, 'history'])->name('history');
Route::get('/report/{id}', [SentimentController::class, 'generateReport'])->name('report.generate');
Route::get('/report/{id}/download', [SentimentController::class, 'downloadReport'])->name('report.download');
Route::delete('/delete/{id}', [SentimentController::class, 'softDelete'])->name('softDelete');
Route::post('/export', [SentimentController::class, 'export'])->name('export');

// Theme and settings
Route::get('/settings', [SentimentController::class, 'settings'])->name('settings');
Route::post('/settings', [SentimentController::class, 'updateSettings'])->name('settings.update');
Route::post('/set-theme', [ThemeController::class, 'setTheme'])->name('theme.set');

// Auth
Route::post('/logout', [SentimentController::class, 'logout'])->name('logout');