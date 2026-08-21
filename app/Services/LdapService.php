<?php

namespace App\Services;

use RuntimeException;

class LdapService
{
    public function authenticate(string $username, string $password): ?array
    {
        if (! config('ldap.enabled')) {
            return null;
        }

        if ($username === '' || $password === '') {
            throw new RuntimeException('Usuário e senha são obrigatórios.');
        }

        if (! extension_loaded('ldap')) {
            throw new RuntimeException('A extensão LDAP do PHP não está instalada.');
        }

        $connection = $this->connect();

        try {
            $bindDn = config('ldap.bind_dn');
            if ($bindDn) {
                if (! @ldap_bind($connection, $bindDn, (string) config('ldap.bind_password'))) {
                    throw new RuntimeException('Não foi possível consultar o diretório corporativo.');
                }
            }

            $escaped = function_exists('ldap_escape')
                ? ldap_escape($username, '', LDAP_ESCAPE_FILTER)
                : addcslashes($username, "\\()*\x00");

            $filter = str_replace('{username}', $escaped, (string) config('ldap.user_filter'));
            $search = @ldap_search($connection, (string) config('ldap.base_dn'), $filter, [
                config('ldap.username_attribute'),
                config('ldap.name_attribute'),
                config('ldap.email_attribute'),
                config('ldap.group_attribute'),
            ]);

            if (! $search) {
                throw new RuntimeException('Falha ao pesquisar o usuário no diretório.');
            }

            $entries = ldap_get_entries($connection, $search);
            if (($entries['count'] ?? 0) !== 1) {
                return null;
            }

            $entry = $entries[0];
            if (! @ldap_bind($connection, $entry['dn'], $password)) {
                return null;
            }

            $groups = $this->values($entry, config('ldap.group_attribute'));
            $this->assertAllowed($groups);

            return [
                'dn' => $entry['dn'],
                'username' => $this->first($entry, config('ldap.username_attribute')) ?: $username,
                'name' => $this->first($entry, config('ldap.name_attribute')) ?: $username,
                'email' => $this->first($entry, config('ldap.email_attribute')),
                'groups' => $groups,
                'role' => $this->resolveRole($groups),
            ];
        } finally {
            @ldap_unbind($connection);
        }
    }

    private function connect()
    {
        foreach (config('ldap.hosts', []) as $host) {
            $scheme = config('ldap.use_ssl') ? 'ldaps' : 'ldap';
            $connection = @ldap_connect(sprintf('%s://%s:%d', $scheme, $host, config('ldap.port')));
            if (! $connection) {
                continue;
            }

            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, config('ldap.timeout'));

            if (config('ldap.use_tls') && ! @ldap_start_tls($connection)) {
                @ldap_unbind($connection);
                continue;
            }

            return $connection;
        }

        throw new RuntimeException('Diretório corporativo indisponível.');
    }

    private function assertAllowed(array $groups): void
    {
        $allowed = config('ldap.allowed_groups', []);
        if ($allowed && ! $this->matchesAny($groups, $allowed)) {
            throw new RuntimeException('Usuário sem autorização para acessar o CADCOLAB.');
        }
    }

    private function resolveRole(array $groups): string
    {
        foreach (config('ldap.roles', []) as $role => $mappedGroups) {
            if ($this->matchesAny($groups, $mappedGroups)) {
                return $role;
            }
        }

        return (string) config('ldap.default_role', 'Consulta');
    }

    private function matchesAny(array $groups, array $expected): bool
    {
        $normalized = array_map('mb_strtolower', $groups);
        foreach ($expected as $group) {
            if (in_array(mb_strtolower($group), $normalized, true)) {
                return true;
            }
        }
        return false;
    }

    private function first(array $entry, string $attribute): ?string
    {
        $key = mb_strtolower($attribute);
        return isset($entry[$key][0]) ? (string) $entry[$key][0] : null;
    }

    private function values(array $entry, string $attribute): array
    {
        $key = mb_strtolower($attribute);
        if (! isset($entry[$key]['count'])) {
            return [];
        }

        $values = [];
        for ($i = 0; $i < $entry[$key]['count']; $i++) {
            $values[] = (string) $entry[$key][$i];
        }
        return $values;
    }
}
