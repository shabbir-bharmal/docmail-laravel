<?php namespace Softlabs\Docmail;

use Illuminate\Support\ServiceProvider;

class DocmailServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//

        $this->app['docmail'] = $this->app->share(function($app)
        {
            $docmail = new Docmail();
            return $docmail;
        });

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('Softlabs/Docmail');
    }

}