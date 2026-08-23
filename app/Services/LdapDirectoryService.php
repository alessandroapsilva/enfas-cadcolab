<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LdapDirectoryService
{
    private $connection = null;

    public function __construct(private readonly IdentitySettingsService $settings) {}

    public function isEnabled(): bool
    {
        $this->settings->apply();
        return (bool) config('identity.enabled');
    }

    public function testConnection(): array
    {
        $this->settings->apply();
        try {
            $ldap = $this->connect();
            $this->bindServiceAccount($ldap);
            return ['success' => true, 'message' => 'Conexão e bind LDAP realizados com sucesso.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function authenticate(string $username, string $password): ?array
    {
        $this->settings->apply();
        if (!$this->isEnabled() || trim($username) === '' || $password === '') {
            return null;
        }

        $ldap = $this->connect();
        $this->bindServiceAccount($ldap);
        $entry = $this->findUser($username, $ldap);
        if (!$entry || !$this->isActive($entry)) {
            return null;
        }

        $dn = $entry['dn'] ?? null;
        if (!$dn) {
            return null;
        }

        if (!@ldap_bind($ldap, $dn, $password)) {
            return null;
        }

        $this->bindServiceAccount($ldap);
        return $this->normalizeUser($entry);
    }

    public function findUser(string $username, $ldap = null): ?array
    {
        $this->settings->apply();
        $ldap = $ldap ?: $this->connectAndBind();
        $cfg = config('identity.ldap');
        $attr = $cfg['username_attribute'];
        $escaped = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
        $suffix = $cfg['account_suffix'];
        $upn = $suffix && !str_contains($username, '@') ? $username.$suffix : $username;
        $escapedUpn = ldap_escape($upn, '', LDAP_ESCAPE_FILTER);
        $filter = sprintf('(&%s(|(%s=%s)(userPrincipalName=%s)))', $cfg['user_filter'], $attr, $escaped, $escapedUpn);
        $result = @ldap_search($ldap, $cfg['users_dn'], $filter, $this->requestedAttributes());
        if (!$result) return null;
        $entries = ldap_get_entries($ldap, $result);
        return ($entries['count'] ?? 0) > 0 ? $entries[0] : null;
    }

    public function syncAll(): array
    {
        $this->settings->apply();
        $ldap = $this->connectAndBind();
        $users = $this->fetchUsers($ldap);
        $groups = $this->fetchGroups($ldap);
        $userCount = 0;
        $groupCount = 0;

        DB::transaction(function () use ($users, $groups, &$userCount, &$groupCount) {
            foreach ($groups as $group) {
                DB::table('identity_directory_groups')->updateOrInsert(
                    ['directory_key' => $group['directory_key']],
                    [
                        'name' => $group['name'],
                        'distinguished_name' => $group['distinguished_name'],
                        'description' => $group['description'],
                        'synced_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $groupCount++;
            }

            foreach ($users as $user) {
                $this->persistUser($user);
                $userCount++;
            }
        });

        return ['users' => $userCount, 'groups' => $groupCount];
    }

    public function persistUser(array $user): void
    {
        DB::table('identity_directory_users')->updateOrInsert(
            ['directory_key' => $user['directory_key']],
            [
                'username' => $user['username'],
                'user_principal_name' => $user['user_principal_name'],
                'display_name' => $user['display_name'],
                'email' => $user['email'],
                'employee_id' => $user['employee_id'],
                'department' => $user['department'],
                'title' => $user['title'],
                'phone' => $user['phone'],
                'mobile' => $user['mobile'],
                'manager_dn' => $user['manager_dn'],
                'distinguished_name' => $user['distinguished_name'],
                'groups_json' => json_encode($user['groups'], JSON_UNESCAPED_UNICODE),
                'profile' => $user['profile'],
                'is_active' => $user['is_active'],
                'synced_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if (config('identity.sync_on_login') || in_array($user['profile'], ['Admin', 'TI', 'RH'], true)) {
            DB::table('usuarios_admin')->updateOrInsert(
                ['usuario' => $user['username']],
                [
                    'nome' => $user['display_name'] ?: $user['username'],
                    'email' => $user['email'],
                    'senha' => password_hash(Str::random(64), PASSWORD_BCRYPT),
                    'perfil' => $user['profile'],
                    'identity_source' => 'ldap',
                    'ldap_directory_key' => $user['directory_key'],
                    'ldap_dn' => $user['distinguished_name'],
                    'ativo' => $user['is_active'],
                    'ldap_synced_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function fetchUsers($ldap): array
    {
        $cfg = config('identity.ldap');
        $result = @ldap_search($ldap, $cfg['users_dn'], $cfg['user_filter'], $this->requestedAttributes());
        if (!$result) throw new RuntimeException('Não foi possível consultar usuários no LDAP.');
        $entries = ldap_get_entries($ldap, $result);
        $out = [];
        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $out[] = $this->normalizeUser($entries[$i]);
        }
        return $out;
    }

    private function fetchGroups($ldap): array
    {
        $cfg = config('identity.ldap');
        $result = @ldap_search($ldap, $cfg['groups_dn'], $cfg['group_filter'], ['objectGUID','cn','distinguishedName','description']);
        if (!$result) throw new RuntimeException('Não foi possível consultar grupos no LDAP.');
        $entries = ldap_get_entries($ldap, $result);
        $out = [];
        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $e = $entries[$i];
            $dn = $e['dn'] ?? $this->first($e, 'distinguishedname');
            $out[] = [
                'directory_key' => $this->guid($this->firstRaw($e, 'objectguid')) ?: sha1((string) $dn),
                'name' => $this->first($e, 'cn') ?: $this->cnFromDn($dn),
                'distinguished_name' => $dn,
                'description' => $this->first($e, 'description'),
            ];
        }
        return $out;
    }

    private function normalizeUser(array $entry): array
    {
        $attrs = config('identity.ldap.attributes');
        $groups = $this->values($entry, strtolower($attrs['groups']));
        $guid = $this->guid($this->firstRaw($entry, strtolower($attrs['guid'])));
        $dn = $entry['dn'] ?? null;
        $username = $this->first($entry, strtolower($attrs['username'])) ?: $this->first($entry, strtolower($attrs['upn']));

        return [
            'directory_key' => $guid ?: sha1((string) ($dn ?: $username)),
            'username' => $username,
            'user_principal_name' => $this->first($entry, strtolower($attrs['upn'])),
            'display_name' => $this->first($entry, strtolower($attrs['name'])) ?: $username,
            'email' => $this->first($entry, strtolower($attrs['email'])),
            'employee_id' => $this->first($entry, strtolower($attrs['employee_id'])),
            'department' => $this->first($entry, strtolower($attrs['department'])),
            'title' => $this->first($entry, strtolower($attrs['title'])),
            'phone' => $this->first($entry, strtolower($attrs['phone'])),
            'mobile' => $this->first($entry, strtolower($attrs['mobile'])),
            'manager_dn' => $this->first($entry, strtolower($attrs['manager'])),
            'distinguished_name' => $dn,
            'groups' => $groups,
            'profile' => $this->profileFromGroups($groups),
            'is_active' => $this->isActive($entry),
        ];
    }

    private function profileFromGroups(array $groups): string
    {
        $names = array_map(fn ($dn) => mb_strtoupper($this->cnFromDn($dn)), $groups);
        foreach (['admin_groups' => 'Admin', 'ti_groups' => 'TI', 'rh_groups' => 'RH'] as $key => $profile) {
            foreach (config('identity.profiles.'.$key, []) as $required) {
                if (in_array(mb_strtoupper($required), $names, true)) return $profile;
            }
        }
        return config('identity.profiles.default', 'Operador');
    }

    private function isActive(array $entry): bool
    {
        $attr = strtolower(config('identity.ldap.attributes.account_control'));
        $uac = (int) ($this->first($entry, $attr) ?: 0);
        return ($uac & 2) !== 2;
    }

    private function requestedAttributes(): array
    {
        $attrs = array_values(config('identity.ldap.attributes'));
        $attrs[] = 'distinguishedName';
        return array_values(array_unique($attrs));
    }

    private function connectAndBind()
    {
        $ldap = $this->connect();
        $this->bindServiceAccount($ldap);
        return $ldap;
    }

    private function connect()
    {
        if (!extension_loaded('ldap')) throw new RuntimeException('Extensão PHP LDAP não instalada. Instale php-ldap no servidor.');
        $cfg = config('identity.ldap');
        if (empty($cfg['host'])) throw new RuntimeException('Servidor LDAP não configurado.');
        $scheme = $cfg['ssl'] ? 'ldaps://' : 'ldap://';
        $ldap = @ldap_connect($scheme.$cfg['host'], $cfg['port']);
        if (!$ldap) throw new RuntimeException('Não foi possível abrir conexão com o servidor LDAP.');
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, $cfg['timeout']);
        if ($cfg['start_tls'] && !$cfg['ssl'] && !@ldap_start_tls($ldap)) {
            throw new RuntimeException('Falha ao iniciar StartTLS no LDAP.');
        }
        $this->connection = $ldap;
        return $ldap;
    }

    private function bindServiceAccount($ldap): void
    {
        $cfg = config('identity.ldap');
        if (!$cfg['bind_dn']) throw new RuntimeException('Usuário de bind LDAP não configurado.');
        if (!@ldap_bind($ldap, $cfg['bind_dn'], $cfg['bind_password'])) {
            throw new RuntimeException('Falha no bind LDAP da conta de serviço.');
        }
    }

    private function first(array $entry, string $key): ?string
    {
        $key = strtolower($key);
        return isset($entry[$key][0]) ? trim((string) $entry[$key][0]) : null;
    }

    private function firstRaw(array $entry, string $key): ?string
    {
        $key = strtolower($key);
        return $entry[$key][0] ?? null;
    }

    private function values(array $entry, string $key): array
    {
        $key = strtolower($key);
        if (!isset($entry[$key]) || !is_array($entry[$key])) return [];
        $values = $entry[$key];
        unset($values['count']);
        return array_values(array_filter(array_map('strval', $values)));
    }

    private function guid(?string $binary): ?string
    {
        if (!$binary) return null;
        if (strlen($binary) !== 16) return bin2hex($binary);
        $hex = unpack('H*hex', $binary)['hex'];
        return substr($hex,6,2).substr($hex,4,2).substr($hex,2,2).substr($hex,0,2).'-'.
               substr($hex,10,2).substr($hex,8,2).'-'.substr($hex,14,2).substr($hex,12,2).'-'.
               substr($hex,16,4).'-'.substr($hex,20,12);
    }

    private function cnFromDn(?string $dn): string
    {
        if (!$dn) return '';
        if (preg_match('/CN=([^,]+)/i', $dn, $m)) return str_replace('\\,', ',', $m[1]);
        return $dn;
    }
}
