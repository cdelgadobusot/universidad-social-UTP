<?php

return [

    // Mensajes por defecto (puedes ir agregando más si quieres)
    'required' => 'Este campo es obligatorio.',
    'email'    => 'Debe ser un correo válido.',
    'confirmed'=> 'Las contraseñas no coinciden.',
    'min'      => [
        'string' => 'Debe tener al menos :min caracteres.',
    ],

    // 👇 Mensajes específicos de Password::defaults() (Breeze / Fortify)
    'password' => [
        'mixed'   => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.',
        'symbols' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.',
        'numbers' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.',
        'letters' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.',
        'uncompromised' => 'Esta contraseña aparece en una filtración. Usa otra.',
    ],
];
