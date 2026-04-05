<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;

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

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'Bank' => \App\Models\BankAccount::class,
            'Cash' => \App\Models\CashPayment::class,
        ]);
    }
}
