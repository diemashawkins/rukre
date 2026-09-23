<?php

use App\Http\Controllers\AudioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VideoController;
use App\Http\Middleware\RequireLogin;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(RequireLogin::class)->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/search', SearchController::class)->name('search');

    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{item}', [VideoController::class, 'show'])->name('videos.show');

    Route::get('/audio', [AudioController::class, 'index'])->name('audio.index');
    Route::get('/audio/{item}', [AudioController::class, 'show'])->name('audio.show');

    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/{item}', [BookController::class, 'show'])->name('books.show');
    Route::get('/books/{item}/pages/{page}', [BookController::class, 'page'])->whereNumber('page')->name('books.page');

    Route::get('/media/{item}/stream', [MediaController::class, 'stream'])->name('media.stream');
    Route::get('/media/{item}/download', [MediaController::class, 'download'])->name('media.download');
    Route::get('/media/{item}/cover', [MediaController::class, 'cover'])->name('media.cover');
    Route::post('/media/{item}/progress', [MediaController::class, 'progress'])->name('media.progress');
});
