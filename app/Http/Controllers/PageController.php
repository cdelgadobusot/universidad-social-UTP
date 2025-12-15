<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    /* ================= DASHBOARD por rol (inyecta datos) ================= */

    public function home(): View
    {
        $user = Auth::user();
        $role = $user?->role ?? 'guest';

        if ($role === 'profesor' || $role === 'organizacion') {
            // Sus propias postulaciones
            $proposals = DB::table('activity_proposals as p')
                ->leftJoin('activities as a','a.id','=','p.activity_id')
                ->select('p.*','a.title as activity_title','a.status as activity_status')
                ->where('p.proposer_user_id', $user->id)
                ->orderByDesc('p.created_at')
                ->get();

            return view($role === 'profesor' ? 'pages.home_profesor' : 'pages.home_organizacion', [
                'proposals' => $proposals,
            ]);
        }

        if ($role === 'estudiante') {
            // Horas totales
            $totalHours = (int) DB::table('hours_logs')
                ->where('student_id', $user->id)
                ->sum('hours');

            // Actividades abiertas (publicadas)
            $openActs = DB::table('activities')
                ->where('status', 'publicada')
                ->orderBy('start_date')
                ->get();

            // IDs de actividades donde YA está inscrito este estudiante
            $myRegIds = DB::table('activity_registrations')
                ->where('student_id', $user->id)
                ->pluck('activity_id')
                ->map(fn($v) => (int)$v)
                ->all();

            // Actividades cerradas en las que está inscrito
            $closedMine = DB::table('activities as a')
                ->join('activity_registrations as r','r.activity_id','=','a.id')
                ->where('a.status','cerrada')
                ->where('r.student_id',$user->id)
                ->select('a.*')
                ->orderBy('a.start_date')
                ->get();

            return view('pages.home_estudiante', [
                'totalHours' => $totalHours,
                'openActs'   => $openActs,
                'closedMine' => $closedMine,
                'myRegIds'   => $myRegIds,   // <<< pásalo a la vista
            ]);
        }


        if ($role === 'administrador') {
            // Bandeja admin: propuestas pendientes y últimas aceptadas/rechazadas
            $pendientes  = DB::table('activity_proposals')->where('status','pendiente')->orderByDesc('created_at')->get();
            $recientes   = DB::table('activity_proposals')->whereIn('status',['aprobada','rechazada'])->orderByDesc('updated_at')->limit(20)->get();
            $actividades = DB::table('activities')->orderByDesc('created_at')->limit(20)->get();

            return view('pages.home_admin', compact('pendientes','recientes','actividades'));
        }

        return view('pages.home');
    }

    /* ================= POSTULAR (profesor/organizacion) ================= */

    public function postularActividades(): View
    {
        return view('pages.postular_actividades');
    }

    public function postularActividadesSubmit(Request $r)
    {
        $r->validate([
            'place' => 'required|string|max:255',
            'event_date' => 'required|date',
            'participants_count' => 'required|integer|min:1',
            'work_type' => 'required|string|max:120',
            'proposal_description' => 'required|string',
            'manager_name' => 'required|string|max:120',
            'manager_phone' => 'nullable|string|max:50',
            'manager_doc' => 'required|file|mimes:pdf,png,jpg,jpeg|max:2048',
        ]);

        $docPath = $r->file('manager_doc')->store('documents', 'public');

        DB::table('activity_proposals')->insert([
            'proposer_user_id' => $r->user()->id,
            'proposer_role' => $r->user()->role, // profesor u organizacion
            'place' => $r->place,
            'event_date' => $r->event_date,
            'participants_count' => $r->participants_count,
            'work_type' => $r->work_type,
            'description' => $r->proposal_description, // guarda descripción de la propuesta
            'permits' => $r->input('permits'),
            'manager_data' => json_encode([
                'encargado' => $r->manager_name,
                'telefono' => $r->manager_phone,
            ]),
            'signature_path' => $docPath,   // documento del encargado
            'status' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ⬇️ Redirige al dashboard con mensaje de éxito
        return redirect()
            ->route('home')
            ->with('status', 'Tu propuesta fue enviada correctamente. Nos pondremos en contacto contigo pronto.');
    }




    /* ================= REGISTRAR (estudiante) ================= */

    public function registrarActividades(): View
    {
        $acts = DB::table('activities')
            ->where('status','publicada')
            ->orderBy('start_date')
            ->get();

        return view('pages.registrar_actividades', compact('acts'));
    }

    public function registrarActividadesSubmit(Request $r)
    {
        $r->validate(
    [
        'activity_id' => ['required', 'exists:activities,id'],
        'receipt'     => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
    ],
    [
        'activity_id.required' => 'Debe seleccionar una actividad.',
        'activity_id.exists'   => 'La actividad seleccionada no existe.',

        'receipt.required' => 'El recibo de matrícula es obligatorio.',
        'receipt.file'     => 'El recibo debe ser un archivo válido.',
        'receipt.mimes'    => 'El recibo debe ser PDF, JPG o PNG.',
        'receipt.max'      => 'El archivo no debe superar los 2MB.',
    ]
);


        // Verifica que la actividad esté abierta
        $act = DB::table('activities')->where('id',$r->activity_id)->first();
        if (!$act || $act->status !== 'publicada') {
            return back()->withErrors(['activity_id' => 'La convocatoria no está abierta.']);
        }

        // Sube el recibo
        $path = $r->file('receipt')->store('receipts', 'public');

        // Crea/actualiza la inscripción (evita duplicados por alumno/actividad)
        DB::table('activity_registrations')->updateOrInsert(
            ['activity_id' => $r->activity_id, 'student_id' => Auth::id()],
            ['receipt_path' => $path, 'status' => 'pendiente', 'updated_at' => now(), 'created_at' => now()]
        );

        // Redirige al panel con mensaje de confirmación
        return redirect()
            ->route('home')
            ->with('status', '✅ Te has inscrito correctamente en “'.$act->title.'”. Revisaremos tu inscripción.');
    }


    /* ================= ADMIN — Propuestas ================= */

    public function adminPropuestas(): View
    {
        $propuestas = DB::table('activity_proposals as p')
            ->leftJoin('users as u','u.id','=','p.proposer_user_id')
            ->select('p.*','u.name as proposer_name','u.email as proposer_email')
            ->orderByRaw("FIELD(p.status,'pendiente','aprobada','rechazada')")
            ->orderByDesc('p.created_at')
            ->get();

        return view('pages.admin_propuestas', compact('propuestas'));
    }

    public function adminPropuestaDecision(Request $r, int $id)
    {
        $r->validate([
            'decision' => 'required|in:aprobada,rechazada',
            'activity_title' => 'nullable|string|max:255',
            'activity_description' => 'nullable|string',
            'social_hours' => 'nullable|integer|min:1|max:100',
        ]);

        $prop = DB::table('activity_proposals as p')
            ->leftJoin('users as u','u.id','=','p.proposer_user_id')
            ->select('p.*','u.name as proposer_name')
            ->where('p.id',$id)->first();

        if (!$prop) abort(404);

        if ($r->decision === 'rechazada') {
            DB::table('activity_proposals')->where('id',$id)->update([
                'status'=>'rechazada', 'updated_at'=>now()
            ]);
            return back()->with('status','Propuesta rechazada.');
        }

        // aprobada → crear actividad PUBLICADA
        $title = $r->input('activity_title') ?: ('Trabajo social: '.$prop->work_type);
        $desc  = $r->input('activity_description') ?: ($prop->description ?: ('Propuesta de '.$prop->proposer_name.'.'));
        $hours = (int) ($r->input('social_hours') ?: 1);
        $orgUserId = ($prop->proposer_role === 'organizacion') ? $prop->proposer_user_id : null;

        $actId = DB::table('activities')->insertGetId([
            'title' => $title,
            'description' => $desc,          // <<< descripción de la actividad a publicar
            'place' => $prop->place,
            'start_date' => $prop->event_date,
            'start_time' => '08:00:00',
            'created_by' => $r->user()->id,
            'organization_user_id' => $orgUserId,
            'status' => 'publicada',
            'attendance_enabled' => 0,
            'social_hours' => $hours,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('activity_proposals')->where('id',$id)->update([
            'status'=>'aprobada',
            'activity_id'=>$actId,
            'updated_at'=>now()
        ]);

        return back()->with('status','Propuesta aceptada. Actividad publicada para estudiantes.');
    }


    /* ================= ADMIN — Gestión de actividad ================= */

    public function adminActividadShow(int $id): View
    {
        $act = DB::table('activities')->where('id',$id)->first();
        if (!$act) abort(404);

        // Inscritos
        $inscritos = DB::table('activity_registrations as r')
            ->join('users as u','u.id','=','r.student_id')
            ->select('u.id','u.name','u.email','r.status','r.created_at')
            ->where('r.activity_id',$id)
            ->orderBy('u.name')->get();

        // Última lista y asistentes (si existe)
        $list = DB::table('attendance_lists')->where('activity_id',$id)->orderByDesc('id')->first();
        $asistentes = [];
        if ($list) {
            $asistentes = DB::table('attendance_entries as e')
                ->join('users as u','u.id','=','e.student_id')
                ->select('u.id','u.name','e.attended','e.marked_at')
                ->where('e.attendance_list_id',$list->id)
                ->orderBy('u.name')->get();
        }

        // Todos los estudiantes (para agregar)
        $studentsAll = DB::table('users')->where('role','estudiante')->orderBy('name')->get();

        // ⬇️ NUEVO: Proponente (por la propuesta que originó la actividad)
        $prop = DB::table('activity_proposals as p')
            ->leftJoin('users as u','u.id','=','p.proposer_user_id')
            ->select(
                'p.id as proposal_id',
                'p.proposer_role',
                'u.id as proposer_id',
                'u.name as proposer_name',
                'u.email as proposer_email'
            )
            ->where('p.activity_id',$id)
            ->first();

        // Fallback si no hay propuesta vinculada pero la actividad pertenece a una organización
        if (!$prop && $act->organization_user_id) {
            $ou = DB::table('users')->where('id',$act->organization_user_id)->select('id','name','email')->first();
            if ($ou) {
                $prop = (object)[
                    'proposal_id'    => null,
                    'proposer_role'  => 'organizacion',
                    'proposer_id'    => $ou->id,
                    'proposer_name'  => $ou->name,
                    'proposer_email' => $ou->email,
                ];
            }
        }

        return view('pages.admin_actividad_show', compact('act','inscritos','list','asistentes','studentsAll','prop'));
    }


    public function adminPropuestaShow(int $id): View
    {
        $p = DB::table('activity_proposals as p')
            ->leftJoin('users as u','u.id','=','p.proposer_user_id')
            ->select('p.*','u.name as proposer_name','u.email as proposer_email')
            ->where('p.id',$id)->first();

        if (!$p) abort(404);

        return view('pages.admin_propuesta_show', compact('p'));
    }


    public function adminActividadAddStudent(Request $r, int $id)
    {
        $r->validate(['student_id'=>'required|exists:users,id']);

        $act = DB::table('activities')->where('id',$id)->first();
        if (!$act) abort(404);
        if ($act->status !== 'publicada') {
            return back()->withErrors(['student_id'=>'La convocatoria está cerrada.']);
        }

        DB::table('activity_registrations')->updateOrInsert(
            ['activity_id'=>$id,'student_id'=>$r->student_id],
            ['receipt_path'=>'admin-manual','status'=>'pendiente','updated_at'=>now(),'created_at'=>now()]
        );

        return back()->with('status','Estudiante agregado a la convocatoria.');
    }

    public function adminActividadRemoveStudent(Request $r, int $id)
    {
        $r->validate(['student_id'=>'required|exists:users,id']);

        DB::table('activity_registrations')->where([
            'activity_id'=>$id,'student_id'=>$r->student_id
        ])->delete();

        return back()->with('status','Estudiante removido de la convocatoria.');
    }

    public function adminActividadClose(int $id)
    {
        DB::table('activities')->where('id',$id)->update([
            'status'=>'cerrada','updated_at'=>now()
        ]);

        return back()->with('status','Convocatoria cerrada. Ya no se pueden anotar nuevos estudiantes.');
    }

    public function adminActividadEnableAttendance(int $id)
    {
        $act = DB::table('activities')->where('id',$id)->first();
        if (!$act) abort(404);

        // Debe estar cerrada la convocatoria para habilitar lista (según tu flujo)
        if ($act->status !== 'cerrada') {
            return back()->withErrors(['att'=>'Primero cierra la convocatoria.']);
        }

        // Crear/actualizar lista con los inscritos actuales
        DB::table('attendance_lists')->updateOrInsert(
            ['activity_id'=>$id],
            ['created_by'=>Auth::id(),'status'=>'compartida','shared_at'=>now(),'updated_at'=>now(),'created_at'=>now()]
        );

        // Recupera ID de la lista
        $list = DB::table('attendance_lists')->where('activity_id',$id)->first();

        // Sincroniza entradas con inscritos
        $inscritos = DB::table('activity_registrations')->where('activity_id',$id)->pluck('student_id')->toArray();

        // Borra entradas antiguas y vuelve a crear desde inscritos
        DB::table('attendance_entries')->where('attendance_list_id',$list->id)->delete();
        foreach ($inscritos as $sid) {
            DB::table('attendance_entries')->insert([
                'attendance_list_id'=>$list->id,
                'student_id'=>$sid,
                'attended'=>0
            ]);
        }

        DB::table('activities')->where('id',$id)->update(['attendance_enabled'=>1,'updated_at'=>now()]);

        return back()->with('status','Lista habilitada para el organismo/profesor.');
    }

    public function adminActividadCloseAttendance(int $id)
    {
        $act = DB::table('activities')->where('id',$id)->first();
        if (!$act) abort(404);

        // Última lista de asistencia
        $list = DB::table('attendance_lists')
            ->where('activity_id',$id)
            ->orderByDesc('id')
            ->first();

        if (!$list) {
            return back()->withErrors(['att'=>'No hay lista para cerrar.']);
        }

        // Cerrar la lista
        DB::table('attendance_lists')->where('id',$list->id)->update([
            'status'     => 'cerrada',
            'updated_at' => now(),
        ]);

        // Deshabilitar "pasar lista" y marcar actividad como cerrada
        DB::table('activities')->where('id',$id)->update([
            'attendance_enabled' => 0,
            'status'             => 'cerrada',
            'updated_at'         => now(),
        ]);

        // Otorgar horas automáticamente a quienes asistieron (idempotente por unique)
        $presentes = DB::table('attendance_entries')
            ->where('attendance_list_id', $list->id)
            ->where('attended', 1)
            ->pluck('student_id')
            ->toArray();

        $hours = (int)($act->social_hours ?: 1);
        $count = 0;

        foreach ($presentes as $sid) {
            DB::table('hours_logs')->updateOrInsert(
                ['student_id' => $sid, 'activity_id' => $id, 'hours_type' => 'servicio'],
                [
                    'hours'     => $hours,
                    'added_by'  => Auth::id(),
                    'note'      => 'Otorgado automáticamente al cerrar lista',
                    'created_at'=> now()
                ]
            );
            $count++;
        }

        return redirect()->route('admin.actividad.show', $id)
            ->with('status', "Lista cerrada y horas otorgadas a {$count} asistente(s).");
    }




    public function adminActividadFinalize(Request $r, int $id)
    {
        return back()->with('status','Las horas ya se otorgan automáticamente al cerrar la lista.');
    }

    public function adminActividadAwardHours(Request $r, int $id)
    {
        return back()->with('status','Las horas ya se otorgan automáticamente al cerrar la lista.');
    }


    /* ================= TOMAR LISTA (organizacion / profesor) ================= */

    public function tomarLista(Request $r): View
    {
        $user = Auth::user();

        $query = DB::table('attendance_lists as l')
            ->join('activities as a','a.id','=','l.activity_id')
            ->leftJoin('activity_proposals as p','p.activity_id','=','a.id')
            ->select('l.*','a.title','a.place','a.start_date','a.start_time')
            ->where('l.status','compartida')          // solo listas habilitadas para tomar
            ->where('a.attendance_enabled',1);

        // Propietario según rol
        if ($user->role === 'organizacion') {
            $query->where('a.organization_user_id',$user->id);
        } else { // profesor
            $query->where('p.proposer_user_id',$user->id);
        }

        // Filtro por actividad (cuando se hace clic en el botón de una fila)
        if ($r->filled('activity_id')) {
            $query->where('l.activity_id', (int)$r->input('activity_id'));
        }

        $lists = $query->orderByDesc('l.shared_at')->get();

        return view('pages.tomar_lista_asistencia', compact('lists'));
    }

public function tomarListaSubmit(Request $r)
{
    $user = $r->user();

    $r->validate(
        [
            'attendance_list_id' => 'required|exists:attendance_lists,id',
            'attended'           => 'array',
            'signature'          => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    // Normaliza para comparar (sin espacios extra y sin importar mayúsculas)
                    $firma  = mb_strtolower(trim(preg_replace('/\s+/', ' ', $value)));
                    $nombre = mb_strtolower(trim(preg_replace('/\s+/', ' ', $user->name)));

                    if ($firma !== $nombre) {
                        $fail('La firma no coincide con tu usuario');
                    }
                }
            ],
        ],
        [
            'signature.required' => 'Debe ingresar su nombre para firmar la lista de asistencia.',
        ]
    );

    $listId = (int) $r->attendance_list_id;

    // Verifica que la lista siga habilitada para tomar (evita enviar listas no compartidas)
    $list = DB::table('attendance_lists')->where('id', $listId)->first();
    if (!$list || $list->status !== 'compartida') {
        return back()->withErrors(['signature' => 'Actividad no disponible para asistencia.']);
    }

    // Reset y marcado de asistencia
    DB::table('attendance_entries')
        ->where('attendance_list_id', $listId)
        ->update([
            'attended'  => 0,
            'marked_at' => now()
        ]);

    foreach (array_keys($r->input('attended', [])) as $sid) {
        DB::table('attendance_entries')
            ->where('attendance_list_id', $listId)
            ->where('student_id', $sid)
            ->update([
                'attended'  => 1,
                'marked_at' => now()
            ]);
    }

    // Guardar firma como texto
    DB::table('org_signatures')->insert([
        'attendance_list_id'   => $listId,
        'organization_user_id' => $user->id,
        'signature_path'       => null,
        'signature_text'       => $r->signature,
        'signed_at'            => now()
    ]);

    DB::table('attendance_lists')->where('id', $listId)->update([
        'status'     => 'enviada',
        'updated_at' => now()
    ]);

    return back()->with('status', 'Lista enviada. El administrador otorgará las horas.');
}


}
