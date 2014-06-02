<?php namespace Quickjob\Siesta;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class SiestaServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot() {
		$this->package('quickjob/siesta');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['quickjob.siesta'] = $this->app->share(function($app)
		{
			// return new Environment($app);
		});

		$this->app->booting(function()
		{
			// $loader = AliasLoader::getInstance();
			// $loader->alias('Siesta', 'Quickjob\Siesta\Facades\Siesta');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('quickjob.siesta');
	}

}
