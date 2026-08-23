<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IdentitySettingsService
{
    private const SECRET_KEYS = ['ldap.bind_password'];
    private const MAP = [
        'identity.enabled' => 'identity.enabled',
        'identity.sync_on_login' => 'identity.sync_on_login',
        'identity.local_fallback' => 'identity.local_fallback',
        'ldap.host' => 'identity.ldap.host',
        'ldap.port' => 'identity.ldap.port',
        'ldap.ssl' => 'identity.ldap.ssl',
        'ldap.start_tls' => 'identity.ldap.start_tls',
        'ldap.timeout' => 'identity.ldap.timeout',
        'ldap.base_dn' => 'identity.ldap.base_dn',
        'ldap.users_dn' => 'identity.ldap.users_dn',
        'ldap.groups_dn' => 'identity.ldap.groups_dn',
        'ldap.bind_dn' => 'identity.ldap.bind_dn',
        'ldap.bind_password' => 'identity.ldap.bind_password',
        'ldap.account_suffix' => 'identity.ldap.account_suffix',
        'ldap.username_attribute' => 'identity.ldap.username_attribute',
        'ldap.user_filter' => 'identity.ldap.user_filter',
        'ldap.group_filter' => 'identity.ldap.group_filter',
        'profiles.admin_groups' => 'identity.profiles.admin_groups',
        'profiles.ti_groups' => 'identity.profiles.ti_groups',
        'profiles.rh_groups' => 'identity.profiles.rh_groups',
        'profiles.default' => 'identity.profiles.default',
    ];

    public function apply(): void
    {
        if (!Schema::hasTable('identity_directory_settings')) return;
        foreach (DB::table('identity_directory_settings')->get() as $row) {
            if (!isset(self::MAP[$row->setting_key])) continue;
            $value = $row->setting_value;
            if ($row->is_encrypted && $value) {
                try { $value = Crypt::decryptString($value); } catch (\Throwable) { continue; }
            }
            config([self::MAP[$row->setting_key] => $this->runtimeValue($row->setting_key, $value)]);
        }
    }

    public function allForForm(): array
    {
        $this->apply();
        return [
            'enabled' => (bool) config('identity.enabled'),
            'sync_on_login' => (bool) config('identity.sync_on_login'),
            'local_fallback' => (bool) config('identity.local_fallback'),
            'ldap' => config('identity.ldap'),
            'profiles' => config('identity.profiles'),
            'bind_password_configured' => Schema::hasTable('identity_directory_settings') && DB::table('identity_directory_settings')->where('setting_key','ldap.bind_password')->whereNotNull('setting_value')->where('setting_value','!=','')->exists(),
        ];
    }

    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (!isset(self::MAP[$key])) continue;
            if ($key === 'ldap.bind_password' && ($value === null || $value === '')) continue;
            $stored = $this->storageValue($key, $value);
            $encrypted = in_array($key, self::SECRET_KEYS, true);
            if ($encrypted && $stored !== '') $stored = Crypt::encryptString($stored);
            DB::table('identity_directory_settings')->updateOrInsert(
                ['setting_key'=>$key],
                ['setting_value'=>$stored,'is_encrypted'=>$encrypted,'updated_by'=>session('admin_nome','Sistema'),'updated_at'=>now(),'created_at'=>now()]
            );
        }
        $this->apply();
    }

    private function storageValue(string $key, mixed $value): string
    {
        if (in_array($key,['identity.enabled','identity.sync_on_login','identity.local_fallback','ldap.ssl','ldap.start_tls'],true)) return filter_var($value,FILTER_VALIDATE_BOOLEAN)?'1':'0';
        if (str_starts_with($key,'profiles.') && str_ends_with($key,'_groups')) {
            $parts = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$value)))));
            return implode(',', $parts);
        }
        return trim((string)$value);
    }

    private function runtimeValue(string $key, mixed $value): mixed
    {
        if (in_array($key,['identity.enabled','identity.sync_on_login','identity.local_fallback','ldap.ssl','ldap.start_tls'],true)) return in_array((string)$value,['1','true','on','yes'],true);
        if (in_array($key,['ldap.port','ldap.timeout'],true)) return (int)$value;
        if (str_starts_with($key,'profiles.') && str_ends_with($key,'_groups')) return array_values(array_filter(array_map('trim', explode(',', (string)$value))));
        return $value;
    }
}
