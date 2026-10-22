<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'auth.login', 'invoices.index'], function ($view) {
            $setting = request()->attributes->get('crm.setting');
            if (! $setting) {
                $setting = Setting::firstOrCreate([], [
                    'owner_name' => config('crm.owner_name'),
                    'owner_email' => config('crm.owner_email'),
                ]);
                request()->attributes->set('crm.setting', $setting);
            }
            $view->with('setting', $setting);
        });
    }
}
