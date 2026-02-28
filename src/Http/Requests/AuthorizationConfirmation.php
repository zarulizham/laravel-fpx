<?php

namespace ZarulIzham\Fpx\Http\Requests;

use ZarulIzham\Fpx\Messages\AuthorizationConfirmation as AuthorizationConfirmationMessage;
use Illuminate\Foundation\Http\FormRequest;

class AuthorizationConfirmation extends FormRequest
{

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [];
	}


	/**
	 * Presist the data to the users table
	 */
	public function handle()
	{
		$data = $this->all();

		return (new AuthorizationConfirmationMessage)->handle($data);
	}
}
