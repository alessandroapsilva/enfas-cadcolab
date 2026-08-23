<?php

return [
    'enabled' => env('IDENTITY_DIRECTORY_ENABLED', false),
    'local_fallback' => env('IDENTITY_LOCAL_FALLBACK', true),
    'sync_on_login' => env('IDENTITY_SYNC_ON_LOGIN', true),
    'ldap' => [
        'host' => env('LDAP_HOST', '127.0.0.1'),
        'port' => (int) env('LDAP_PORT', 636),
        'ssl' => env('LDAP_SSL', true),
        'start_tls' => env('LDAP_START_TLS', false),
        'timeout' => (int) env('LDAP_TIMEOUT', 5),
        'base_dn' => env('LDAP_BASE_DN', ''),
        'users_dn' => env('LDAP_USERS_DN', env('LDAP_BASE_DN', '')),
        'groups_dn' => env('LDAP_GROUPS_DN', env('LDAP_BASE_DN', '')),
        'bind_dn' => env('LDAP_BIND_DN', ''),
        'bind_password' => env('LDAP_BIND_PASSWORD', ''),
        'account_suffix' => env('LDAP_ACCOUNT_SUFFIX', ''),
        'user_filter' => env('LDAP_USER_FILTER', '(&(objectCategory=person)(objectClass=user))'),
        'group_filter' => env('LDAP_GROUP_FILTER', '(objectClass=group)'),
        'username_attribute' => env('LDAP_USERNAME_ATTRIBUTE', 'sAMAccountName'),
        'attributes' => [
            'guid'=>'objectGUID','username'=>'sAMAccountName','upn'=>'userPrincipalName','name'=>'displayName','email'=>'mail','employee_id'=>'employeeID','department'=>'department','title'=>'title','phone'=>'telephoneNumber','mobile'=>'mobile','manager'=>'manager','groups'=>'memberOf','account_control'=>'userAccountControl',
        ],
    ],
    'profiles' => [
        'admin_groups' => array_values(array_filter(array_map('trim', explode(',', env('LDAP_ADMIN_GROUPS', 'CADCOLAB-ADMIN'))))),
        'ti_groups' => array_values(array_filter(array_map('trim', explode(',', env('LDAP_TI_GROUPS', 'CADCOLAB-TI'))))),
        'rh_groups' => array_values(array_filter(array_map('trim', explode(',', env('LDAP_RH_GROUPS', 'CADCOLAB-RH'))))),
        'default' => env('LDAP_DEFAULT_PROFILE', 'Operador'),
    ],
];
