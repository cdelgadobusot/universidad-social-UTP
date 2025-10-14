@extends('layouts.app')
@section('title','Crear cuenta')

@section('content')
  <div class="container" style="max-width:520px">
    <div class="card">
      <h1 style="margin-bottom:1rem">Crear cuenta</h1>

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

      <form method="POST" action="{{ route('register') }}" id="form-register" novalidate>
        @csrf

        <div class="form-group">
          <label for="name">Nombre completo</label>
          <input id="name" name="name" type="text" required
                 value="{{ old('name') }}" autocomplete="name" placeholder="Tu nombre" />
          @error('name') <small class="text-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input id="email" name="email" type="email" required
                 value="{{ old('email') }}" autocomplete="email" placeholder="tucorreo@ejemplo.com" />
          @error('email') <small class="text-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input id="password" name="password" type="password" required minlength="8"
                 autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
          @error('password') <small class="text-error">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmar contraseña</label>
          <input id="password_confirmation" name="password_confirmation" type="password"
                 required minlength="8" autocomplete="new-password" placeholder="Repite la contraseña" />
        </div>

        <button type="submit" class="btn btn-primary" id="btn-register">Crear cuenta</button>

        <p style="margin-top:1rem">
          ¿Ya tienes cuenta?
          <a href="{{ route('login') }}"><strong>Inicia sesión</strong></a>
        </p>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Evita doble envío y validación básica de confirmación
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-register');
    const btn  = document.getElementById('btn-register');
    const p1   = document.getElementById('password');
    const p2   = document.getElementById('password_confirmation');

    form.addEventListener('submit', (e) => {
      if (p1.value !== p2.value) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.');
        p2.focus();
        return;
      }
      btn.disabled = true;
      btn.textContent = 'Creando...';
    });
  });
</script>
@endpush
