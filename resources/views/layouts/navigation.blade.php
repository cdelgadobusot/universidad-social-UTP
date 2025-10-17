<nav class="navbar" style="background-color:#ffffff; border-bottom:2px solid #0f5132; padding:0.8rem 1rem;">
    <div class="container" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;">

        <!-- Logo (siempre lleva al panel del admin o a /home) -->
        <a href="{{ route('home') }}" class="navbar-brand"
           style="font-weight:700; font-size:1.3rem; color:#0f5132; text-decoration:none; line-height:1.2;">
            Universidad<br>Social
        </a>

        <!-- Enlaces -->
        <div style="
            display:flex;
            align-items:center;
            justify-content:center;
            flex:1;
            gap:2rem;
            flex-wrap:wrap;
            text-align:center;
        ">
            @php
                $isAuth = Auth::check();
                $role = $isAuth ? Auth::user()->role : null;
                $currentRoute = request()->route()?->getName();
                $onAdminPanel = ($currentRoute === 'home') && ($role === 'administrador');
            @endphp

            @if($role === 'administrador' && $onAdminPanel)
                <a href="#sec-pendientes" data-scroll
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Propuestas pendientes
                </a>
                <a href="#sec-activas" data-scroll
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Propuestas activas
                </a>
                <a href="#sec-finales" data-scroll
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Propuestas finalizadas o rechazadas
                </a>
            @elseif($role === 'administrador' && $currentRoute === 'admin.actividad.show')
                {{-- En vista de "Ver" (finalizadas/rechazadas): SIN enlaces en la navbar --}}
            @elseif($role === 'administrador')
                <a href="{{ route('home') }}"
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Panel
                </a>
            @elseif($role === 'profesor' || $role === 'organizacion')
                <a href="{{ route('postular.actividades') }}"
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Postular Actividades
                </a>
                <a href="{{ route('tomar.lista') }}"
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Tomar Lista
                </a>
            @elseif($role === 'estudiante')
                <a href="{{ route('registrar.actividades') }}"
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Registrar en Actividades
                </a>
            @else
                <a href="{{ route('home') }}"
                   style="color:#0f5132; font-weight:500; text-decoration:none;">
                   Inicio
                </a>
            @endif
        </div>

        <!-- Usuario / Cerrar sesión -->
        <div style="display:flex; align-items:center; gap:1rem;">
            @auth
                <span style="color:#0f5132; font-weight:600;">Hola, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit"
                            style="background:transparent; color:#0f5132; border:2px solid #0f5132;
                                   border-radius:0.5rem; padding:0.4rem 0.9rem;
                                   font-weight:600; cursor:pointer;">
                        Cerrar sesión
                    </button>
                </form>
            @endauth
        </div>

    </div>

    {{-- Scroll suave para enlaces con data-scroll (solo en panel) --}}
    @push('scripts')
    <script>
      document.addEventListener('click', (e) => {
        const a = e.target.closest('a[data-scroll]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (href && href.startsWith('#')) {
          const target = document.querySelector(href);
          if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }
      });
    </script>
    @endpush
</nav>
