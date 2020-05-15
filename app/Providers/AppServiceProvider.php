<?php

namespace App\Providers;

use App\Components\Vendor\Manager;
use App\Model\Booking\Order;
use App\Model\CruiseCabin;
use App\Observers\BookingOrderObserver;
use App\Model\Booking\Cabin as BookingCabin;
use App\Observers\BookingCabinsObserver;
use App\Observers\CruiseCabinObserver;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;


/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Order::observe(BookingOrderObserver::class);
        BookingCabin::observe(BookingCabinsObserver::class);
        CruiseCabin::observe(CruiseCabinObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    	//register ide helper for development environment
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        //registering vendor manager
        $this->app->bind(Manager::class, function ($app) {
            return new Manager($app);
        });
    }
}
