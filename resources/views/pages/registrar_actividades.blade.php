@extends('layouts.app')
@section('title','Registrar en Actividades')
@section('content')
  <h1>Registrar en Actividades</h1>

  <form id="form-reg" class="card" method="POST" action="{{ route('registrar.actividades.submit') }}" enctype="multipart/form-data" data-success="Te has registrado en la actividad correctamente.">
    @csrf
    <div class="form-group"><label for="titulo">Título de la actividad</label><input id="titulo" name="titulo" required /></div>
    <div class="form-group"><label for="descripcion">Descripción</label><textarea id="descripcion" name="descripcion" rows="3" required></textarea></div>
    <div class="form-group"><label for="fecha">Fecha</label><input type="date" id="fecha" name="fecha" required /></div>
    <div class="form-group"><label for="lugar">Lugar</label><input id="lugar" name="lugar" required /></div>
    <div class="form-group"><label for="recibo">Recibo de matrícula</label><input type="file" id="recibo" name="recibo" accept="image/*,application/pdf" required /></div>
    <button type="submit" class="btn btn-primary">Enviar</button>
  </form>

  <p><a class="btn btn-secondary" href="{{ route('home') }}">Menú Principal</a></p>
@endsection
