<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SolutionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/solutions', [SolutionController::class, 'index'])->name('solutions.index');
Route::get('/solutions/{solution:slug}', [SolutionController::class, 'show'])->name('solutions.show');

Route::get('/prestations', [ServiceController::class, 'index'])->name('services.index');
Route::get('/prestations/{category:slug}', [ServiceController::class, 'category'])->name('services.category');
Route::get('/prestation/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/references', [ReferenceController::class, 'index'])->name('references.index');

Route::get('/actualites', [PostController::class, 'index'])->name('posts.index');
Route::get('/actualites/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/a-propos', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/devis', [PageController::class, 'quote'])->name('quote');
Route::get('/mentions-legales', [PageController::class, 'legal'])->name('legal');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
