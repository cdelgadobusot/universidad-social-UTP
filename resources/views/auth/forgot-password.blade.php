<x-guest-layout>
    <div class="mb-3 text-muted">
        ¿Olvidaste tu contraseña? No hay problema. Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.
    </div>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Campo de correo con margen inferior Bootstrap --}}
        <div class="mb-4">
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input
                id="email"
                class="form-control mt-1 w-100"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Contenedor del botón con margen superior Bootstrap --}}
        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                Enviar enlace para restablecer contraseña
            </button>
        </div>
    </form>
</x-guest-layout>
