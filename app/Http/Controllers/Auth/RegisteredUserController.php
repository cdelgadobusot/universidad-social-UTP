<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                // IMPORTANTE: si NO quieres permitir crear admins desde registro público,
                // cambia el in: para quitar "administrador".
                'role' => ['required', 'in:estudiante,profesor,organizacion'],
            ],
            [
                'password.required'  => 'La contraseña es obligatoria.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.min'       => 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un carácter especial.',
                'password.mixed'     => 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un carácter especial.',
                'password.symbols'   => 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un carácter especial.',
                'email.unique'   => 'El correo electrónico ya está registrado.',
            ]
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        event(new Registered($user));

        // ✅ NO iniciar sesión automáticamente
        // Auth::login($user);

        // ✅ Mensaje + redirección al login
        return redirect()
            ->route('login')
            ->with('status', 'Cuenta creada correctamente. Ahora puedes iniciar sesión.');
    }
}
