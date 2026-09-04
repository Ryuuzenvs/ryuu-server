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
    
    // READ ROUTES (Bisa diakses Owner & Guest)
    Route::get('/files', [FileManagerController::class, 'index'])->name('files.index');
    Route::get('/files/editor', [FileManagerController::class, 'editFile'])->name('files.editor');

    // OWNER ONLY ROUTES (Dikunci Middleware 'is_owner')
    Route::middleware(['is_owner'])->group(function () {
        Route::post('/files/mkdir', [FileManagerController::class, 'createFolder'])->name('files.mkdir');
        Route::post('/files/touch', [FileManagerController::class, 'createFile'])->name('files.touch');
        Route::post('/files/rename', [FileManagerController::class, 'renameItem'])->name('files.rename');
        Route::post('/files/delete', [FileManagerController::class, 'deleteItem'])->name('files.delete');
        Route::post('/files/save', [FileManagerController::class, 'saveFile'])->name('files.save');
    });

});
