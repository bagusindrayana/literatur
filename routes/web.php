<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TranslateBookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::resource('book', BookController::class);
Route::get('book/{id}/pdf', [BookController::class, 'pdf'])->name('book.pdf');
Route::get('book/{id}/{content}', [BookController::class, 'content'])->name('book.content');
Route::resource('translate-book', TranslateBookController::class);
Route::post('translate-book/translate-content/{translateBookId}/{pageIndex}/{content}', [TranslateBookController::class, 'translateContent'])->name('translate-book.translate-content');
Route::post('translate-book/save-content/{translateBookId}', [TranslateBookController::class, 'saveChanges'])->name('translate-book.save-content');

