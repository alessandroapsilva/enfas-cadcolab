<?php

namespace App\Services;

class ModuleRegistryService
{
    public function version(): string
    {
        return (string) config('cadcolab_modules.version', '5.0.0-alpha');
    }

    public function role(): string
    {
        $raw = strtolower(trim((string) session('admin_perfil', '')));
        return match (true) {
            str_contains($raw, 'admin') => 'admin',
            str_contains($raw, 'ti') => 'ti',
            str_contains($raw, 'rh') => 'rh',
            default => 'rh',
        };
    }

    public function groups(?string $role = null): array
    {
        $role ??= $this->role();
        $groups = config('cadcolab_modules.groups', []);
        $visible = [];

        foreach ($groups as $key => $group) {
            $modules = [];
            foreach (($group['modules'] ?? []) as $moduleKey => $module) {
                if (in_array($role, $module['roles'] ?? [], true)) {
                    $modules[$moduleKey] = $module;
                }
            }
            if ($modules) {
                $group['modules'] = $modules;
                $visible[$key] = $group;
            }
        }
        return $visible;
    }

    public function module(string $page, ?string $role = null): ?array
    {
        foreach ($this->groups($role) as $groupKey => $group) {
            if (isset($group['modules'][$page])) {
                return $group['modules'][$page] + [
                    'key' => $page,
                    'group_key' => $groupKey,
                    'group_label' => $group['label'] ?? $groupKey,
                ];
            }
        }
        return null;
    }

    public function canAccess(string $page, ?string $role = null): bool
    {
        return $this->module($page, $role) !== null;
    }
}
