<?php

namespace ZarulIzham\Fpx\Models;

use ZarulIzham\Fpx\Models\Bank;
use Illuminate\Database\Eloquent\Model;
use ZarulIzham\Fpx\Constant\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FpxTransaction extends Model
{
	use HasFactory;

	public function getTable()
	{
		return config('fpx.tables.fpx_transactions', 'fpx_transactions');
	}

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'request_payload' => 'object',
		'response_payload' => 'object',
	];

	public function getAttribute($key)
	{
		[$key, $path] = preg_split('/(->|\.)/', $key, 2) + [null, null];

		return data_get(parent::getAttribute($key), $path);
	}

	/**
	 * Get the bank that owns the FpxTransaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function bank(): BelongsTo
	{
		return $this->belongsTo(Bank::class, 'request_payload->targetBankId', 'bank_id');
	}

	public function getResponseCodeDescriptionAttribute()
	{
		return Response::STATUS[$this->debit_auth_code];
	}
}
