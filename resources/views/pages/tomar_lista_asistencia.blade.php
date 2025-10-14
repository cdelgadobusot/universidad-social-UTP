@extends('layouts.app')
@section('title','Tomar Lista de Asistencia')
@section('content')
  <h1>Tomar Lista de Asistencia</h1>

  <form id="form-lista" class="card" method="POST" action="{{ route('tomar.lista.submit') }}" data-success="Se ha enviado la lista de asistencia correctamente.">
    @csrf
    <table class="table">
      <thead><tr><th>Participante</th><th>Asiste</th></tr></thead>
      <tbody>
        @foreach($participantes as $i => $p)
          <tr><td>{{ $p }}</td><td><input type="checkbox" name="asiste{{ $i+1 }}"></td></tr>
        @endforeach
      </tbody>
    </table>
    <div class="form-group"><label for="firma">Firma digital</label><input id="firma" name="firma" required placeholder="Dibuja o escribe tu firma" /></div>
    <button type="submit" class="btn btn-primary">Enviar lista</button>
  </form>

  <p><a class="btn btn-secondary" href="{{ route('home') }}">Página Principal</a></p>
@endsection
