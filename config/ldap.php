<?php

return [
    'enabled' => (bool) env('LDAP_ENABLED', false),
    'hosts' => array_values(array_filter(array_map('trim', explode(',', env('LDAP_HOSTS', ''))))),
    'port' => (int) env('LDAP_PORT', 636),
    'use_ssl' => (bool) env('LDAP_USE_SSL', true),
    'use_tls' => (bool) env('LDAP_USE_TLS', false),
    'timeout' => (int) env('LDAP_TIMEOUT', 5),
    'base_dn' => env('LDAP_BASE_DN'),
    'bind_dn' => env('LDAP_BIND_DN'),
    'bind_password' => env('LDAP_BIND_PASSWORD'),
    'user_filter' => env('LDAP_USER_FILTER', '(&(objectClass=user)(|(sAMAccountName={username})(userPrincipalName={username})(mail={username})))'),
    'username_attribute' => env('LDAP_USERNAME_ATTRIBUTE', 'sAMAccountName'),
    'name_attribute' => env('LDAP_NAME_ATTRIBUTE', 'displayName'),
    'email_attribute' => env('LDAP_EMAIL_ATTRIBUTE', 'mail'),
    'group_attribute' => env('LDAP_GROUP_ATTRIBUTE', 'memberOf'),
    'allowed_groups' => array_values(array_filter(array_map('trim', explode(';', env('LDAP_ALLOWED_GROUPS', ''))))),
    'roles' => [
        'TI' => array_values(array_filter(array_map('trim', explode(';', env('LDAP_GROUPS_TI', ''))))),
        'RH' => array_values(array_filter(array_map('trim', explode(';', env('LDAP_GROUPS_RH', ''))))),
        'Gestor' => array_values(array_filter(array_map('trim', explode(';', env('LDAP_GROUPS_GESTOR', ''))))),
        'Operador' => array_values(array_filter(array_map('trim', explode(';', env('LDAP_GROUPS_OPERADOR', ''))))),
        'Consulta' => array_values(array_filter(array_map('trim', explode(';', env('LDAP_GROUPS_CONSULTA', ''))))),
    ],
    'default_role' => env('LDAP_DEFAULT_ROLE', 'Consulta'),
];
