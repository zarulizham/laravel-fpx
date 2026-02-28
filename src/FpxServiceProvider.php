<?php

namespace ZarulIzham\Fpx;

use ZarulIzham\Fpx\Commands\UpdateBankListCommand;
use ZarulIzham\Fpx\Commands\FpxPublish;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ZarulIzham\Fpx\Commands\PaymentStatusCommand;

class FpxServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application services.
	 */
	public function boot()
	{
		$this->configureRoutes();

		$this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-fpx');
		$this->loadViewsFrom(__DIR__.'/../resources/views', 'fpx-payment');

		$this->configureComponents();

		$this->configurePublish();
	}

	/**
	 * Register the application services.
	 */
	public function register()
	{
		// Automatically apply the package configuration
		$this->mergeConfigFrom(__DIR__.'/../config/fpx.php', 'fpx');
	}

	public function configureComponents()
	{
		Blade::component('laravel-fpx::components.pay', 'laravel-fpx');
	}

	public function configureRoutes()
	{
		Route::group([
			'middleware' => Config::get('fpx.middleware')
		], function () {
			$this->loadRoutesFrom(__DIR__.'/../routes/web.php');
		});

		Route::prefix('api')->group(
			function () {
				Route::group([
					'middleware' => 'api'
				], function () {
					$this->loadRoutesFrom(__DIR__.'/../routes/api.php');
				});
			}
		);
	}

	public function configurePublish()
	{
		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__.'/../config/fpx.php' => config_path('fpx.php'),
			], 'fpx-config');

			$this->publishes($this->migrationPublishPaths(), 'fpx-migrations');

			$this->publishes([
				__DIR__.'/../stubs/Controller.php' => app_path('Http/Controllers/FPX/Controller.php'),
			], 'fpx-controller');

			$this->publishes([
				__DIR__.'/../public/assets' => public_path('assets/vendor/fpx'),
			], 'fpx-assets');

			$this->publishes([
				__DIR__.'/../resources/views/payment.blade.php' => resource_path('views/vendor/fpx-payment/payment.blade.php'),
			], 'fpx-views');

			$this->publishes([
				__DIR__.'/../resources/lang/en.php' => lang_path('vendor/laravel-fpx/en.php'),
				__DIR__.'/../resources/lang/ms.php' => lang_path('vendor/laravel-fpx/ms.php'),
			], 'fpx-locales');

			$this->commands([
				UpdateBankListCommand::class,
				FpxPublish::class,
				PaymentStatusCommand::class
			]);
		}
	}

	protected function migrationPublishPaths(): array
	{
		$paths = [];
		$migrationFiles = glob(__DIR__.'/../database/migrations/*.php') ?: [];

		foreach ($migrationFiles as $migrationFile) {
			$paths[$migrationFile] = database_path('migrations/'.basename($migrationFile));
		}

		return $paths;
	}
}
