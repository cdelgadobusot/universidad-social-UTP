@extends('layouts.app')
@section('title','Propuestas — Admin')

@section('content')
<div class="container" style="max-width:1100px">
  <div style="display:flex;align-items:center;gap:1rem;justify-content:space-between;margin-bottom:1rem">
    <h1 style="margin:0">Propuestas recibidas</h1>
    <a class="btn btn-secondary" href="{{ route('home') }}">Volver al inicio</a>
  </div>

  @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
  @if ($errors->any())
    <div class="alert alert-error">
      <ul style="margin:0 0 0 1rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card">
    <table class="table">
      <thead><tr>
        <th>Fecha</th>
        <th>Proponente</th>
        <th>Rol</th>
        <th>Lugar</th>
        <th>Fecha del evento</th>
        <th>Tipo trabajo</th>
        <th>Acción</th>
      </tr></thead>
      <tbody>
      @forelse($propuestas as $p)
        @php
          $defaultTitle = 'Trabajo social: '.$p->work_type;
          $defaultDesc  = 'Propuesta de '.$p->proposer_name.'. Tipo: '.$p->work_type.'.';
        @endphp
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d') }}</td>
          <td>{{ $p->proposer_name }}<br><small>{{ $p->proposer_email }}</small></td>
          <td>{{ $p->proposer_role }}</td>
          <td>{{ $p->place }}</td>
          <td>{{ $p->event_date }}</td>
          <td>{{ $p->work_type }}</td>
          <td>
            @if($p->status==='pendiente')
              <div style="display:grid;gap:.35rem">
                <button
                  class="btn btn-success js-open-accept"
                  data-id="{{ $p->id }}"
                  data-title="{{ $defaultTitle }}"
                  data-hours="1"
                  data-description="{{ $defaultDesc }}"
                >Aceptar y publicar</button>

                <form class="js-confirm" data-msg="¿Rechazar esta propuesta?"
                      method="POST" action="{{ route('admin.propuestas.decision',$p->id) }}">
                  @csrf
                  <input type="hidden" name="decision" value="rechazada">
                  <button class="btn btn-danger">Rechazar</button>
                </form>
              </div>
            @else
              <em>{{ ucfirst($p->status) }}</em>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="7">No hay propuestas.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Modal reutilizable de aceptar/publicar (mismo markup que en home_admin) --}}
<div id="modal-accept" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:1rem">
  <div style="background:#fff;max-width:640px;width:100%;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.2);">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center">
      <strong>Aceptar y publicar</strong>
      <button type="button" class="btn btn-secondary" id="modal-accept-close" style="padding:.25rem .5rem">×</button>
    </div>
    <form id="modal-accept-form" method="POST" action="#" style="padding:1rem 1.25rem;display:grid;gap:.65rem">
      @csrf
      <input type="hidden" name="decision" value="aprobada">
      <label>Título
        <input type="text" name="title" placeholder="Título de la actividad" required>
      </label>
      <label>Horas sociales
        <input type="number" name="social_hours" min="1" max="100" value="1" required>
      </label>
      <label>Descripción
        <textarea name="description" rows="4" placeholder="Descripción de la actividad"></textarea>
      </label>
      <div style="display:flex;gap:.5rem;margin-top:.5rem;justify-content:flex-end">
        <button type="button" class="btn btn-secondary" id="modal-accept-cancel">Cancelar</button>
        <button class="btn btn-success">Publicar actividad</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  // Confirmación genérica
  document.addEventListener('click', (e) => {
    const f = e.target.closest('form.js-confirm');
    if (!f) return;
    const msg = f.dataset.msg || '¿Confirmar acción?';
    if (!confirm(msg)) { e.preventDefault(); e.stopPropagation(); }
  }, true);

  // Modal aceptar/publicar
  const $accept = document.getElementById('modal-accept');
  const $acceptForm = document.getElementById('modal-accept-form');
  function openAccept({id, title, hours, description}) {
    $accept.style.display = 'flex';
    $acceptForm.action = "{{ url('/admin/propuestas/') }}/" + id + "/decision";
    $acceptForm.querySelector('[name=title]').value = title || '';
    $acceptForm.querySelector('[name=social_hours]').value = hours || 1;
    $acceptForm.querySelector('[name=description]').value = description || '';
  }
  function closeAccept(){ $accept.style.display='none'; }
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-accept'); if(!btn) return;
    openAccept({
      id: btn.dataset.id,
      title: btn.dataset.title,
      hours: btn.dataset.hours,
      description: btn.dataset.description
    });
  });
  document.getElementById('modal-accept-close')?.addEventListener('click', closeAccept);
  document.getElementById('modal-accept-cancel')?.addEventListener('click', closeAccept);
  $accept.addEventListener('click', (e)=>{ if(e.target===$accept) closeAccept(); });
</script>
@endpush
@endsection
