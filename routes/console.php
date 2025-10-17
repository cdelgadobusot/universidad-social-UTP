<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\DB;

Artisan::command('sessions:flush', function () {
    $driver = config('session.driver');

    if ($driver === 'database') {
        $table = config('session.table', 'sessions');
        DB::table($table)->truncate();
        $this->info("Tabla '{$table}' truncada. Todos deslogueados.");
        return;
    }

    if ($driver === 'file') {
        $path = storage_path('framework/sessions');
        $deleted = 0;
        foreach (glob($path.'/*') ?: [] as $f) {
            if (is_file($f)) { @unlink($f); $deleted++; }
        }
        $this->info("Eliminadas {$deleted} sesiones (driver file).");
        return;
    }

    $this->warn("Driver de sesión '{$driver}' no gestionado automáticamente. Limpia manualmente.");
})->purpose('Cierra todas las sesiones activas (forzar re-login)');
