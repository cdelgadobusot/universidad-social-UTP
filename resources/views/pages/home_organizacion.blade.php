@extends('layouts.app')
@section('title','Panel — Organización')

@section('content')
<div class="container" style="max-width:1100px">
  <h1 style="margin-bottom:1rem">Panel de la Organización</h1>
  @if (session('status'))
    <div class="alert alert-success" style="margin:0 0 1rem 0">
      {{ session('status') }}
    </div>
  @endif

  <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
    <a href="{{ route('postular.actividades') }}" class="btn btn-primary">Postular nueva actividad</a>
  </div>

  @php
    // === Preload de listas
    $ids = collect($proposals)->pluck('activity_id')->filter()->unique()->all();
    $lists = [];
    if (count($ids)) {
      $rows = DB::table('attendance_lists')
               ->select('activity_id','status','id')
               ->whereIn('activity_id',$ids)
               ->orderByDesc('id')
               ->get();
      foreach ($rows as $r) {
        if (!isset($lists[$r->activity_id])) $lists[$r->activity_id] = $r;
      }
    }

    // === Preload de actividades
    $acts = count($ids)
      ? DB::table('activities')->whereIn('id',$ids)->get()->keyBy('id')
      : collect();

    // Split
    $pendientes = collect();
    $activas    = collect();
    $finales    = collect();

    foreach ($proposals as $p) {
      $isFinal = false;
      if ($p->status === 'rechazada') {
        $isFinal = true;
      } elseif ($p->status === 'aprobada' && $p->activity_id && isset($lists[$p->activity_id]) && $lists[$p->activity_id]->status === 'cerrada') {
        $isFinal = true;
      }

      if ($p->status === 'pendiente') {
        $pendientes->push($p);
      } elseif ($isFinal) {
        $finales->push($p);
      } else {
        $activas->push($p);
      }
    }
  @endphp

  {{-- PENDIENTES --}}
  <section style="margin-bottom:2rem">
    <h2 style="margin:0 0 .75rem 0">Propuestas realizadas (pendientes)</h2>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Fecha evento</th>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Actividad</th>
            <th>Estado</th> {{-- Estado al final --}}
          </tr>
        </thead>
        <tbody>
          @forelse($pendientes as $p)
            @php $act = $p->activity_id ? ($acts[$p->activity_id] ?? null) : null; @endphp
            <tr>
              <td>{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d') }}</td>
              <td>{{ $p->place }}</td>
              <td>{{ $p->event_date }}</td>
              <td>{{ $p->work_type }}</td>
              <td>
                <button class="btn btn-secondary" onclick="openDesc('desc-org-pend-{{ $p->id }}')">Ver descripción</button>
                <template id="desc-org-pend-{{ $p->id }}">
                  <h3 style="margin-top:0">Descripción de la propuesta</h3>
                  <p style="white-space:pre-wrap">{{ $p->description ?? '—' }}</p>
                </template>
              </td>
              <td>
                @if($act)
                  {{ $act->title }} ({{ $act->status }})
                @else
                  —
                @endif
              </td>
              <td>En proceso</td>
            </tr>
          @empty
            <tr><td colspan="7">No tienes propuestas pendientes.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- ACEPTADAS Y ACTIVAS --}}
  <section style="margin-bottom:2rem">
    <h2 style="margin:0 0 .75rem 0">Propuestas aceptadas y activas</h2>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Fecha evento</th>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Actividad</th>
            <th>Tomar lista</th>
            <th>Estado</th> {{-- Estado al final --}}
          </tr>
        </thead>
        <tbody>
          @forelse($activas as $p)
            @php $act = $p->activity_id ? ($acts[$p->activity_id] ?? null) : null; @endphp
            <tr>
              <td>{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d') }}</td>
              <td>{{ $p->place }}</td>
              <td>{{ $p->event_date }}</td>
              <td>{{ $p->work_type }}</td>
              <td>
                <button class="btn btn-secondary" onclick="openDesc('desc-org-act-{{ $p->id }}')">Ver descripción</button>
                <template id="desc-org-act-{{ $p->id }}">
                  <h3 style="margin-top:0">Descripción de la propuesta</h3>
                  <p style="white-space:pre-wrap">{{ $p->description ?? '—' }}</p>
                </template>
              </td>
              <td>
                @if($act)
                  {{ $act->title }} ({{ $act->status }})
                @else
                  —
                @endif
              </td>
              <td>
                @if($p->activity_id && isset($lists[$p->activity_id]) && $lists[$p->activity_id]->status === 'compartida')
                  <a class="btn btn-primary" href="{{ route('tomar.lista', ['activity_id' => $p->activity_id]) }}">Tomar lista</a>
                @elseif($p->activity_id && isset($lists[$p->activity_id]) && $lists[$p->activity_id]->status === 'enviada')
                  <span class="badge">Lista enviada</span>
                @elseif($p->activity_id && isset($lists[$p->activity_id]) && $lists[$p->activity_id]->status === 'cerrada')
                  <span class="badge">Lista cerrada</span>
                @else
                  <span class="text-muted">Esperando habilitación del administrador</span>
                @endif
              </td>
              <td>Aceptada</td>
            </tr>
          @empty
            <tr><td colspan="8">No tienes propuestas activas.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- FINALIZADAS O RECHAZADAS --}}
  <section style="margin-bottom:2rem">
    <h2 style="margin:0 0 .75rem 0">Propuestas finalizadas o rechazadas</h2>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Fecha evento</th>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Actividad</th>
            <th>Estado</th> {{-- Estado al final --}}
          </tr>
        </thead>
        <tbody>
          @forelse($finales as $p)
            @php $act = $p->activity_id ? ($acts[$p->activity_id] ?? null) : null; @endphp
            <tr>
              <td>{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d') }}</td>
              <td>{{ $p->place }}</td>
              <td>{{ $p->event_date }}</td>
              <td>{{ $p->work_type }}</td>
              <td>
                <button class="btn btn-secondary" onclick="openDesc('desc-org-fin-{{ $p->id }}')">Ver descripción</button>
                <template id="desc-org-fin-{{ $p->id }}">
                  <h3 style="margin-top:0">Descripción de la propuesta</h3>
                  <p style="white-space:pre-wrap">{{ $p->description ?? '—' }}</p>
                </template>
              </td>
              <td>
                @if($act)
                  {{ $act->title }} ({{ $act->status }})
                @else
                  —
                @endif
              </td>
              <td>
                @if($p->status==='rechazada')
                  Rechazada
                  <div class="alert alert-warning" style="margin-top:.35rem">
                    Por favor póngase en contacto con nosotros vía correo para poder darle más detalle del rechazo. Gracias.
                  </div>
                @else
                  Finalizada
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7">No tienes propuestas finalizadas o rechazadas.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>

{{-- ========= MODAL "VER DESCRIPCIÓN" (centrado + animación) ========= --}}
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
    max-height: 90vh;
    overflow:auto;
    background:#fff; border-radius:12px;
    box-shadow:0 10px 35px rgba(0,0,0,.2);
    transform: translateY(8px) scale(.98);
    animation: usPop .18s ease-out forwards;
  }
  .us-modal-head{ position:sticky; top:0; background:#fff; padding:1rem 1.25rem; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; z-index:1;}
  .us-modal-body{ padding:1rem 1.25rem; }
  .us-modal-foot{ padding:0 1.25rem 1rem; display:flex; gap:.5rem; justify-content:flex-end; }
  @keyframes usPop { to { transform:none; opacity:1; } }
  @keyframes usFadeIn { from { opacity:0;} to {opacity:1;} }
</style>

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

<script>
  function portal(el){
    if (!el) return;
    if (el.parentNode !== document.body) document.body.appendChild(el);
  }
  function lockScroll(){
    const sbw = window.innerWidth - document.documentElement.clientWidth;
    document.body.classList.add('us-modal-open');
    if (sbw > 0) document.body.style.paddingRight = sbw + 'px';
  }
  function unlockScroll(){
    document.body.classList.remove('us-modal-open');
    document.body.style.paddingRight = '';
  }

  const $desc = document.getElementById('modal-desc');
  const $descBody = document.getElementById('modal-desc-body');

  function openDesc(tplId){
    const tpl = document.getElementById(tplId);
    if(!tpl) return;
    $descBody.innerHTML = tpl.innerHTML;
    portal($desc); lockScroll();
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
@endsection
