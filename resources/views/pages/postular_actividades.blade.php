@extends('layouts.app')
@section('title','Postular Actividades')
@section('content')
  <h1>Postular Actividades</h1>
  <p>Seleccione su rol para continuar.</p>

  <div class="grid-2">
    <form id="form-prof" class="card" method="POST" action="{{ route('postular.actividades.submit') }}" data-success="Actividad postulada; un trabajador de la DSSU se pondrá en contacto contigo pronto, vía telefónica, para confirmar la aceptación de la actividad.">
      @csrf
      <h2>Formulario para Profesor</h2>
      <div class="form-group"><label for="prof-lugar">Lugar</label><input id="prof-lugar" name="lugar" required /></div>
      <div class="form-group"><label for="prof-fecha">Fecha</label><input type="date" id="prof-fecha" name="fecha" required /></div>
      <div class="form-group"><label for="prof-participantes">Cantidad de participantes</label><input type="number" id="prof-participantes" name="participantes" min="1" required /></div>
      <div class="form-group"><label for="prof-tipo">Tipo de trabajo social</label><input id="prof-tipo" name="tipo" required /></div>
      <div class="form-group"><label for="prof-permisos">Permisos requeridos</label><textarea id="prof-permisos" name="permisos" rows="2"></textarea></div>
      <div class="form-group"><label for="prof-datos">Datos del encargado</label><textarea id="prof-datos" name="datos" rows="2" required></textarea></div>
      <div class="form-group"><label for="prof-firma">Firma digital</label><input id="prof-firma" name="firma" required /></div>
      <button type="submit" class="btn btn-primary">Postular</button>
    </form>

    <form id="form-org" class="card" method="POST" action="{{ route('postular.actividades.submit') }}" data-success="Actividad postulada; un trabajador de la DSSU se pondrá en contacto contigo pronto, vía telefónica, para confirmar la aceptación de la actividad.">
      @csrf
      <h2>Formulario para Organismo</h2>
      <div class="form-group"><label for="org-lugar">Lugar</label><input id="org-lugar" name="lugar" required /></div>
      <div class="form-group"><label for="org-fecha">Fecha</label><input type="date" id="org-fecha" name="fecha" required /></div>
      <div class="form-group"><label for="org-participantes">Cantidad de participantes</label><input type="number" id="org-participantes" name="participantes" min="1" required /></div>
      <div class="form-group"><label for="org-tipo">Tipo de trabajo social</label><input id="org-tipo" name="tipo" required /></div>
      <div class="form-group"><label for="org-permisos">Permisos requeridos</label><textarea id="org-permisos" name="permisos" rows="2"></textarea></div>
      <div class="form-group"><label for="org-datos">Datos del encargado</label><textarea id="org-datos" name="datos" rows="2" required></textarea></div>
      <div class="form-group"><label for="org-firma">Firma digital</label><input id="org-firma" name="firma" required /></div>
      <button type="submit" class="btn btn-primary">Postular</button>
    </form>
  </div>

  <p><a class="btn btn-secondary" href="{{ route('home') }}">Regresar a Página Principal</a></p>
@endsection
