<?php

namespace ZarulIzham\Fpx\Messages;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use ZarulIzham\Fpx\Models\FpxTransaction;
use ZarulIzham\Fpx\Traits\VerifyCertificate;
use ZarulIzham\Fpx\Events\FpxTransactionUpdated;
use ZarulIzham\Fpx\Contracts\Message as Contract;

class AuthorizationRequest extends Message implements Contract
{
	use VerifyCertificate;

	protected $referenceId;

	protected $referenceType;

	/**
	 * Message code on the FPX side
	 */
	public const CODE = 'AR';


	/**
	 * Message Url
	 */
	public $url;


	public function __construct()
	{
		parent::__construct();

		$this->url = App::environment('production') ?
			Config::get('fpx.urls.production.auth_request') :
			Config::get('fpx.urls.uat.auth_request');
	}

	/**
	 * handle a message
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function handle($options)
	{
		$data = Validator::make(
			$options,
			[
				'order_number' => 'required',
				'exchange_order_number' => 'nullable',
				'reference_id' => 'nullable',
				'reference_type' => 'nullable|string|max:255',
				'datetime' => 'nullable',
				'currency' => 'nullable',
				'response_format' => 'nullable',
				'remark' => 'nullable',
				'additional_params' => 'nullable',
				'amount' => 'required|numeric|between:'.Config::get('fpx.min_amount', '1').','.Config::get('fpx.max_amount', '30000'),
				'customer_name' => 'required',
				'customer_email' => 'required',
				'bank_id' => 'required',
				'flow' => 'required|in:01,02,03',
			],
			[
				'order_number.required' => 'Order Number is required.',
				'customer_name.required' => 'Buyer Name is required.',
				'customer_email.required' => 'Email is required.',
				'bank_id.required' => 'Please select bank for the payment.',
				'flow.required' => 'Please select bank type.'
			],
		)->validate();


		$this->type = self::CODE;
		$this->flow = $data['flow'];
		$this->reference = $data['order_number'];
		$this->id = $data['exchange_order_number'] ?? $this->id;
		$this->referenceId = $data['reference_id'] ?? null;
		$this->referenceType = $data['reference_type'] ?? null;
		$this->timestamp = $data['datetime'] ?? date("YmdHis");
		$this->currency = $data['currency'] ?? $this->currency;
		$this->productDescription = $data['remark'] ?? ' ';
		$this->amount = $data['amount'];
		$this->buyerEmail = $data['customer_email'];
		$this->buyerName = $data['customer_name'];
		$this->targetBankId = $data['bank_id'];
		$this->checkSum = $this->getCheckSum($this->format());
		$this->responseFormat = $data['response_format'] ?? 'HTML';
		$this->additionalParams = $data['additional_params'];

		$this->saveTransaction();

		return $this;
	}


	/**
	 * Format data for checksum
	 * @return string
	 */
	public function format()
	{
		return $this->list()->join('|');
	}

	/**
	 * returns collection of all fields
	 */
	public function list()
	{
		return collect([
			'buyerAccountNumber' => $this->buyerAccountNumber ?? '',
			'targetBankBranch' => $this->targetBankBranch ?? '',
			'targetBankId' => $this->targetBankId ?? '',
			'buyerEmail' => $this->buyerEmail ?? '',
			'buyerIBAN' => $this->buyerIBAN ?? '',
			'buyerId' => $this->buyerId ?? '',
			'buyerName' => $this->buyerName ?? '',
			'buyerMakerName' => $this->buyerMakerName ?? '',
			'flow' => $this->flow ?? '',
			'type' => $this->type ?? '',
			'productDescription' => $this->productDescription ?? '',
			'bankCode' => $this->bankCode ?? '',
			'exchangeId' => $this->exchangeId ?? '',
			'id' => $this->id ?? '',
			'sellerId' => $this->sellerId ?? '',
			'reference' => $this->reference ?? '',
			'timestamp' => $this->timestamp ?? '',
			'amount' => $this->amount ?? '',
			'currency' => $this->currency ?? '',
			'version' => $this->version ?? '',
		]);
	}

	/**
	 * Save request to transaction
	 */
	public function saveTransaction()
	{

		$transaction = new FpxTransaction;
		$transaction->exchange_order_number = $this->id;
		$transaction->order_number = $this->reference;
		$transaction->reference_id = $this->referenceId;
		$transaction->reference_type = $this->referenceType;
		$transaction->response_format = $this->responseFormat;
		$transaction->additional_params = $this->additionalParams;
		$transaction->request_payload = json_decode($this->list()->toJson());
		$transaction->save();

		event(new FpxTransactionUpdated($transaction));
	}
}
