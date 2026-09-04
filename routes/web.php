<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    
    // Read-only Routes (Owner & Guest bisa akses)
    Route::get('/files', [FileManagerController::class, 'index'])->name('files.index');

    // Owner-Only Action Routes (Guest diblokir via middleware 'is_owner')
    Route::middleware(['is_owner'])->group(function () {
        Route::post('/files/mkdir', [FileManagerController::class, 'createFolder'])->name('files.mkdir');
        Route::post('/files/delete', [FileManagerController::class, 'deleteItem'])->name('files.delete');
    });

});
