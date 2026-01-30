<?php

namespace Xenon007\SeatSpfarm\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class SeatSpfarmServiceProvider
 *
 * Registers the plugin with the SeAT application. This service provider
 * loads routes, views, migrations and registers menu entries. It also
 * publishes configuration and view assets when the application runs in
 * console to allow end users to customise the plugin.
 */
class SeatSpfarmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge our configuration with the host application. This allows
        // sensible defaults to be overridden by the consuming application.
        $this->mergeConfigFrom(__DIR__ . '/../../config/seat-spfarm.php', 'seat-spfarm');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load plugin resources
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'seat-spfarm');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Publish configuration and view files for customisation when running
        // artisan vendor:publish --tag=seat-spfarm-config or -views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/seat-spfarm.php' => config_path('seat-spfarm.php'),
            ], 'seat-spfarm-config');

            $this->publishes([
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/seat-spfarm'),
            ], 'seat-spfarm-views');
        }

        // Attempt to register a menu entry. Not all versions of SeAT expose
        // a menu service, so wrap in a try/catch to avoid fatal errors.
        try {
            // Defer menu registration until the application has booted to
            // ensure the menu service is available.
            $this->app->booted(function () {
                $this->registerMenu();
            });
        } catch (\Throwable $ex) {
            // Silently ignore menu registration errors. The plugin's routes
            // will still be accessible directly at /spfarm and /spfarm/settings.
        }
    }

    /**
     * Register entries in the SeAT menu.
     *
     * Many SeAT installations expose a 'menu' service via the container
     * (provided by lavary/laravel-menu). We construct a top‑level node
     * titled "SP Farming" and attach child entries for the dashboard and
     * settings pages. Icons use fontawesome names consistent with other
     * SeAT menus (fa-cow for the parent and fa-home/fa-cog for children).
     */
    protected function registerMenu(): void
    {
        // Attempt to resolve the menu service from the container
        $menu = $this->app['menu'] ?? null;
        if (! $menu) {
            return;
        }

        // Many SeAT installations use a root menu called 'sidebar'. If not
        // present, register on the root builder.
        $root = $menu->get('sidebar') ?? $menu;

        $spfarm = $root->add('SP Farming', [
            'icon' => 'fa fa-cow',
        ]);

        $spfarm->add('Dashboard', [
            'route' => 'seat-spfarm.dashboard',
            'icon' => 'fa fa-home',
        ]);

        $spfarm->add('Settings', [
            'route' => 'seat-spfarm.settings',
            'icon' => 'fa fa-cog',
        ]);
    }
}