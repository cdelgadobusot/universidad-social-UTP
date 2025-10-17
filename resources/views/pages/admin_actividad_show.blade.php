@extends('layouts.app')
@section('title','Actividad — Admin')

@section('content')
<div class="container" style="max-width:1100px">
  <div style="display:flex;align-items:center;gap:1rem;justify-content:space-between;margin-bottom:1rem">
    <h1 style="margin:0">{{ $act->title }}</h1>
    {{-- Sin botón superior adicional: navbar/logo llevan al panel --}}
  </div>

  {{-- Proponente (si existe) --}}
  @if(!empty($prop))
    <p>
      <strong>Proponente:</strong>
      {{ $prop->proposer_name ?? '—' }}
      <small>&lt;{{ $prop->proposer_email ?? '' }}&gt;</small>
      — <strong>Rol:</strong> {{ $prop->proposer_role ?? '—' }}
    </p>
  @endif

  {{-- Estado / cálculo local para evitar "Undefined variable" --}}
  @php
    $lista = $list ?? null;
    $estaFinalizada = $lista && $lista->status === 'cerrada';

    // Estado visible:
    // - Si finalizada (lista cerrada) => "Finalizada"
    // - Si no, y activity.status === 'cerrada' => "Convocatoria cerrada y actividad activa"
    // - En otro caso => activity.status tal cual.
    if ($estaFinalizada) {
        $estadoTexto = 'Finalizada';
    } elseif ($act->status === 'cerrada') {
        $estadoTexto = 'Convocatoria cerrada y actividad activa';
    } else {
        $estadoTexto = $act->status;
    }
  @endphp

  <p>
    <strong>Lugar:</strong> {{ $act->place }} —
    <strong>Fecha:</strong> {{ $act->start_date }} {{ $act->start_time }}
  </p>
  <p>
    <strong>Estado:</strong> {{ $estadoTexto }} —
    <strong>Horas:</strong> {{ $act->social_hours }} —
    <strong>Lista habilitada:</strong> {{ $act->attendance_enabled ? 'Sí' : 'No' }}
  </p>

  {{-- Descripción de la actividad --}}
  <div class="card" style="margin:.75rem 0 1rem">
    <h3 style="margin:.25rem 0 .5rem">Descripción de la actividad</h3>
    <p style="white-space:pre-wrap">{{ $act->description }}</p>
  </div>

  @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
  @if ($errors->any())
    <div class="alert alert-error">
      <ul style="margin:0 0 0 1rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <hr>

  <h3>Inscritos</h3>

  @unless($estaFinalizada)
  <form method="POST" action="{{ route('admin.actividad.addStudent',$act->id) }}"
        style="display:flex;gap:.5rem;align-items:center;margin-bottom:.75rem">
    @csrf

    <div>
      <input id="studentFilter" type="text" placeholder="Buscar estudiante por nombre o email"
             style="min-width:320px" />
      <select id="studentSelect" name="student_id" style="min-width:320px;margin-top:.35rem">
        @foreach($studentsAll as $s)
          <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->email }}</option>
        @endforeach
      </select>
    </div>

    <button class="btn btn-secondary">Agregar estudiante</button>
  </form>
  @endunless

  <table class="table">
    <thead><tr><th>Nombre</th><th>Email</th><th>Estado reg.</th><th>Acciones</th></tr></thead>
    <tbody>
      @foreach($inscritos as $i)
        <tr>
          <td>{{ $i->name }}</td>
          <td>{{ $i->email }}</td>
          <td>{{ $i->status }}</td>
          <td>
            @unless($estaFinalizada)
            <form class="js-confirm" data-msg="¿Quitar este estudiante?"
                  method="POST" action="{{ route('admin.actividad.removeStudent',[$act->id]) }}">
              @csrf
              <input type="hidden" name="student_id" value="{{ $i->id }}">
              <button class="btn btn-danger">Quitar</button>
            </form>
            @endunless
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  @unless($estaFinalizada)
  <div style="display:flex;gap:1rem;flex-wrap:wrap;margin:1rem 0">
    @if($act->status==='publicada')
      <form class="js-confirm" data-msg="¿Cerrar convocatoria para nuevos inscritos?"
            method="POST" action="{{ route('admin.actividad.close',$act->id) }}">@csrf
        <button class="btn btn-success">Cerrar convocatoria</button>
      </form>
    @endif

    @if($act->status==='cerrada' && !$act->attendance_enabled)
      <form class="js-confirm" data-msg="¿Habilitar la lista de asistencia para el organizador/profesor?"
            method="POST" action="{{ route('admin.actividad.enableAttendance',$act->id) }}">@csrf
        <button class="btn btn-primary">Habilitar “Tomar lista”</button>
      </form>
    @endif

    @if($list && $list->status!=='cerrada')
      <form class="js-confirm" data-msg="¿Cerrar la lista de asistencia?"
            method="POST" action="{{ route('admin.actividad.closeAttendance',$act->id) }}">@csrf
        <button class="btn btn-warning">Cerrar lista de asistencia</button>
      </form>
    @endif

    @if($list && $list->status==='cerrada')
      <form class="js-confirm" data-msg="¿Finalizar actividad y otorgar horas a asistentes?"
            method="POST" action="{{ route('admin.actividad.finalize',$act->id) }}">@csrf
        <button class="btn btn-success">Finalizar actividad</button>
      </form>
    @endif
  </div>
  @endunless

  @if($list)
    <hr>
    <h3>Asistencia (lista #{{ $list->id }} — estado: {{ $list->status }})</h3>
    <table class="table">
      <thead><tr><th>Estudiante</th><th>Asistió</th><th>Marcado</th></tr></thead>
      <tbody>
        @foreach($asistentes as $a)
          <tr>
            <td>{{ $a->name }}</td>
            <td>{{ $a->attended ? 'Sí' : 'No' }}</td>
            <td>{{ $a->marked_at }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Si necesitas la vía manual de "Otorgar horas" cuando la lista está enviada pero no cerrada --}}
    @if(!$estaFinalizada && $list->status==='enviada')
      <form class="js-confirm" data-msg="¿Otorgar horas a asistentes?"
            method="POST" action="{{ route('admin.actividad.awardHours',$act->id) }}" style="display:flex;gap:.5rem;align-items:center">
        @csrf
        <input type="number" name="hours" min="1" max="100" placeholder="Horas (defecto {{ $act->social_hours }})">
        <button class="btn btn-success">Otorgar horas a asistentes</button>
      </form>
    @endif
  @endif

  {{-- Botón fijo al final para volver al panel --}}
  <div style="margin-top:1.25rem">
    <a class="btn btn-secondary" href="{{ route('home') }}">Volver al panel</a>
  </div>
</div>

@push('scripts')
<script>
  // Confirmación genérica
  document.addEventListener('click', (e) => {
    const f = e.target.closest('form.js-confirm');
    if (!f) return;
    const msg = f.dataset.msg || '¿Confirmar?';
    if (!confirm(msg)) { e.preventDefault(); e.stopPropagation(); }
  }, true);

  // Búsqueda simple sobre el <select> (solo cuando no está finalizada)
  const filter = document.getElementById('studentFilter');
  const sel = document.getElementById('studentSelect');
  if (filter && sel) {
    const ALL = Array.from(sel.options).map(o => ({value:o.value, text:o.text}));
    filter.addEventListener('input', () => {
      const q = filter.value.trim().toLowerCase();
      sel.innerHTML = '';
      ALL.filter(o => o.text.toLowerCase().includes(q))
         .forEach(o => {
           const opt = document.createElement('option');
           opt.value = o.value; opt.textContent = o.text;
           sel.appendChild(opt);
         });
    });
  }
</script>
@endpush
@endsection
