@extends('layouts.app')
@section('title','Registrar en Actividades')

@section('content')
<div class="container" style="max-width:900px">
  <h1 style="margin:0 0 .75rem 0">Registrar en Actividades</h1>

  @if (session('status'))
    <div class="alert alert-success" style="margin-bottom:1rem">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-error" style="margin-bottom:1rem">
      <ul style="margin:0 0 0 1rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @php
    $preSelected = old('activity_id', request('activity_id'));
  @endphp

  @if(($acts ?? collect())->count() === 0)
    <div class="card" style="padding:1rem">
      No hay convocatorias abiertas en este momento.
    </div>
    <div style="margin-top:1rem">
      <a class="btn btn-secondary" href="{{ route('home') }}">Volver al panel</a>
    </div>
  @else
    <form id="form-reg" class="card" method="POST" action="{{ route('registrar.actividades.submit') }}" enctype="multipart/form-data" style="display:grid;gap:.75rem">
      @csrf

      {{-- Selección de actividad --}}
      <div class="form-group">
        <label for="activity_id"><strong>Selecciona la actividad</strong></label>
        <select id="activity_id" name="activity_id" required>
          <option value="" disabled {{ $preSelected ? '' : 'selected' }}>— Elige una actividad —</option>
          @foreach($acts as $a)
            <option
              value="{{ $a->id }}"
              data-title="{{ $a->title }}"
              data-place="{{ $a->place }}"
              data-date="{{ $a->start_date }}"
              data-time="{{ $a->start_time }}"
              data-hours="{{ $a->social_hours }}"
              data-description='@json($a->description)'
              {{ (string)$a->id === (string)$preSelected ? 'selected' : '' }}
            >
              {{ $a->title }} — {{ $a->place }} ({{ $a->start_date }} {{ $a->start_time }})
            </option>
          @endforeach
        </select>
        @error('activity_id')<div class="text-error">{{ $message }}</div>@enderror
      </div>

      {{-- Detalle de la actividad seleccionada --}}
      <div id="act-details" class="card" style="border:1px solid #eee">
        <h3 style="margin:.5rem 0">Detalle de la actividad</h3>
        <div id="act-summary" style="margin-bottom:.5rem;color:#444">Selecciona una actividad para ver sus detalles.</div>
        <div>
          <h4 style="margin:.25rem 0 .35rem">Descripción</h4>
          <p id="act-desc" style="white-space:pre-wrap;margin:0;color:#333">—</p>
        </div>
      </div>

      {{-- Recibo --}}
      <div class="form-group">
        <label for="receipt"><strong>Recibo de matrícula (PDF/JPG/PNG)</strong></label>
        <input type="file" id="receipt" name="receipt" accept="application/pdf,image/*" required>
        @error('receipt')<div class="text-error">{{ $message }}</div>@enderror
        <small class="text-muted">Este archivo es necesario para validar tu inscripción.</small>
      </div>

      <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:flex-end">
        <a class="btn btn-secondary" href="{{ route('home') }}">Volver al panel</a>
        <button type="submit" class="btn btn-primary">Confirmar inscripción</button>
      </div>
    </form>
  @endif
</div>

@push('scripts')
<script>
(function(){
  const sel = document.getElementById('activity_id');
  const sum = document.getElementById('act-summary');
  const des = document.getElementById('act-desc');

  function renderDetails() {
    const opt = sel?.options[sel.selectedIndex];
    if (!opt || !opt.value) {
      sum.textContent = 'Selecciona una actividad para ver sus detalles.';
      des.textContent = '—';
      return;
    }
    const title = opt.dataset.title || 'Actividad';
    const place = opt.dataset.place || '—';
    const date  = opt.dataset.date  || '—';
    const time  = opt.dataset.time  || '';
    const hours = opt.dataset.hours ? ` — Horas: ${opt.dataset.hours}` : '';
    try {
      const desc = JSON.parse(opt.dataset.description || '""') || '';
      des.textContent = desc || '—';
    } catch(e) {
      des.textContent = opt.dataset.description || '—';
    }
    sum.innerHTML = `<strong>${title}</strong> — ${place} — ${date} ${time}${hours}`;
  }

  sel?.addEventListener('change', renderDetails);
  // Render inicial si vino preseleccionada por ?activity_id=
  renderDetails();
})();
</script>
@endpush
@endsection
