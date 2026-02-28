<?php

namespace ZarulIzham\Fpx\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use ZarulIzham\Fpx\Fpx;
use ZarulIzham\Fpx\Messages\AuthEnquiry;
use ZarulIzham\Fpx\Models\FpxTransaction;

class PaymentStatusCommand extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fpx:payment-status {order_number? : Order Number}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'get status of payment.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 */
	public function handle()
	{

		$orderNumbers = $this->argument('order_number');
		if ($orderNumbers) {
			$orderNumbers = explode(',', $orderNumbers);
			$orderNumbers = FpxTransaction::query()
				->whereIn('order_number', $orderNumbers)
				->get('order_number')
				->toArray();
		} else {
			$orderNumbers = FpxTransaction::whereNull('debit_auth_code')->orWhere('debit_auth_code', AuthEnquiry::STATUS_PENDING_CODE)->get('order_number')->toArray();
		}

		if ($orderNumbers) {
			try {
				$bar = $this->output->createProgressBar(count($orderNumbers));
				$bar->start();
				foreach ($orderNumbers as $row) {
					$status[] = Fpx::getTransactionStatus($row['order_number']);
				}
			} catch (Exception $e) {
				$status[] = [
					'status' => 'failed',
					'message' => $e->getMessage(),
					'transaction_id' => null,
					'order_number' => $row['order_number'],
					'amount' => null,
					'transaction_timestamp' => null,
					'buyer_bank_name' => null,
					'response_format' => null,
					'additional_params' => null,
				];
			}
			$bar->finish();
			$this->newLine();
			$this->newLine();

			$this->table(array_keys(Arr::first($status)), $status);
			$this->newLine();


		} else {
			$this->error("There is no Pending transactions.");
		}
	}
}
