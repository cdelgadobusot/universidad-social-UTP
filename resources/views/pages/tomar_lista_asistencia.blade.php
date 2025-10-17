@extends('layouts.app')
@section('title','Tomar Lista de Asistencia')

@section('content')
<div class="container" style="max-width:900px">
  <h1 style="margin-bottom:1rem">Tomar Lista de Asistencia</h1>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-error">
      <ul style="margin:0 0 0 1rem">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  @php
    // Normaliza $lists a colección
    $lists = collect($lists ?? []);
  @endphp

  @if ($lists->isEmpty())
    <div class="card">
      <p style="margin:0">No tienes listas habilitadas para tomar asistencia por el momento.</p>
    </div>
    <p style="margin-top:1rem"><a class="btn btn-secondary" href="{{ route('home') }}">Volver al panel</a></p>

  @elseif ($lists->count() > 1 && !request()->filled('activity_id'))
    {{-- Varias listas: ofrece selector por actividad --}}
    <div class="card">
      <p style="margin-bottom:.75rem">Selecciona una actividad para tomar asistencia:</p>
      <table class="table">
        <thead>
          <tr>
            <th>Actividad</th>
            <th>Lugar</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($lists as $l)
            <tr>
              <td>{{ $l->title }}</td>
              <td>{{ $l->place }}</td>
              <td>{{ $l->start_date }}</td>
              <td>{{ $l->start_time }}</td>
              <td>
                <a class="btn btn-primary" href="{{ route('tomar.lista', ['activity_id' => $l->activity_id]) }}">
                  Abrir
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <p style="margin-top:1rem"><a class="btn btn-secondary" href="{{ route('home') }}">Volver al panel</a></p>

  @else
    {{-- Una sola lista (o filtrado por activity_id): muestra el formulario de marcado --}}
    @php
      $list = $lists->first();
      // Cargar participantes sincronizados para esta lista
      $entries = DB::table('attendance_entries as e')
        ->join('users as u','u.id','=','e.student_id')
        ->select('u.id','u.name','u.email','e.attended','e.marked_at')
        ->where('e.attendance_list_id',$list->id)
        ->orderBy('u.name')
        ->get();
    @endphp

    <div class="card" style="margin-bottom:1rem">
      <h3 style="margin:0 0 .25rem 0">{{ $list->title }}</h3>
      <p style="margin:0">
        <strong>Lugar:</strong> {{ $list->place }} —
        <strong>Fecha:</strong> {{ $list->start_date }} {{ $list->start_time }}
      </p>
    </div>

    <form id="form-lista" class="card" method="POST" action="{{ route('tomar.lista.submit') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="attendance_list_id" value="{{ $list->id }}">

      <table class="table">
        <thead>
          <tr>
            <th>Estudiante</th>
            <th>Email</th>
            <th>Asistió</th>
          </tr>
        </thead>
        <tbody>
          @forelse($entries as $e)
            <tr>
              <td>{{ $e->name }}</td>
              <td>{{ $e->email }}</td>
              <td>
                <input type="checkbox" name="attended[{{ $e->id }}]" value="1" {{ $e->attended ? 'checked' : '' }}>
              </td>
            </tr>
          @empty
            <tr><td colspan="3">No hay participantes sincronizados. Pide al administrador que habilite/sincronice la lista.</td></tr>
          @endforelse
        </tbody>
      </table>

      <div class="form-group">
        <label for="signature">Firma</label>
        <input id="signature" name="signature" type="text" maxlength="255" placeholder="Escriba su nombre completo como firma" required>
      </div>


      <button type="submit" class="btn btn-primary" id="btn-send">Enviar lista</button>
    </form>

    <p style="margin-top:1rem"><a class="btn btn-secondary" href="{{ route('home') }}">Volver al panel</a></p>

    @push('scripts')
    <script>
      document.getElementById('form-lista')?.addEventListener('submit', function(){
        const btn = document.getElementById('btn-send');
        btn.disabled = true; btn.textContent = 'Enviando...';
      });
    </script>
    @endpush
  @endif
</div>
@endsection

