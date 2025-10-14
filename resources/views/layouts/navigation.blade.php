<nav class="navbar" style="background-color:#ffffff; border-bottom:2px solid #0f5132; padding:0.8rem 1rem;">
    <div class="container" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="navbar-brand"
           style="font-weight:700; font-size:1.3rem; color:#0f5132; text-decoration:none; line-height:1.2;">
            Universidad<br>Social
        </a>

        <!-- Enlaces principales -->
        <div style="
            display:flex;
            align-items:center;
            justify-content:center;
            flex:1;
            gap:2rem;
            flex-wrap:wrap;
            text-align:center;
        ">
            <a href="{{ route('home') }}" 
               class="{{ request()->routeIs('home') ? 'active' : '' }}"
               style="color:#0f5132; font-weight:500; text-decoration:none;">
               Inicio
            </a>
            <a href="{{ route('postular.actividades') }}" 
               style="color:#0f5132; font-weight:500; text-decoration:none;">
               Postular Actividades
            </a>
            <a href="{{ route('registrar.actividades') }}" 
               style="color:#0f5132; font-weight:500; text-decoration:none;">
               Registrar en Actividades
            </a>
            <a href="{{ route('crear.lista') }}" 
               style="color:#0f5132; font-weight:500; text-decoration:none;">
               Crear Lista
            </a>
            <a href="{{ route('tomar.lista') }}" 
               style="color:#0f5132; font-weight:500; text-decoration:none;">
               Tomar Lista
            </a>
            <a href="{{ route('ingresar.horas') }}" 
               style="color:#0f5132; font-weight:500; text-decoration:none;">
               Ingresar Horas
            </a>
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
</nav>
