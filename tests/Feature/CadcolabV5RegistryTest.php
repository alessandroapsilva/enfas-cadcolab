<?php

namespace Tests\Feature;

use Tests\TestCase;

class CadcolabV5RegistryTest extends TestCase
{
    public function test_module_registry_has_unique_pages_and_valid_roles(): void
    {
        $groups = config('cadcolab_modules.groups', []);
        $seen = [];
        $validRoles = ['admin','ti','rh','gestor','operador','consulta'];

        foreach ($groups as $group) {
            $this->assertNotEmpty($group['label'] ?? null);
            foreach (($group['modules'] ?? []) as $key => $module) {
                $this->assertNotContains($key, $seen, "Módulo duplicado: {$key}");
                $seen[] = $key;
                $this->assertNotEmpty($module['label'] ?? null);
                $this->assertNotEmpty($module['roles'] ?? []);
                foreach ($module['roles'] as $role) $this->assertContains($role, $validRoles);
            }
        }

        foreach (['dashboard','colaboradores','configuracoes','relatorios','auditoria','changelog','badge-studio','identity-directory'] as $required) {
            $this->assertContains($required, $seen, "Módulo obrigatório ausente: {$required}");
        }
    }

    public function test_unknown_and_operational_profiles_are_never_promoted_to_rh(): void
    {
        $registry = app(\App\Services\ModuleRegistryService::class);

        session(['admin_perfil' => 'Operador']);
        $this->assertSame('operador', $registry->role());
        $this->assertFalse($registry->canAccess('configuracoes'));

        session(['admin_perfil' => 'Perfil desconhecido']);
        $this->assertSame('consulta', $registry->role());
        $this->assertFalse($registry->canAccess('usuarios'));
    }

    public function test_dashboard_requires_an_authenticated_corporate_session(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_v5_shell_assets_and_views_exist(): void
    {
        foreach ([
            resource_path('views/layouts/cadcolab-v5.blade.php'),
            resource_path('views/v5/dashboard.blade.php'),
            resource_path('views/v5/collaborators.blade.php'),
            resource_path('views/v5/settings.blade.php'),
            resource_path('views/v5/reports.blade.php'),
            resource_path('views/v5/insights.blade.php'),
            resource_path('views/v5/email-logs.blade.php'),
            resource_path('views/v5/changelog.blade.php'),
            public_path('cadcolab-v5.css'),
            public_path('cadcolab-v5.js'),
        ] as $path) {
            $this->assertFileExists($path);
        }
    }
}
