@extends('layouts.app')
@section('title','Crear Lista de Asistencia')
@section('content')
  <h1>Crear Lista de Asistencia</h1>

  <section id="actividades" class="grid-2">
    @foreach($actividades as $act)
      <div class="card">
        <h2>{{ $act['titulo'] }}</h2>
        <p>Fecha: {{ $act['fecha'] }}</p>
        <button class="btn btn-primary" data-open-modal data-id="{{ $act['id'] }}">Crear Lista</button>
      </div>
    @endforeach
  </section>

  <div id="modal-lista" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content">
      <button class="modal-close" aria-label="Cerrar">×</button>
      <h2>Lista de Estudiantes</h2>
      <div id="lista-estudiantes"></div>
      <div style="display:flex;justify-content:flex-end;gap:1rem;margin-top:1.5rem">
        <form method="POST" action="{{ route('crear.lista.borrador') }}">@csrf
          <button id="btn-borrador" class="btn btn-secondary" type="submit">Guardar Borrador</button>
        </form>
        <form method="POST" action="{{ route('crear.lista.compartir') }}">@csrf
          <button id="btn-compartir" class="btn btn-primary" type="submit">Compartir lista a organismos receptores</button>
        </form>
      </div>
    </div>
  </div>

  <p><a class="btn btn-secondary" href="{{ route('home') }}">Página Principal</a></p>
@endsection
