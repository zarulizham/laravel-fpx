<?php

use ZarulIzham\Fpx\Fpx;
use Illuminate\Support\Facades\Route;
use ZarulIzham\Fpx\Http\Controllers\PaymentController;

Route::get('banks', [PaymentController::class, 'banks'])->name('api.banks.index');
