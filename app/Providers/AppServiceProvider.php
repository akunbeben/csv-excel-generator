<?php

namespace App\Providers;

use App\Models\User;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

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
        FilamentColor::register([
            'gray' => Color::Zinc,
            'primary' => Color::Sky,
        ]);

        Gate::define('viewPulse', fn () => request(null)->query('key') === config('app.key'));
        Pulse::user(fn ($user) => ['key' => $user->key]);
    }
}
