<?php

namespace Orkhanahmadov\LaravelCurrencylayer;

use Illuminate\Support\ServiceProvider;
use OceanApplications\currencylayer\client;
use Orkhanahmadov\LaravelCurrencylayer\Commands\LiveCommand;
use Orkhanahmadov\LaravelCurrencylayer\Commands\RateCommand;

/**
 * @codeCoverageIgnore
 */
class LaravelCurrencylayerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->bind(client::class, static function () {
            return new client(config('currencylayer.access_key'));
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('currencylayer.php'),
            ], 'config');

            if (! class_exists('CreateCurrencylayerCurrenciesTable') &&
                ! class_exists('CreateCurrencylayerRatesTable')
            ) {
                $this->publishes([
                    __DIR__.'/../database/migrations/create_currencylayer_currencies_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_currencylayer_currencies_table.php'),
                    __DIR__.'/../database/migrations/create_currencylayer_rates_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_currencylayer_rates_table.php'),
                ], 'migrations');
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'currencylayer');

        $this->app->singleton('currencylayer', static function () {
            return new Currencylayer(new client(config('currencylayer.access_key')));
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                LiveCommand::class,
                RateCommand::class,
            ]);
        }
    }
}
