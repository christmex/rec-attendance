<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\Widgets\View\WidgetsRenderHook;
use Illuminate\Contracts\View\View;

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
        Model::unguard();
        FilamentView::registerRenderHook(
            WidgetsRenderHook::TABLE_WIDGET_START,
            // fn (): View => view('event'),
            fn (): View => view('event_with_form'),
            scopes: \App\Filament\Widgets\MemberList::class
        );
    }
}
