<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                // Usa la política global definida en AppServiceProvider (Password::defaults())
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['required', 'in:estudiante,profesor,organizacion,administrador'],
            ],
            // 🔽 Mensajes personalizados
            [
                'password.required'  => 'La contraseña es obligatoria.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                // Forzamos que, si falla cualquiera de las reglas (min/mixed/symbols),
                // se muestre SIEMPRE este mismo mensaje claro:
                'password.min'       => 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un carácter especial.',
                'password.mixed'     => 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un carácter especial.',
                'password.symbols'   => 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un carácter especial.',
            ]
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
