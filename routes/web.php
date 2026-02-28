<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\FPX\Controller;
use ZarulIzham\Fpx\Http\Controllers\PaymentController;


$directPath = Config::get('fpx.direct_path');
$indirectPath = Config::get('fpx.indirect_path');

Route::post('fpx/payment/auth', [PaymentController::class, 'handle'])->name('fpx.payment.auth.request');

Route::post($directPath, [Controller::class, 'direct'])->name('fpx.payment.direct');
Route::post($indirectPath, [Controller::class, 'indirect'])->name('fpx.payment.indirect');

if (config('app.env') != 'production') {
	Route::match(
		['get', 'post'],
		'fpx/initiate/payment/{iniated_from?}/{test?}',
		[Controller::class, 'initiatePayment']
	)->name('fpx.initiate.payment');

	Route::get(
		'fpx/csr/request',
		function () {
			return view('laravel-fpx::csr_request');
		}
	)->name('fpx.csr.request');
}
