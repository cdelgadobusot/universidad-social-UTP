@extends('layouts.app')
@section('title','Postular Actividades')

@section('content')
<div class="container" style="max-width:820px">
  <h1 style="margin-bottom:.75rem">Postular Actividades</h1>
  <p style="margin:0 0 1rem 0">
    Completa los datos de la propuesta. El documento debe ser del encargado (Pasaporte, Cédula o Licencia).
  </p>

  @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
  @if ($errors->any())
    <div class="alert alert-error">
      <ul style="margin:0 0 0 1rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('postular.actividades.submit') }}" class="card" enctype="multipart/form-data">
    @csrf
    <div class="grid-2">
      <div class="form-group">
        <label for="place">Lugar</label>
        <input id="place" name="place" required maxlength="255" />
      </div>

      <div class="form-group">
        <label for="event_date">Fecha del evento</label>
        <input type="date" id="event_date" name="event_date" required />
      </div>

      <div class="form-group">
        <label for="participants_count">Cantidad de participantes</label>
        <input type="number" id="participants_count" name="participants_count" min="1" required />
      </div>

      <div class="form-group">
        <label for="work_type">Tipo de trabajo social</label>
        <input id="work_type" name="work_type" required maxlength="120" />
      </div>
    </div>

    <div class="form-group">
      <label for="proposal_description"><strong>Descripción detallada de la actividad</strong></label>
      <textarea id="proposal_description" name="proposal_description" rows="5" required placeholder="Describa objetivos, alcance, tareas, logística, etc."></textarea>
    </div>

    <div class="form-group">
      <label for="permits">Permisos requeridos</label>
      <textarea id="permits" name="permits" rows="2" placeholder="(Opcional)"></textarea>
    </div>

    <div class="grid-2">
      <div class="form-group">
        <label for="manager_name">Nombre del encargado</label>
        <input id="manager_name" name="manager_name" required maxlength="120" />
      </div>
      <div class="form-group">
        <label for="manager_phone">Teléfono del encargado</label>
        <input id="manager_phone" name="manager_phone" maxlength="50" placeholder="(Opcional)" />
      </div>
    </div>

    <div class="form-group">
      <label for="manager_doc"><strong>Documento personal del encargado</strong> (Pasaporte/Cédula/Licencia)</label>
      <input type="file" id="manager_doc" name="manager_doc" accept="application/pdf,image/png,image/jpeg" required />
      <small class="text-muted">Formatos: PDF, JPG, PNG. Máx. 2MB.</small>
    </div>

    <button class="btn btn-primary">Postular</button>
    <a class="btn btn-secondary" href="{{ route('home') }}">Regresar</a>
  </form>
</div>
@endsection
