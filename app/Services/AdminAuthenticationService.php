<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class AdminAuthenticationService
{
    public function __construct(private readonly LdapService $ldap)
    {
    }

    public function attempt(string $username, string $password): ?object
    {
        if (config('ldap.enabled')) {
            try {
                $identity = $this->ldap->authenticate($username, $password);
                if ($identity) {
                    return $this->syncLdapUser($identity);
                }

                if (! env('LOCAL_AUTH_FALLBACK', false)) {
                    return null;
                }
            } catch (RuntimeException $exception) {
                report($exception);
                if (! env('LOCAL_AUTH_FALLBACK', false)) {
                    throw $exception;
                }
            }
        }

        if (! env('LOCAL_AUTH_ENABLED', true)) {
            return null;
        }

        $user = DB::table('usuarios_admin')->where('usuario', $username)->first();
        return $user && Hash::check($password, $user->senha) ? $user : null;
    }

    private function syncLdapUser(array $identity): object
    {
        $existing = DB::table('usuarios_admin')->where('usuario', $identity['username'])->first();
        $values = [
            'nome' => $identity['name'],
            'email' => $identity['email'],
            'perfil' => $identity['role'],
            'ldap_dn' => $identity['dn'],
            'auth_source' => 'ldap',
            'last_login_at' => now(),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('usuarios_admin')->where('id', $existing->id)->update($values);
            return DB::table('usuarios_admin')->find($existing->id);
        }

        $id = DB::table('usuarios_admin')->insertGetId($values + [
            'usuario' => $identity['username'],
            'senha' => Hash::make(Str::random(64)),
            'created_at' => now(),
        ]);

        return DB::table('usuarios_admin')->find($id);
    }
}
