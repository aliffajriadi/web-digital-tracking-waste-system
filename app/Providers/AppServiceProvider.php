<?php

namespace App\Providers;

use App\Services\StockService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StockService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SET LOCALE KE INDONESIA AGAR translatedFormat BEKERJA
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        // Peringatan B3 di sidebar & navbar admin (di-cache singkat agar tidak dihitung di setiap halaman)
        View::composer(['layouts.partials.sidebar', 'layouts.partials.navbar'], function ($view) {
            static $alerts = null;
            $alerts ??= Cache::remember('admin.b3_alerts', 60, fn () => app(StockService::class)->b3Alerts()->all());

            $view->with('b3AlertCount', count($alerts));
            $view->with('b3AlertsPreview', collect($alerts)->take(6));
        });
    }
}
