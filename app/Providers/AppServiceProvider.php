<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password; // ← Importante

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
        // Política de contraseña global:
        Password::defaults(function () {
            return Password::min(8)   // mínimo 8 caracteres
                ->mixedCase()         // al menos una mayúscula y una minúscula
                ->symbols();          // al menos un carácter especial
        });
    }
}
