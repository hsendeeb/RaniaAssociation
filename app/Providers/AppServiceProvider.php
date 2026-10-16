<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
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
        // Inertia serialises ApiResources through their `Responsable` contract,
        // which would otherwise nest every prop under a `data` key. Disabling
        // the wrapper keeps single resources and plain collections flat, while
        // paginated collections still expose `data`, `links`, and `meta`.
        JsonResource::withoutWrapping();
    }
}
