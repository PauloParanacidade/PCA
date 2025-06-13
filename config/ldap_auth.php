<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication Model
    |--------------------------------------------------------------------------
    |
    | This is the LDAP model that will be used for authentication.
    |
    */
    'model'    => LdapRecord\Models\ActiveDirectory\User::class,

    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication Rules
    |--------------------------------------------------------------------------
    |
    | Upon successful LDAP authentication, these rules will be used to determine
    | if the user is allowed to be logged into the application.
    |
    */
    'rules'    => [],

    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication Scopes
    |--------------------------------------------------------------------------
    |
    | Upon successful LDAP authentication, these scopes will be applied to the
    | query to determine the user that should be authenticated.
    |
    */
    'scopes'   => [],

    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard that will be used for LDAP authentication.
    |
    */
    'guard'    => 'web',

    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication Provider
    |--------------------------------------------------------------------------
    |
    | The authentication provider that will be used for LDAP authentication.
    |
    */
    'provider' => 'ldap',

    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication Database
    |--------------------------------------------------------------------------
    |
    | The database configuration for LDAP authentication.
    |
    */
    'database' => [
        'model'           => App\Models\User::class,
        'sync_passwords'  => true,
        'sync_attributes' => [
            'name'           => 'displayname',
            'email'          => 'mail',
            'username'       => 'samaccountname',
            'manager'        => 'manager',
            'department'     => 'departmentnumber',
            'employeeNumber' => 'employeenumber',
        ],
        'identifiers'     => [
            'ldap'     => [
                'locate_users_by' => 'username',
                'bind_users_by'   => 'distinguishedname',
            ],
            'database' => [
                'guid_column'     => 'objectguid',
                'username_column' => 'username',
            ],
        ],
    ],
];
