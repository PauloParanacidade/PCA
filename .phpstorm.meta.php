<?php

namespace PHPSTORM_META {

    override(\Illuminate\Support\Facades\Auth::user(), map([
        '' => '@',
    ]));

    override(\Illuminate\Support\Facades\Auth::id(), map([
        '' => 'int',
    ]));

    // Definir que o usuário tem os métodos hasRole e hasAnyRole
    override(\App\Models\User::hasRole(0), map([
        'string|array' => 'bool',
    ]));

    override(\App\Models\User::hasAnyRole(0), map([
        'string|array' => 'bool',
    ]));
}
