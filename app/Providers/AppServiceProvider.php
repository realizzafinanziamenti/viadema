<?php

namespace App\Providers;

use App\Services\Imports\CrmXlsxReader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Livewire\Mechanisms\ComponentRegistry::class, \App\Livewire\Chat\ComponentRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            \Livewire\Livewire::component('wirechat.chat', \App\Livewire\Chat\Chat::class);
            \Livewire\Livewire::component('wirechat.chat.info', \App\Livewire\Chat\Info::class);
            \Livewire\Livewire::component('wirechat.chat.group.info', \App\Livewire\Chat\GroupInfo::class);
        });
        IOFactory::registerReader(CrmXlsxReader::TYPE, CrmXlsxReader::class);

        Blade::withoutDoubleEncoding();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });
    }
}
