<?php

namespace App\Http\Controllers;

use App\Services\ModuleRegistryService;
use App\Services\ReleaseNotesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardRouterController extends Controller
{
    public function index(Request $request, ModuleRegistryService $registry)
    {
        if (!session('admin_logado')) return redirect('/login');

        $page = (string) $request->query('p', 'dashboard');
        $role = $registry->role();
        $module = $registry->module($page, $role);

        if ($page === 'cracha' || $page === 'ficha') return app(AdminController::class)->index($request);
        if ($page === 'badge-studio') return response()->view('modules.badge-studio')->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        if ($page === 'identity-directory') return app(IdentityDirectoryController::class)->dashboard($request);

        if (!$module) return redirect('/dashboard?p=dashboard')->with('swal_error', 'Módulo indisponível para o seu perfil.');

        $legacyResponse = app(AdminController::class)->index($request);
        $legacyData = $legacyResponse instanceof View ? $legacyResponse->getData() : [];
        $dados = $legacyData['dados'] ?? [];
        $cfgGlobal = $legacyData['cfg_global'] ?? [];

        $module['description'] = $this->description($page);
        $base = [
            'page' => $page,
            'module' => $module,
            'moduleGroups' => $registry->groups($role),
            'cadcolabVersion' => $registry->version(),
            'dados' => $dados,
            'cfg_global' => $cfgGlobal,
            'isAdmin' => $legacyData['isAdmin'] ?? false,
            'isTI' => $legacyData['isTI'] ?? false,
            'isRH' => $legacyData['isRH'] ?? false,
        ];

        $view = match ($page) {
            'dashboard' => 'v5.dashboard',
            'colaboradores' => 'v5.collaborators',
            'unidades', 'setores', 'cargos', 'grupos', 'sistemas', 'usuarios' => 'v5.collection',
            'auditoria', 'erros' => 'v5.logs',
            'configuracoes' => 'v5.settings',
            'changelog' => 'v5.changelog',
            default => 'v5.hub',
        };

        if ($page === 'changelog') $base['versions'] = app(ReleaseNotesService::class)->all();
        if ($view === 'v5.collection') {
            $base['tableName'] = match ($page) {
                'sistemas' => 'sistemas_hc',
                'usuarios' => 'usuarios_admin',
                'grupos' => 'perfis_acesso',
                default => $page,
            };
        }

        return response()->view($view, $base)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function description(string $page): string
    {
        return match ($page) {
            'dashboard' => 'Visão executiva de pessoas, identidades, acessos, integrações e governança.',
            'colaboradores' => 'Gestão 360º do ciclo de vida do colaborador e das identidades corporativas.',
            'unidades' => 'Estrutura corporativa, parâmetros e modelos por unidade.',
            'setores' => 'Organização dos setores e vínculos funcionais.',
            'cargos' => 'Cargos, funções e políticas relacionadas.',
            'grupos' => 'Perfis RBAC e políticas de acesso corporativo.',
            'sistemas' => 'Catálogo de aplicações SSO e acessos corporativos.',
            'usuarios' => 'Operadores administrativos e privilégios do CADCOLAB.',
            'microsoft365' => 'Provisionamento, licenças, estado da conta e diagnóstico Microsoft Graph.',
            'google-workspace' => 'Provisionamento e sincronização de identidades Google Workspace.',
            'status' => 'Saúde de Microsoft 365, Google, SMTP, WhatsApp e demais integrações.',
            'robos' => 'Automações operacionais e regras de jornada.',
            'lifecycle' => 'Onboarding, movimentações, offboarding e trilha de provisionamento.',
            'comunicacoes' => 'Central unificada de e-mail e WhatsApp.',
            'email-logs' => 'Histórico auditável de mensagens enviadas e falhas.',
            'whatsapp' => 'Conversas, templates, webhooks e status da Meta Cloud API.',
            'relatorios' => 'Relatórios executivos e operacionais por domínio.',
            'auditoria' => 'Trilha de auditoria, operadores, eventos e rastreabilidade.',
            'insights' => 'Score de governança, recomendações e riscos detectados localmente.',
            'erros' => 'Diagnóstico de falhas e observabilidade técnica.',
            'configuracoes' => 'Credenciais, políticas, integrações e parâmetros globais.',
            'ajuda' => 'Base de conhecimento e orientação operacional.',
            'changelog' => 'Histórico completo de evolução da plataforma.',
            default => 'Módulo CADCOLAB Enterprise.',
        };
    }
}
