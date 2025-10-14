<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PageController;

/*
|--------------------------------------------------------------------------
| Redirección raíz
|--------------------------------------------------------------------------
| Muestra /login por defecto.
| Si el usuario ya está autenticado, lo manda a /home.
*/
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
})->name('root');

/*
|--------------------------------------------------------------------------
| Dashboard de Breeze
|--------------------------------------------------------------------------
| Breeze intenta redirigir a 'dashboard' tras login/registro.
| Aquí lo enviamos a tu página principal (home).
*/
Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas protegidas (solo para usuarios autenticados)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/home', [PageController::class, 'home'])->name('home');

    Route::get('/postular-actividades', [PageController::class, 'postularActividades'])->name('postular.actividades');
    Route::post('/postular-actividades', [PageController::class, 'postularActividadesSubmit'])->name('postular.actividades.submit');

    Route::get('/registrar-actividades', [PageController::class, 'registrarActividades'])->name('registrar.actividades');
    Route::post('/registrar-actividades', [PageController::class, 'registrarActividadesSubmit'])->name('registrar.actividades.submit');

    Route::get('/crear-lista-asistencia', [PageController::class, 'crearLista'])->name('crear.lista');
    Route::post('/crear-lista-asistencia/compartir', [PageController::class, 'compartirLista'])->name('crear.lista.compartir');
    Route::post('/crear-lista-asistencia/borrador', [PageController::class, 'borradorLista'])->name('crear.lista.borrador');

    Route::get('/tomar-lista-asistencia', [PageController::class, 'tomarLista'])->name('tomar.lista');
    Route::post('/tomar-lista-asistencia', [PageController::class, 'tomarListaSubmit'])->name('tomar.lista.submit');

    Route::get('/ingresar-horas', [PageController::class, 'ingresarHoras'])->name('ingresar.horas');
    Route::post('/ingresar-horas', [PageController::class, 'ingresarHorasSubmit'])->name('ingresar.horas.submit');
});

/*
|--------------------------------------------------------------------------
| Rutas de autenticación (login/register/forgot password)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
