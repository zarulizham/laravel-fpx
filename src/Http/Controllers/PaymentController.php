<?php

namespace ZarulIzham\Fpx\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ZarulIzham\Fpx\Models\Bank;
use ZarulIzham\Fpx\Messages\AuthorizationRequest;

class PaymentController extends Controller
{

	/**
	 * Initiate the request authorization message to FPX
	 */
	public function handle(Request $request)
	{
		return view('fpx-payment::redirect_to_bank', [
			'request' => (new AuthorizationRequest)->handle($request->all()),
		]);
	}

	public function banks(Request $request)
	{
		$banks = Bank::query()->select('bank_id', 'name', 'short_name', 'status');

		if ($request->type) {
			$banks->type($request->type == '01' ? 'B2C' : 'B2B');
		}

		if ($request->name) {
			$banks->where('name', 'LIKE', "%$request->name%");
		}

		$banks = $banks->orderBy('position', 'ASC')->get();

		return response()->json([
			'banks' => $banks,
		], 200);
	}
}
