@extends('layouts.app')
@section('title','Iniciar sesión')

@section('content')
  <div class="container" style="max-width:520px">
    <div class="card">
      <h1 style="margin-bottom:1rem">Inicia sesión</h1>
      
      {{-- Resumen de errores --}}
      @if ($errors->any())
        <div class="alert alert-error">
          <strong>Revisa los campos:</strong>
          <ul style="margin:.5rem 0 0 1rem">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Mensaje de estado (por ejemplo, “Contraseña reseteada”) --}}
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}" id="form-login" novalidate>
        @csrf

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input id="email" name="email" type="email" required
                 value="{{ old('email') }}" autocomplete="username"
                 placeholder="tucorreo@ejemplo.com" />
          @error('email') <small class="text-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <div style="position:relative">
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   minlength="8" placeholder="••••••••" />
            <button type="button" id="togglePass" class="btn btn-secondary"
                    style="position:absolute;right:.25rem;top:.25rem;padding:.4rem .6rem">Ver</button>
          </div>
          @error('password') <small class="text-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group" style="display:flex;justify-content:space-between;align-items:center">
          <label style="display:flex;align-items:center;gap:.5rem">
            <input type="checkbox" name="remember" id="remember_me" />
            <span>Recordarme</span>
          </label>

          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
          @endif
        </div>

        <button type="submit" class="btn btn-primary" id="btn-login">Iniciar sesión</button>

        @if (Route::has('register'))
          <p style="margin-top:1rem">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}"><strong>Regístrate</strong></a>
          </p>
        @endif
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Evita doble envío y permite ver/ocultar contraseña
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-login');
    const btn  = document.getElementById('btn-login');
    const pass = document.getElementById('password');
    const tog  = document.getElementById('togglePass');

    form.addEventListener('submit', () => {
      btn.disabled = true;
      btn.textContent = 'Ingresando...';
    });

    tog.addEventListener('click', () => {
      const t = pass.getAttribute('type') === 'password' ? 'text' : 'password';
      pass.setAttribute('type', t);
      tog.textContent = t === 'password' ? 'Ver' : 'Ocultar';
    });
  });
</script>
@endpush
