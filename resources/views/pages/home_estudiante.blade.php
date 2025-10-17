@extends('layouts.app')
@section('title','Panel — Estudiante')

@section('content')
<div class="container" style="max-width:1100px">
  <h1 style="margin:0 0 .75rem 0">Panel del Estudiante</h1>
  @if (session('status'))
    <div class="alert alert-success" style="margin-bottom:1rem">
      {{ session('status') }}
    </div>
  @endif

  {{-- Horas acumuladas (más grande) --}}
  <div class="card" style="margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;padding:1.25rem">
    <div>
      <div style="font-size:.95rem;color:#666">Horas de labor social acumuladas</div>
      <div style="font-size:2.25rem;font-weight:800;line-height:1">{{ $totalHours }}</div>
    </div>
    <div style="opacity:.7">⏱️</div>
  </div>

  {{-- =================== CONVOCATORIAS ABIERTAS =================== --}}
  <section id="convocatorias" style="margin-bottom:2rem">
    <h2 style="margin:0 0 .5rem 0">Convocatorias abiertas</h2>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Actividad</th>
            <th>Lugar</th>
            <th>Fecha</th>
            <th>Ver</th>
            <th>Apuntarse</th>
          </tr>
        </thead>
        <tbody>
        @forelse($openActs as $a)
          @php $yaInscrito = in_array($a->id, $myRegIds ?? []); @endphp
          <tr>
            <td>{{ $a->title }}</td>
            <td>{{ $a->place }}</td>
            <td>{{ $a->start_date }} {{ $a->start_time }}</td>
            <td>
              <button class="btn btn-secondary js-open-details"
                      data-act='@json($a)'
                      data-cansignup="{{ $yaInscrito ? '0' : '1' }}">Ver</button>
            </td>
            <td>
              @if($yaInscrito)
                <button class="btn" disabled title="Ya estás inscrito">Apuntado</button>
              @else
                <button class="btn btn-success js-open-confirm"
                        data-activity-id="{{ $a->id }}"
                        data-activity-title="{{ $a->title }}">Apuntarse</button>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5">No hay actividades disponibles ahora mismo.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </section>


  {{-- =================== MIS ACTIVIDADES (ACTIVAS) =================== --}}
  @php
    // IDs de actividades con lista de asistencia CERRADA (consideraremos estas como "finalizadas")
    $finalIds = \Illuminate\Support\Facades\DB::table('attendance_lists')
                  ->where('status','cerrada')
                  ->pluck('activity_id')->unique()->all();

    // Mis actividades activas = donde estoy inscrito y NO está en $finalIds
    $activeMine = \Illuminate\Support\Facades\DB::table('activities as a')
        ->join('activity_registrations as r','r.activity_id','=','a.id')
        ->where('r.student_id', \Illuminate\Support\Facades\Auth::id())
        ->whereNotIn('a.id', $finalIds)
        ->select('a.*')
        ->orderBy('a.start_date')
        ->get();

    // Mis actividades finalizadas = donde estoy inscrito y SÍ está en $finalIds
    $finalMine = \Illuminate\Support\Facades\DB::table('activities as a')
        ->join('activity_registrations as r','r.activity_id','=','a.id')
        ->where('r.student_id', \Illuminate\Support\Facades\Auth::id())
        ->whereIn('a.id', $finalIds)
        ->select('a.*')
        ->orderByDesc('a.start_date')
        ->get();
  @endphp

  <section id="mis-activas" style="margin-bottom:2rem">
    <h2 style="margin:0 0 .5rem 0">Tus actividades activas</h2>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Actividad</th>
            <th>Lugar</th>
            <th>Fecha</th>
            <th>Ver</th>
          </tr>
        </thead>
        <tbody>
        @forelse($activeMine as $a)
          <tr>
            <td>{{ $a->title }}</td>
            <td>{{ $a->place }}</td>
            <td>{{ $a->start_date }} {{ $a->start_time }}</td>
            <td>
              <button class="btn btn-secondary js-open-details"
                      data-act='@json($a)'
                      data-cansignup="0">Ver</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="4">Aún no estás apuntado a actividades activas.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- =================== MIS ACTIVIDADES (FINALIZADAS) =================== --}}
  <section id="mis-finalizadas" style="margin-bottom:2rem">
    <h2 style="margin:0 0 .5rem 0">Tus actividades finalizadas</h2>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Actividad</th>
            <th>Lugar</th>
            <th>Fecha</th>
            <th>Ver</th>
          </tr>
        </thead>
        <tbody>
        @forelse($finalMine as $a)
          <tr>
            <td>{{ $a->title }}</td>
            <td>{{ $a->place }}</td>
            <td>{{ $a->start_date }} {{ $a->start_time }}</td>
            <td>
              <button class="btn btn-secondary js-open-details"
                      data-act='@json($a)'
                      data-cansignup="0">Ver</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="4">No tienes actividades finalizadas.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- Botón general para volver (por si el estudiante entra desde otra parte) --}}
  <div style="margin-top:.75rem">
    <a class="btn btn-secondary" href="{{ route('home') }}">Volver a la página principal</a>
  </div>
</div>

{{-- =================== MODALES (overlay centrado con animación) =================== --}}
<style>
  .modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    display: none; align-items: center; justify-content: center; z-index: 1000;
    padding: 1rem;
  }
  .modal-card {
    background: #fff; width: 100%; max-width: 720px;
    border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.25);
    transform: translateY(12px) scale(.98); opacity: 0;
    transition: transform .22s ease, opacity .22s ease;
  }
  .modal-overlay.show { display: flex; }
  .modal-overlay.show .modal-card { transform: translateY(0) scale(1); opacity: 1; }
  .modal-header {
    padding: 1rem 1.25rem; border-bottom: 1px solid #eee; display:flex; align-items:center; justify-content:space-between;
  }
  .modal-body { padding: 1rem 1.25rem; }
  .modal-footer { padding: 0 1.25rem 1rem; display:flex; gap:.5rem; justify-content:flex-end; flex-wrap:wrap; }
</style>

<div id="student-modal" class="modal-overlay" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="student-modal-title">
    <div class="modal-header">
      <strong id="student-modal-title">Detalle</strong>
      <button type="button" class="btn btn-secondary" id="student-modal-close" style="padding:.25rem .5rem">×</button>
    </div>
    <div id="student-modal-body" class="modal-body"></div>
    <div id="student-modal-footer" class="modal-footer"></div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const $overlay = document.getElementById('student-modal');
  const $body    = document.getElementById('student-modal-body');
  const $footer  = document.getElementById('student-modal-footer');
  const $title   = document.getElementById('student-modal-title');
  const close    = ()=>{ $overlay.classList.remove('show'); setTimeout(()=>{ $overlay.style.display='none'; }, 200); };
  const open     = ()=>{ $overlay.style.display='flex'; requestAnimationFrame(()=> $overlay.classList.add('show')); };

  document.getElementById('student-modal-close')?.addEventListener('click', close);
  $overlay.addEventListener('click', (e)=>{ if(e.target === $overlay) close(); });

  // === Confirmación de apuntarse ===
  function openConfirm(activityId, title){
    $title.textContent = 'Confirmar inscripción';
    $body.innerHTML = `
      <p><strong>${title}</strong></p>
      <p style="margin:.5rem 0 0">Al inscribirte, aceptas las siguientes responsabilidades:</p>
      <ul style="margin:.35rem 0 0 1rem">
        <li>Asistir puntualmente a la actividad y cumplir con las indicaciones del encargado.</li>
        <li>Mantener una conducta adecuada y representar a la universidad con respeto.</li>
        <li>Notificar con antelación si surgiera alguna imposibilidad real de asistir.</li>
      </ul>
      <p style="margin:.5rem 0 0"><strong>Importante:</strong> No asistir luego de inscribirte puede implicar no otorgar horas, limitar futuras inscripciones y/o reporte a coordinación.</p>
      <p style="margin:.5rem 0 0">Para completar la inscripción se te solicitará cargar tu recibo de matrícula.</p>
    `;
    $footer.innerHTML = `
      <a class="btn btn-secondary" href="{{ route('home') }}">Volver a la página principal</a>
      <button type="button" class="btn btn-secondary" id="btn-cancel">Cancelar</button>
      <button type="button" class="btn btn-success" id="btn-accept">Aceptar y continuar</button>
    `;
    open();
    document.getElementById('btn-cancel')?.addEventListener('click', close);
    document.getElementById('btn-accept')?.addEventListener('click', () => {
      // Redirige al formulario de inscripción con el activity_id preseleccionado (si lo manejas allí)
      window.location.href = "{{ route('registrar.actividades') }}?activity_id=" + encodeURIComponent(activityId);
    });
  }

  // === Detalles de actividad (con botón apuntarse opcional) ===
  function openDetails(act, canSignUp){
    $title.textContent = 'Detalle de la actividad';
    const hours = (act.social_hours ?? '') ? ` — <strong>Horas:</strong> ${act.social_hours}` : '';
    const time  = (act.start_time ?? '') ? ` ${act.start_time}` : '';
    $body.innerHTML = `
      <h3 style="margin:.25rem 0 .5rem">${act.title ?? 'Actividad'}</h3>
      <p><strong>Lugar:</strong> ${act.place ?? '—'} — <strong>Fecha:</strong> ${act.start_date ?? '—'}${time}${hours}</p>
      <div class="card" style="margin:.5rem 0 1rem">
        <h4 style="margin:.25rem 0 .35rem">Descripción</h4>
        <p style="white-space:pre-wrap">${act.description ?? '—'}</p>
      </div>
    `;
    $footer.innerHTML = `
      <a class="btn btn-secondary" href="{{ route('home') }}">Volver a la página principal</a>
      ${canSignUp ? `<button type="button" class="btn btn-success" id="btn-signup">Apuntarse</button>` : ''}
      <button type="button"class="btn btn-secondary" id="btn-close">Cerrar</button>
    `;
    open();
    document.getElementById('btn-close')?.addEventListener('click', close);
    const btn = document.getElementById('btn-signup');
    if (btn) {
      btn.addEventListener('click', () => openConfirm(act.id, act.title));
    }
  }

  // Delegación: abrir modal de detalles
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-details');
    if (!btn) return;
    try {
      const act = JSON.parse(btn.dataset.act);
      const canSign = btn.dataset.cansignup === '1';
      openDetails(act, canSign);
    } catch(err) {}
  });

  // Delegación: abrir modal de confirmación de inscripción (desde tabla)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-confirm');
    if (!btn) return;
    const id = btn.dataset.activityId;
    const t  = btn.dataset.activityTitle || 'Actividad';
    openConfirm(id, t);
  });
})();
</script>
@endpush
@endsection
