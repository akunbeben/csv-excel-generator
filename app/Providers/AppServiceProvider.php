<?php

namespace App\Providers;

use App\Models\User;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Livewire\Livewire;

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

        Gate::define('viewPulse', function ($user) {
            if (Livewire::isLivewireRequest()) {
                return true;
            }

            return request('key') === config('pulse.key');
        });

        Pulse::user(function ($user) {
            $user->load(['sessions' => fn ($query) => $query->latest()->limit(1)]);

            return [
                'name' => $user->key,
                'extra' => "{$user->sessions->first()?->ip_address} | {$user->sessions->first()?->user_agent}",
            ];
        });
    }
}
