<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IdentityPolicyService
{
    public function resolve(array $identity): array
    {
        if (!Schema::hasTable('identity_access_policies')) {
            return ['profile' => $identity['profile'] ?? 'Operador', 'm365_groups' => [], 'google_groups' => [], 'applications' => [], 'matched' => []];
        }

        $policies = DB::table('identity_access_policies')
            ->where('enabled', true)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $matched = $policies->filter(fn ($policy) => $this->matches($policy, $identity));

        return [
            'profile' => optional($matched->first())->profile ?: ($identity['profile'] ?? 'Operador'),
            'm365_groups' => $this->mergeJsonLists($matched, 'm365_groups'),
            'google_groups' => $this->mergeJsonLists($matched, 'google_groups'),
            'applications' => $this->mergeJsonLists($matched, 'applications'),
            'matched' => $matched->pluck('name')->values()->all(),
        ];
    }

    private function matches(object $policy, array $identity): bool
    {
        if ($policy->department && mb_strtolower(trim($policy->department)) !== mb_strtolower(trim((string) ($identity['department'] ?? '')))) return false;
        if ($policy->title && mb_strtolower(trim($policy->title)) !== mb_strtolower(trim((string) ($identity['title'] ?? '')))) return false;
        if ($policy->ldap_group) {
            $groups = array_map(fn ($g) => mb_strtolower($this->cnFromDn((string) $g)), $identity['groups'] ?? []);
            if (!in_array(mb_strtolower(trim($policy->ldap_group)), $groups, true)) return false;
        }
        return true;
    }

    private function mergeJsonLists(Collection $policies, string $column): array
    {
        return $policies->flatMap(function ($policy) use ($column) {
            $value = json_decode($policy->{$column} ?? '[]', true);
            return is_array($value) ? $value : [];
        })->filter()->unique()->values()->all();
    }

    private function cnFromDn(string $dn): string
    {
        return preg_match('/CN=([^,]+)/i', $dn, $m) ? str_replace('\\,', ',', $m[1]) : $dn;
    }
}
