```php
{{-- resources/views/pages/home_admin.blade.php --}}
@extends('layouts.app')
@section('title','Panel — Administración DSSU')

@section('content')
<div class="container">
  <h1 style="margin-bottom:1rem">Panel de Administración</h1>


  {{-- ===================== 1) PENDIENTES ===================== --}}
  <section id="pendientes" style="margin-bottom:2rem">
    <h2 style="margin-bottom:.5rem">Propuestas pendientes</h2>
    @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    @if ($errors->any())
      <div class="alert alert-error">
        <ul style="margin:0 0 0 1rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <table class="table">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Proponente</th>
          <th>Rol</th>
          <th>Lugar</th>
          <th>Fecha del evento</th>
          <th>Tipo trabajo</th>
          <th>Descripción</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
      @php $pend = $pendientes ?? collect(); @endphp
      @forelse($pend as $p)
        @php
          $proposer = \Illuminate\Support\Facades\DB::table('users')
                      ->select('name','email')->where('id',$p->proposer_user_id)->first();
          $defaultTitle = 'Trabajo social: '.$p->work_type;
          $defaultDesc  = 'Propuesta de '.($proposer->name ?? '—').'. Tipo: '.$p->work_type.'.';
        @endphp
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d') }}</td>
          <td>{{ $proposer->name ?? '—' }}<br><small>{{ $proposer->email ?? '' }}</small></td>
          <td>{{ $p->proposer_role }}</td>
          <td>{{ $p->place }}</td>
          <td>{{ $p->event_date }}</td>
          <td>{{ $p->work_type }}</td>
          <td>
            <button class="btn btn-secondary" onclick="openDesc('desc-{{ $p->id }}')">Ver descripción</button>
            <template id="desc-{{ $p->id }}">
              <h3 style="margin-top:0">Descripción de la propuesta</h3>
              <p style="white-space:pre-wrap">{{ $p->description }}</p>
            </template>
          </td>
          <td>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
              {{-- Aceptar (abre modal centrado con animación) --}}
              <button
                class="btn btn-success"
                onclick="openApprove(this)"
                data-id="{{ $p->id }}"
                data-title="{{ $defaultTitle }}"
                data-hours="1"
                data-description="{{ $defaultDesc }}"
              >Aceptar y publicar</button>

              {{-- Rechazar con confirmación --}}
              <form method="POST" action="{{ route('admin.propuestas.decision',$p->id) }}"
                    onsubmit="return confirm('¿Rechazar esta propuesta?');">
                @csrf
                <input type="hidden" name="decision" value="rechazada">
                <button class="btn btn-danger">Rechazar</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="8">No hay propuestas pendientes.</td></tr>
      @endforelse
      </tbody>
    </table>
  </section>

  {{-- ===================== 2) ACTIVAS ===================== --}}
  <section id="activas" style="margin-bottom:2rem">
    <h2 style="margin-bottom:.5rem">Actividades activas</h2>

    <table class="table">
      <thead>
        <tr>
          <th>Actividad</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
      @php
        // Trae actividades publicadas (abiertas a estudiantes) y las que están "cerrada" de convocatoria
        // pero que AÚN no tienen la lista de asistencia cerrada (no finalizadas).
        $activasActs = \Illuminate\Support\Facades\DB::table('activities')
            ->whereIn('status', ['publicada','cerrada'])
            ->orderByDesc('created_at')
            ->get();

        $mostradas = 0;
      @endphp

      @foreach($activasActs as $a)
        @php
          $list = \Illuminate\Support\Facades\DB::table('attendance_lists')
                  ->where('activity_id', $a->id)
                  ->orderByDesc('id')->first();
          // excluir las que ya tienen lista cerrada (esas van a "finalizadas")
          $excluir = $list && $list->status === 'cerrada';
        @endphp

        @unless($excluir)
          @php $mostradas++; @endphp
          <tr>
            <td>{{ $a->title }}</td>
            <td>{{ $a->status }}</td>
            <td>{{ $a->start_date }}</td>
            <td>
              <a class="btn btn-primary" href="{{ route('admin.actividad.show', $a->id) }}">Ver</a>
            </td>
          </tr>
        @endunless
      @endforeach

      @if($mostradas === 0)
        <tr><td colspan="4">No hay actividades activas.</td></tr>
      @endif
      </tbody>
    </table>
  </section>


  {{-- ===================== 3) FINALIZADAS / RECHAZADAS ===================== --}}
  <section id="finalizadas" style="margin-bottom:2rem">
    <h2 style="margin-bottom:.5rem">Propuestas finalizadas o rechazadas</h2>

    @php
      // === LÓGICA CONSERVADA: construir $finales aquí ===
      $finales = collect();

      // Rechazadas
      $rech = \Illuminate\Support\Facades\DB::table('activity_proposals')
              ->where('status','rechazada')->orderByDesc('updated_at')->get();
      foreach ($rech as $p) { $p->_final_state = 'rechazada'; $finales->push($p); }

      // Aprobadas con lista CERRADA => finalizadas
      $aprob = \Illuminate\Support\Facades\DB::table('activity_proposals')
               ->where('status','aprobada')->orderByDesc('updated_at')->get();
      foreach ($aprob as $p) {
        if (!$p->activity_id) continue;
        $list = \Illuminate\Support\Facades\DB::table('attendance_lists')
                  ->where('activity_id',$p->activity_id)->orderByDesc('id')->first();
        if ($list && $list->status==='cerrada') {
          $p->_final_state = 'finalizada';
          $finales->push($p);
        }
      }
    @endphp

    <table class="table">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Título / Tipo</th>
          <th>Estado</th>
          <th>Ver</th>
        </tr>
      </thead>
      <tbody>
      @forelse($finales as $p)
        @php
          $act = $p->activity_id
            ? \Illuminate\Support\Facades\DB::table('activities')->where('id',$p->activity_id)->first()
            : null;
          $estado = $p->_final_state === 'finalizada' ? 'finalizada' : 'rechazada';
        @endphp
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d') }}</td>
          <td>{{ $act?->title ?? $p->work_type ?? '—' }}</td>
          <td>{{ $estado }}</td>
          <td>
            @if($estado==='finalizada' && $act)
              <a class="btn btn-secondary" href="{{ route('admin.actividad.show',$act->id) }}">Ver</a>
            @else
              {{-- propuesta rechazada (sin actividad) --}}
              <a class="btn btn-secondary" href="{{ route('admin.propuesta.show',$p->id) }}">Ver</a>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="4">No hay elementos.</td></tr>
      @endforelse
      </tbody>
    </table>
  </section>
</div>

{{-- ========= MODALES REUTILIZABLES (centrados + animación) ========= --}}
<style>
  .us-modal-open { overflow:hidden; }
  .us-modal-overlay{
    position:fixed; top:0; left:0; width:100vw; height:100vh;
    display:none; align-items:center; justify-content:center;
    background:rgba(0,0,0,.45);
    z-index:1000;
    animation: usFadeIn .18s ease-out;
    backdrop-filter: blur(2px);
  }
  .us-modal-overlay.show{ display:flex; }
  .us-modal-card{
    width:min(720px, 92vw);
    max-height: 90vh; /* si el contenido es alto, el card hace scroll */
    overflow:auto;
    background:#fff; border-radius:12px;
    box-shadow:0 10px 35px rgba(0,0,0,.2);
    transform: translateY(8px) scale(.98);
    animation: usPop .18s ease-out forwards;
  }
  .us-modal-head{
    position:sticky; top:0; background:#fff;
    padding:1rem 1.25rem; border-bottom:1px solid #eee;
    display:flex; align-items:center; justify-content:space-between;
    z-index:1;
  }
  .us-modal-body{ padding:1rem 1.25rem; }
  .us-modal-foot{
    padding:0 1.25rem 1rem; display:flex; gap:.5rem; justify-content:flex-end;
  }
  @keyframes usPop { to { transform:none; opacity:1; } }
  @keyframes usFadeIn { from { opacity:0;} to {opacity:1;} }
</style>

{{-- Modal: ACEPTAR Y PUBLICAR --}}
<div id="modal-accept" class="us-modal-overlay" aria-hidden="true">
  <div class="us-modal-card" role="dialog" aria-modal="true" aria-labelledby="us-accept-title">
    <div class="us-modal-head">
      <strong id="us-accept-title">Aceptar y publicar</strong>
      <button type="button" class="btn btn-secondary" onclick="closeAccept()" style="padding:.25rem .5rem">×</button>
    </div>
    <form id="modal-accept-form" method="POST" action="#" class="us-modal-body" style="display:grid;gap:.65rem">
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
      <div class="us-modal-foot">
        <button type="button" class="btn btn-secondary" onclick="closeAccept()">Cancelar</button>
        <button class="btn btn-success">Publicar actividad</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal: VER DESCRIPCIÓN --}}
<div id="modal-desc" class="us-modal-overlay" aria-hidden="true">
  <div class="us-modal-card" role="dialog" aria-modal="true" aria-labelledby="us-desc-title">
    <div class="us-modal-head">
      <strong id="us-desc-title">Detalle de la propuesta</strong>
      <button type="button" class="btn btn-secondary" onclick="closeDesc()" style="padding:.25rem .5rem">×</button>
    </div>
    <div id="modal-desc-body" class="us-modal-body"></div>
    <div class="us-modal-foot">
      <button type="button" class="btn btn-primary" onclick="closeDesc()">Cerrar</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // ===== utilidades para centrar SIEMPRE en viewport =====
  function portal(el){
    if (!el) return;
    if (el.parentNode !== document.body) {
      document.body.appendChild(el); // evita que algún transform del contenedor afecte a position:fixed
    }
  }
  function lockScroll(){
    // Evita "salto" por la barra de scroll
    const sbw = window.innerWidth - document.documentElement.clientWidth;
    document.body.classList.add('us-modal-open');
    if (sbw > 0) document.body.style.paddingRight = sbw + 'px';
  }
  function unlockScroll(){
    document.body.classList.remove('us-modal-open');
    document.body.style.paddingRight = '';
  }

  // ===== Modal "Aceptar y publicar"
  const $accept = document.getElementById('modal-accept');
  const $acceptForm = document.getElementById('modal-accept-form');

  function openApprove(btn){
    const id    = btn.dataset.id;
    const title = btn.dataset.title || '';
    const hours = btn.dataset.hours || 1;
    const desc  = btn.dataset.description || '';

    $acceptForm.action = "{{ url('/admin/propuestas') }}/" + id + "/decision";
    $acceptForm.querySelector('[name=title]').value = title;
    $acceptForm.querySelector('[name=social_hours]').value = hours;
    $acceptForm.querySelector('[name=description]').value = desc;

    portal($accept);
    lockScroll();
    $accept.classList.add('show');
    $accept.setAttribute('aria-hidden','false');
  }
  function closeAccept(){
    $accept.classList.remove('show');
    $accept.setAttribute('aria-hidden','true');
    unlockScroll();
  }
  $accept.addEventListener('click', (e)=>{ if(e.target === $accept) closeAccept(); });
  window.addEventListener('resize', ()=>{/* el overlay fixed ya re-centra por flex */});
  window.addEventListener('orientationchange', ()=>{/* idem */});

  // ===== Modal "Ver descripción"
  const $desc = document.getElementById('modal-desc');
  const $descBody = document.getElementById('modal-desc-body');

  function openDesc(tplId){
    const tpl = document.getElementById(tplId);
    if(!tpl) return;
    $descBody.innerHTML = tpl.innerHTML;

    portal($desc);
    lockScroll();
    $desc.classList.add('show');
    $desc.setAttribute('aria-hidden','false');
  }
  function closeDesc(){
    $desc.classList.remove('show');
    $desc.setAttribute('aria-hidden','true');
    unlockScroll();
  }
  $desc.addEventListener('click', (e)=>{ if(e.target === $desc) closeDesc(); });
</script>
@endpush
@endsection
```
