@extends('layouts.app')
@section('title','Ingresar Horas')

@section('content')
<div class="container animate-fade-in">
  <h1>Ingresar Horas</h1>

  {{-- Mensaje de éxito del backend --}}
  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  <div class="card">
    <input id="buscar" placeholder="Buscar estudiante…" style="width:100%;margin-bottom:1rem" />

    <ul id="listado" style="list-style:none;padding-left:0;margin:0">
      @foreach ($estudiantes as $i => $e)
        <li style="margin:0 0 .5rem">
          <a href="#" data-index="{{ $i }}">{{ $e['nombre'] }}</a>
        </li>
      @endforeach
    </ul>
  </div>

  {{-- Modal para editar horas --}}
  <div id="modal-horas" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content">
      <button class="modal-close" aria-label="Cerrar">×</button>
      <h2 id="modal-title" style="margin-bottom:1rem"></h2>

      <form id="form-horas" method="POST" action="{{ route('ingresar.horas.submit') }}">
        @csrf
        <input type="hidden" id="nombre" name="nombre" />

        <div class="form-group">
          <label for="horas-servicio">Horas de servicio social</label>
          <input type="number" id="horas-servicio" name="servicio" min="0" required />
        </div>

        <div class="form-group">
          <label for="horas-voluntariado">Horas de voluntariado</label>
          <input type="number" id="horas-voluntariado" name="voluntariado" min="0" required />
        </div>

        <button type="submit" class="btn btn-primary" id="btn-guardar">Guardar Cambios</button>
      </form>
    </div>
  </div>
</div>

{{-- ================== DATOS PARA JS ================== --}}
<script id="estudiantes-data" type="application/json">
  {!! json_encode($estudiantes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

{{-- ================== SCRIPT PRINCIPAL ================== --}}
<script>
(function(){
  // Utilidades rápidas
  const qs = (sel, scope = document) => scope.querySelector(sel);

  // Cargar datos desde el JSON embebido
  const raw = document.getElementById('estudiantes-data').textContent;
  const estudiantes = JSON.parse(raw);

  // Elementos del DOM
  const listado = qs('#listado');
  const buscar = qs('#buscar');
  const modal = qs('#modal-horas');
  const closeBtn = qs('.modal-close', modal);
  const titleEl = qs('#modal-title');
  const nombre = qs('#nombre');
  const servInp = qs('#horas-servicio');
  const volInp = qs('#horas-voluntariado');
  const form = qs('#form-horas');
  const btn = qs('#btn-guardar');

  // Renderizar lista filtrada
  function renderList(filter = '') {
    const f = filter.toLowerCase();
    let html = '';
    estudiantes.forEach((e, i) => {
      if (e.nombre.toLowerCase().includes(f)) {
        html += `<li style="margin:0 0 .5rem">
                   <a href="#" data-index="${i}">${e.nombre}</a>
                 </li>`;
      }
    });
    listado.innerHTML = html || '<li style="color:#666">Sin resultados…</li>';
  }
  renderList();

  // Filtro en tiempo real
  buscar.addEventListener('input', e => renderList(e.target.value));

  // Abrir modal con los datos del estudiante
  listado.addEventListener('click', (e) => {
    if (e.target.tagName === 'A') {
      e.preventDefault();
      const i = Number(e.target.dataset.index);
      const est = estudiantes[i];
      titleEl.textContent = est.nombre;
      nombre.value = est.nombre;
      servInp.value = est.servicio;
      volInp.value = est.voluntariado;
      modal.classList.add('active');
    }
  });

  // Cerrar modal
  closeBtn.addEventListener('click', () => modal.classList.remove('active'));

  // Evitar doble envío del formulario
  form.addEventListener('submit', () => {
    btn.disabled = true;
    btn.textContent = 'Guardando…';
  });
})();
</script>
@endsection
