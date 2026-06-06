<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/tokens', [DashboardController::class, 'generateToken'])->name('tokens.generate');
    Route::get('cards/export', [CardController::class, 'exportCsv'])->name('cards.export');
    Route::resource('cards', CardController::class)->except(['show']);
    Route::get('history', [ActivityLogController::class, 'index'])->name('history.index');
    Route::get('history/export', [ActivityLogController::class, 'exportCsv'])->name('history.export');
});

require __DIR__.'/settings.php';
