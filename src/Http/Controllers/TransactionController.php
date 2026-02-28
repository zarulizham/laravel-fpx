<?php

namespace ZarulIzham\Fpx\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use ZarulIzham\Fpx\Fpx;
use ZarulIzham\Fpx\Models\FpxTransaction;

class TransactionController extends Controller
{
    /**
     * Display FPX transactions with pagination.
     */
    public function index(Request $request): View
    {
        abort_unless(Fpx::check($request), 403);

        $transactions = FpxTransaction::query()
            ->with('bank')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('laravel-fpx::transactions', compact('transactions'));
    }

    /**
     * Requery FPX status for a transaction.
     */
    public function requery(Request $request, FpxTransaction $transaction): RedirectResponse
    {
        abort_unless(Fpx::check($request), 403);

        $response = Fpx::getTransactionStatus($transaction->order_number, $transaction->exchange_order_number);

        return back()->with('status', [
            'type' => ($response['status'] ?? 'failed') === 'succeeded' ? 'success' : 'warning',
            'message' => $response['message'] ?? 'Unable to requery transaction status.',
            'order_number' => $transaction->order_number,
        ]);
    }
}
