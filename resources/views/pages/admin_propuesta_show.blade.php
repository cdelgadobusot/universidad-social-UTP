@extends('layouts.app')
@section('title','Propuesta — Admin')

@section('content')
<div class="container" style="max-width:900px">
  <h1 style="margin-bottom:.5rem">Detalle de Propuesta</h1>
  <p><strong>Proponente:</strong> {{ $p->proposer_name }} <small>({{ $p->proposer_email }})</small></p>
  <p><strong>Rol:</strong> {{ $p->proposer_role }} — <strong>Lugar:</strong> {{ $p->place }} — <strong>Evento:</strong> {{ $p->event_date }}</p>
  <p><strong>Tipo de trabajo:</strong> {{ $p->work_type }} — <strong>Participantes:</strong> {{ $p->participants_count }}</p>
  <p><strong>Estado:</strong> {{ $p->status }}</p>

  <div class="card" style="margin:1rem 0">
    <h3 style="margin:.25rem 0 .5rem">Descripción de la propuesta</h3>
    <p style="white-space:pre-wrap">{{ $p->description }}</p>
  </div>

  @php
    $md = @json_decode($p->manager_data, true) ?: [];
  @endphp
  <p><strong>Encargado:</strong> {{ $md['encargado'] ?? '—' }} — <strong>Tel:</strong> {{ $md['telefono'] ?? '—' }}</p>

  @if ($p->signature_path)
    <p><strong>Documento del encargado:</strong> {{ $p->signature_path }}</p>
  @endif

  <div style="margin-top:1rem">
    <a href="{{ route('home') }}" class="btn btn-secondary">Volver al panel</a>
  </div>
</div>
@endsection
