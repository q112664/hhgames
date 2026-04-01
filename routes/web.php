<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('resources', [ResourceController::class, 'index'])->name('resources.index');
Route::get('resources/{resource:slug}', [ResourceController::class, 'show'])->name('resources.show');
Route::get('resources/{resource:slug}/download/{entry}', [ResourceController::class, 'download'])->name('resources.download');
Route::get('resources/{resource:slug}/files/create', [ResourceFileController::class, 'create'])
    ->middleware('auth')
    ->name('resources.files.create');
Route::get('resources/{resource:slug}/files', [ResourceController::class, 'files'])->name('resources.files');
Route::get('resources/{resource:slug}/files/{entry}/edit', [ResourceFileController::class, 'edit'])
    ->middleware('auth')
    ->name('resources.files.edit');
Route::post('resources/{resource:slug}/files', [ResourceFileController::class, 'store'])
    ->middleware('auth')
    ->name('resources.files.store');
Route::patch('resources/{resource:slug}/files/{entry}', [ResourceFileController::class, 'update'])
    ->middleware('auth')
    ->name('resources.files.update');
Route::delete('resources/{resource:slug}/files/{entry}', [ResourceFileController::class, 'destroy'])
    ->middleware('auth')
    ->name('resources.files.destroy');
Route::get('resources/{resource:slug}/screenshots', [ResourceController::class, 'screenshots'])->name('resources.screenshots');
Route::get('resources/{resource:slug}/comments', [ResourceController::class, 'comments'])->name('resources.comments');
Route::post('resources/{resource:slug}/favorite', [ResourceController::class, 'favorite'])
    ->middleware('auth')
    ->name('resources.favorite');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/settings/profile')->name('dashboard');
});

require __DIR__.'/settings.php';
