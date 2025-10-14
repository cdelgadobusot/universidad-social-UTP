@extends('layouts.app')

@section('title','Universidad Social – Inicio')

@section('content')
  <h1>Bienvenido a Universidad Social</h1>
  <p>Gestione actividades de servicio social y voluntariado de manera sencilla.</p>

  <section class="grid-3">
    <a href="{{ route('postular.actividades') }}" class="card">
      <h2>Postular Actividades</h2>
      <p>Profesores y organismos pueden proponer nuevas actividades.</p>
    </a>
    <a href="{{ route('registrar.actividades') }}" class="card">
      <h2>Registrar en Actividades</h2>
      <p>Estudiantes pueden apuntarse a actividades disponibles.</p>
    </a>
    <a href="{{ route('crear.lista') }}" class="card">
      <h2>Crear Lista de Asistencia</h2>
      <p>Trabajadores de DSSU pueden organizar participantes.</p>
    </a>
    <a href="{{ route('tomar.lista') }}" class="card">
      <h2>Tomar Lista de Asistencia</h2>
      <p>Organismos receptores pueden confirmar asistencia.</p>
    </a>
    <a href="{{ route('ingresar.horas') }}" class="card">
      <h2>Ingresar Horas</h2>
      <p>Actualiza las horas completadas por los estudiantes.</p>
    </a>
  </section>
@endsection
