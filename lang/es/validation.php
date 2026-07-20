<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'max' => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'confirmed' => 'La confirmación del campo :attribute no coincide.',
    'unique' => 'El campo :attribute ya ha sido registrado.',
    'password' => [
        'letters' => 'La contraseña debe contener al menos una letra.',
        'mixed' => 'La contraseña debe contener tanto letras mayúsculas como minúsculas.',
        'symbols' => 'La contraseña debe contener al menos un símbolo.',
        'numbers' => 'La contraseña debe contener al menos un número.',
        'uncompromised' => 'La contraseña ingresada ha aparecido en una filtración de datos. Por favor, elija otra.',
    ],
    'custom' => [
        'email' => [
            'invalid_credentials' => 'El correo electrónico o la contraseña son incorrectos.',
        ],
    ],
    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmar contraseña',
    ],
];
