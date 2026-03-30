<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('resources', [ResourceController::class, 'index'])->name('resources.index');
Route::get('resources/{resource:slug}', [ResourceController::class, 'show'])->name('resources.show');
Route::get('resources/{resource:slug}/download/{entry}', [ResourceController::class, 'download'])->name('resources.download');
Route::get('resources/{resource:slug}/files', [ResourceController::class, 'files'])->name('resources.files');
Route::get('resources/{resource:slug}/screenshots', [ResourceController::class, 'screenshots'])->name('resources.screenshots');
Route::get('resources/{resource:slug}/comments', [ResourceController::class, 'comments'])->name('resources.comments');
Route::post('resources/{resource:slug}/favorite', [ResourceController::class, 'favorite'])
    ->middleware('auth')
    ->name('resources.favorite');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/settings/profile')->name('dashboard');
});

require __DIR__.'/settings.php';
