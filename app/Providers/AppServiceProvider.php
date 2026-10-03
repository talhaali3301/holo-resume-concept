<?php

namespace App\Providers;

use App\Portfolio\PortfolioCatalog;
use App\Portfolio\PortfolioPresenter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PortfolioCatalog::class, fn (): PortfolioCatalog => PortfolioCatalog::load());
        $this->app->singleton(PortfolioPresenter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('app', function ($view): void {
            $view->with(app(PortfolioPresenter::class)->document(request()));
        });
    }
}
