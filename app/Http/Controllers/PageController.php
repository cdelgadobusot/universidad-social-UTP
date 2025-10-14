<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home() {
        return view('pages.home');
    }

    public function postularActividades() {
        return view('pages.postular_actividades');
    }
    public function postularActividadesSubmit(Request $request) {
        $request->validate([
            'lugar' => 'required|string|max:255',
            'fecha' => 'required|date',
            'participantes' => 'required|integer|min:1',
            'tipo' => 'required|string|max:255',
            'permisos' => 'nullable|string',
            'datos' => 'required|string',
            'firma' => 'required|string',
        ]);
        return back()->with('ok', 'Actividad postulada; un trabajador de la DSSU se pondrá en contacto contigo pronto.');
    }

    public function registrarActividades() {
        return view('pages.registrar_actividades');
    }
    public function registrarActividadesSubmit(Request $request) {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
            'lugar' => 'required|string|max:255',
            'recibo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);
        return back()->with('ok', 'Te has registrado en la actividad correctamente.');
    }

    public function crearLista() {
        $actividades = [
            ['id' => 1, 'titulo' => 'Reforestación Parque', 'fecha' => '2025-07-01'],
            ['id' => 2, 'titulo' => 'Recogida de Alimentos', 'fecha' => '2025-07-10'],
        ];
        $estudiantes = [
            'Ana Gómez','Luis Pérez','María Rodríguez','Carlos Díaz','José López','Fernanda Torres','Miguel Castillo','Laura Sánchez'
        ];
        return view('pages.crear_lista_asistencia', compact('actividades','estudiantes'));
    }
    public function compartirLista(Request $request) {
        return back()->with('ok', 'Lista compartida con éxito.');
    }
    public function borradorLista(Request $request) {
        return back()->with('ok', 'Borrador guardado.');
    }

    public function tomarLista() {
        $participantes = ['Ana Gómez','Luis Pérez','María Rodríguez','Carlos Díaz'];
        return view('pages.tomar_lista_asistencia', compact('participantes'));
    }
    public function tomarListaSubmit(Request $request) {
        $request->validate(['firma' => 'required|string']);
        return back()->with('ok', 'Se ha enviado la lista de asistencia correctamente.');
    }

    public function ingresarHoras() {
        $estudiantes = [
            ['nombre' => 'Ana Gómez', 'servicio' => 30, 'voluntariado' => 10],
            ['nombre' => 'Luis Pérez', 'servicio' => 20, 'voluntariado' => 5],
            ['nombre' => 'María Rodríguez', 'servicio' => 0, 'voluntariado' => 12],
            ['nombre' => 'Carlos Díaz', 'servicio' => 15, 'voluntariado' => 8],
        ];
        return view('pages.ingresar_horas', compact('estudiantes'));
    }
    public function ingresarHorasSubmit(Request $request) {
        $request->validate([
            'servicio' => 'required|integer|min:0',
            'voluntariado' => 'required|integer|min:0',
        ]);
        return back()->with('ok', 'Horas actualizadas correctamente.');
    }
}

