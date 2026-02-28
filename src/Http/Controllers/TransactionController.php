<?php

namespace ZarulIzham\Fpx\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

        return view('laravel-fpx::transactions');
    }

    /**
     * Return paginated transaction data for SPA.
     */
    public function data(Request $request): JsonResponse
    {
        abort_unless(Fpx::check($request), 403);

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $query = FpxTransaction::query()->with('bank');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('order_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        if ($status === 'success') {
            $query->where('debit_auth_code', '00');
        } elseif ($status === 'pending') {
            $query->where('debit_auth_code', '09');
        } elseif ($status === 'failed') {
            $query->where(function ($builder) {
                $builder->whereNull('debit_auth_code')
                    ->orWhereNotIn('debit_auth_code', ['00', '09']);
            });
        }

        $transactions = $query
            ->latest()
            ->paginate(15)
            ->through(fn (FpxTransaction $transaction) => $this->serializeTransaction($transaction));

        return response()->json($transactions);
    }

    /**
     * Requery FPX status for a transaction.
     */
    public function requery(Request $request, FpxTransaction $transaction): RedirectResponse|JsonResponse
    {
        abort_unless(Fpx::check($request), 403);

        $response = Fpx::getTransactionStatus($transaction->order_number, $transaction->exchange_order_number);

        if ($request->expectsJson()) {
            $transaction->refresh()->load('bank');

            return response()->json([
                'type' => ($response['status'] ?? 'failed') === 'succeeded' ? 'success' : 'warning',
                'message' => $response['message'] ?? 'Unable to requery transaction status.',
                'order_number' => $transaction->order_number,
                'transaction' => $this->serializeTransaction($transaction),
            ]);
        }

        return back()->with('status', [
            'type' => ($response['status'] ?? 'failed') === 'succeeded' ? 'success' : 'warning',
            'message' => $response['message'] ?? 'Unable to requery transaction status.',
            'order_number' => $transaction->order_number,
        ]);
    }

    protected function serializeTransaction(FpxTransaction $transaction): array
    {
        $statusCode = $transaction->debit_auth_code;
        $statusClass = $statusCode === '00' ? 'success' : ($statusCode === '09' ? 'warning text-dark' : 'danger');

        return [
            'id' => $transaction->id,
            'order_number' => $transaction->order_number,
            'exchange_order_number' => $transaction->exchange_order_number,
            'reference_id' => $transaction->reference_id,
            'reference_type' => $transaction->reference_type,
            'transaction_id' => $transaction->transaction_id,
            'bank' => optional($transaction->bank)->short_name ?? $transaction->request_payload?->targetBankId,
            'amount' => number_format((float) str_replace(',', '', $transaction->request_payload->amount ?? 0), 2),
            'status_code' => $statusCode,
            'status_text' => $transaction->response_code_description ?? 'Unknown',
            'status_class' => $statusClass,
            'updated_at' => optional($transaction->updated_at)->format('Y-m-d H:i:s'),
            'request_payload' => $transaction->request_payload,
            'response_payload' => $transaction->response_payload,
            'requery_url' => route('fpx.transactions.requery', $transaction),
        ];
    }
}
