<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('fpx.tables.fpx_transactions', 'fpx_transactions'), function (Blueprint $table) {
			$table->id();
			$table->string('unique_id')->comment('Unique auto generated reference Id');
			$table->string('reference_id')->comment('Unique Order no/Reference id');
			$table->string('transaction_id')->nullable()->comment('Transaction id returned by FPX');
			$table->string('debit_auth_code')->default('09')->comment('Transaction status code');
			$table->text('request_payload')->comment('Request data sent to FPX');
			$table->text('response_payload')->nullable()->comment('Response data received from FPX');
			$table->string('response_format')->default('HTML')->comment('Response format HTML/JSON');
			$table->text('additional_params')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists(config('fpx.tables.fpx_transactions', 'fpx_transactions'));
	}
};
