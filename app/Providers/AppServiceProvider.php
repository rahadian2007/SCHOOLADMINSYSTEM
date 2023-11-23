<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        Blade::directive('currency', function ($amount) {
            return "<?php echo 'Rp' . number_format($amount); ?>";
        });

        Blade::directive('numeric', function ($amount) {
            return "<?php echo number_format($amount); ?>";
        });
    }
}
