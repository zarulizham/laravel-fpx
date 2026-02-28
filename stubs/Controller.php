<?php

namespace App\Http\Controllers\FPX;

use ZarulIzham\Fpx\Http\Requests\AuthorizationConfirmation as Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use ZarulIzham\Fpx\Fpx;

class Controller extends BaseController
{

	/**
	 * Direct callback URL for FPX payment
	 */
	public function direct(Request $request)
	{
		$response = $request->handle();
		// Update your order status

		return 'OK';
	}

	/**
	 * Redirect URL after FPX payment
	 */
	public function indirect(Request $request)
	{
		$response = $request->handle();
		// Update your order status

		return redirect('some/url');
	}

	/**
	 * Initiate FPX payment
	 */
	public function initiatePayment(Request $request, $iniated_from = 'web')
	{
		$day = 1;
		$hour = 24;
		$minute = 60;
		$second = 60;

		$banks = Cache::remember('banks', $day * $hour * $minute * $second, function () {
			return Fpx::getBankList(true);
		});

		$response_format = $iniated_from == 'app' ? 'JSON' : 'HTML';

		return view('laravel-fpx::payment', compact('banks', 'response_format', 'request'));
	}
}
