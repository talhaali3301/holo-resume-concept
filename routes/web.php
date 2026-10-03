<?php

use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

$slug = '[a-z0-9]+(?:-[a-z0-9]+)*';

Route::get('/', [PortfolioController::class, 'lobby'])->name('lobby');
Route::get('/projects', [PortfolioController::class, 'projects'])->name('projects.index');
Route::get('/projects/{project}', [PortfolioController::class, 'projects'])->where('project', $slug)->name('projects.show');
Route::get('/skills', [PortfolioController::class, 'skills'])->name('skills.index');
Route::get('/skills/{skill}', [PortfolioController::class, 'skills'])->where('skill', $slug)->name('skills.show');
Route::get('/sitemap.xml', [PortfolioController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PortfolioController::class, 'robots'])->name('robots');
