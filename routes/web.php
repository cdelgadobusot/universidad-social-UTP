<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PageController;

Route::get('/', fn() => redirect()->route('login'))->name('root');

/*
|--------------------------------------------------------------------------
| Redirección raíz tras login (Breeze redirige a /dashboard)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $u = Auth::user();
    if (!$u) return redirect()->route('login');

    return match ($u->role) {
        'administrador' => redirect()->route('home'),
        'profesor'      => redirect()->route('home'),
        'organizacion'  => redirect()->route('home'),
        'estudiante'    => redirect()->route('home'),
        default         => redirect()->route('home'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas protegidas comunes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/home', [PageController::class, 'home'])->name('home');

    // (si los usas aún) funcionalidades generales
    Route::get('/crear-lista-asistencia', [PageController::class, 'crearLista'])->name('crear.lista');
    Route::post('/crear-lista-asistencia/compartir', [PageController::class, 'compartirLista'])->name('crear.lista.compartir');
    Route::post('/crear-lista-asistencia/borrador', [PageController::class, 'borradorLista'])->name('crear.lista.borrador');

    Route::get('/ingresar-horas', [PageController::class, 'ingresarHoras'])->name('ingresar.horas');
    Route::post('/ingresar-horas', [PageController::class, 'ingresarHorasSubmit'])->name('ingresar.horas.submit');
});

/*
|--------------------------------------------------------------------------
| Profesor / Organización
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:profesor,organizacion'])->group(function () {
    // Postular actividades
    Route::get('/postular-actividades',  [PageController::class, 'postularActividades'])->name('postular.actividades');
    Route::post('/postular-actividades', [PageController::class, 'postularActividadesSubmit'])->name('postular.actividades.submit');

    // Tomar lista (HABILITADA por admin)
    Route::get('/tomar-lista-asistencia',  [PageController::class, 'tomarLista'])->name('tomar.lista');
    Route::post('/tomar-lista-asistencia', [PageController::class, 'tomarListaSubmit'])->name('tomar.lista.submit');
});

/*
|--------------------------------------------------------------------------
| Estudiante
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:estudiante'])->group(function () {
    Route::get('/registrar-actividades',  [PageController::class, 'registrarActividades'])->name('registrar.actividades');
    Route::post('/registrar-actividades', [PageController::class, 'registrarActividadesSubmit'])->name('registrar.actividades.submit');
});

/*
|--------------------------------------------------------------------------
| Administrador
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:administrador'])->group(function () {
    // Bandeja de propuestas
    Route::get('/admin/propuestas', [PageController::class, 'adminPropuestas'])->name('admin.propuestas');
    Route::post('/admin/propuestas/{id}/decision', [PageController::class, 'adminPropuestaDecision'])->name('admin.propuestas.decision');

    // Ver detalle de propuesta (rechazada o pendiente)
    Route::get('/admin/propuesta/{id}', [PageController::class, 'adminPropuestaShow'])->name('admin.propuesta.show');

    // Gestión de actividad aceptada
    Route::get('/admin/actividades/{id}', [PageController::class, 'adminActividadShow'])->name('admin.actividad.show');
    Route::post('/admin/actividades/{id}/agregar-estudiante',   [PageController::class, 'adminActividadAddStudent'])->name('admin.actividad.addStudent');
    Route::post('/admin/actividades/{id}/remover-estudiante',   [PageController::class, 'adminActividadRemoveStudent'])->name('admin.actividad.removeStudent');
    Route::post('/admin/actividades/{id}/cerrar-convocatoria',  [PageController::class, 'adminActividadClose'])->name('admin.actividad.close');
    Route::post('/admin/actividades/{id}/habilitar-lista',      [PageController::class, 'adminActividadEnableAttendance'])->name('admin.actividad.enableAttendance');

    // Extra controles: cerrar lista y finalizar actividad
    Route::post('/admin/actividad/{id}/close-attendance', [PageController::class, 'adminActividadCloseAttendance'])->name('admin.actividad.closeAttendance');
    Route::post('/admin/actividad/{id}/finalize',         [PageController::class, 'adminActividadFinalize'])->name('admin.actividad.finalize');

    // Otorgar horas manualmente (si aplica)
    Route::post('/admin/actividades/{id}/otorgar-horas',  [PageController::class, 'adminActividadAwardHours'])->name('admin.actividad.awardHours');
});

/*
|--------------------------------------------------------------------------
| Auth scaffolding + fallback
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

Route::fallback(fn() => redirect()->route('login'));
