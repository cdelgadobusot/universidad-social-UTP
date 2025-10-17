@extends('layouts.app')
@section('title','Ingresar Horas')

@section('content')
<div class="container animate-fade-in" style="max-width:900px">
  <h1 style="margin-bottom:.75rem">Horas de labor social</h1>

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  <div class="card" style="padding:1rem 1.25rem">
    <p style="font-size:1.05rem;line-height:1.5">
      A partir de ahora, las horas se <strong>asignan automáticamente</strong> a los estudiantes que
      <strong>aparecen como asistentes</strong> cuando el administrador <strong>cierra la lista de asistencia</strong>.
    </p>
    <ul style="margin:.5rem 0 0 1.25rem">
      <li>El sistema registra las horas configuradas en la actividad (<em>Horas sociales</em>).</li>
      <li>No es necesario cargar horas manualmente.</li>
      <li>El panel mostrará un mensaje de confirmación con cuántos estudiantes recibieron horas.</li>
    </ul>
  </div>

  <div style="margin-top:1rem">
    <a class="btn btn-secondary" href="{{ route('home') }}">Volver al panel</a>
  </div>
</div>
@endsection
