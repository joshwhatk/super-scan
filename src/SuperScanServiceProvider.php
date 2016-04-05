<?php

namespace JoshWhatK\SuperScan;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.1
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use Illuminate\Support\ServiceProvider;

class SuperScanServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/joshwhatk.super_scan.php' => config_path('joshwhatk.super_scan.php'),
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ]);

        $this->loadViewsFrom(__DIR__.'/../views', 'super-scan');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
