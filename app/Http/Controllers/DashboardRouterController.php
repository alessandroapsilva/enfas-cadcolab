<?php

namespace App\Http\Controllers;

use App\Services\ModuleRegistryService;
use App\Services\ReleaseNotesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class DashboardRouterController extends Controller
{
    public function index(Request $request, ModuleRegistryService $registry)
    {
        if (!session('admin_logado')) return redirect('/login');

        // A interface v3 continua sendo a experiência oficial e completa.
        // O shell v5 permanece disponível apenas para desenvolvimento controlado.
        if (config('cadcolab_modules.ui_mode', 'v3') !== 'v5') {
            return app(AdminController::class)->index($request);
        }

        $page = (string) $request->query('p', 'dashboard');
        $role = $registry->role();
        $module = $registry->module($page, $role);

        if ($page === 'cracha' || $page === 'ficha') return app(AdminController::class)->index($request);
        if (!$module) return redirect('/dashboard?p=dashboard')->with('swal_error', 'Módulo indisponível para o seu perfil.');

        $module['description'] = $this->description($page);
        $base = [
            'page' => $page,
            'module' => $module,
            'moduleGroups' => $registry->groups($role),
            'cadcolabVersion' => $registry->version(),
            'dados' => [],
            'cfg_global' => [],
            'isAdmin' => $role === 'admin',
            'isTI' => $role === 'ti',
            'isRH' => in_array($role, ['admin','ti','rh'], true),
        ];

        if ($page === 'identity-directory') {
            $directoryResponse = app(IdentityDirectoryController::class)->dashboard($request);
            $directoryData = $directoryResponse instanceof View ? $directoryResponse->getData() : [];
            $base = array_merge($base, $directoryData);
            return response()->view('v5.identity-directory', $base)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $legacyResponse = app(AdminController::class)->index($request);
        $legacyData = $legacyResponse instanceof View ? $legacyResponse->getData() : [];
        $base['dados'] = $legacyData['dados'] ?? [];
        $base['cfg_global'] = $legacyData['cfg_global'] ?? [];
        $base['isAdmin'] = $legacyData['isAdmin'] ?? $base['isAdmin'];
        $base['isTI'] = $legacyData['isTI'] ?? $base['isTI'];
        $base['isRH'] = $legacyData['isRH'] ?? $base['isRH'];

        $view = match ($page) {
            'dashboard' => 'v5.dashboard',
            'colaboradores' => 'v5.collaborators',
            'unidades', 'setores', 'cargos', 'grupos', 'sistemas', 'usuarios' => 'v5.collection',
            'auditoria', 'erros' => 'v5.logs',
            'relatorios' => 'v5.reports',
            'insights' => 'v5.insights',
            'email-logs' => 'v5.email-logs',
            'configuracoes' => 'v5.settings',
            'badge-studio' => 'modules.badge-studio',
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
            $base['collectionFields'] = $this->collectionFields($base['tableName']);
            $base['collectionRows'] = collect($base['dados']['regs'] ?? [])->mapWithKeys(function ($row) {
                $safe = Arr::except((array) $row, ['senha','password','remember_token','identity_dn','ldap_dn']);
                return isset($safe['id']) ? [(string) $safe['id'] => $safe] : [];
            })->all();
            $base['canManageCollection'] = in_array($role, ['admin','ti'], true)
                || ($role === 'rh' && in_array($page, ['unidades','setores','cargos'], true));
        }

        return response()->view($view, $base)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function collectionFields(string $table): array
    {
        if (! Schema::hasTable($table)) return [];

        $labels = [
            'nome'=>'Nome','email'=>'E-mail','usuario'=>'Usuário','senha'=>'Senha','perfil'=>'Perfil',
            'endereco'=>'Endereço','telefone'=>'Telefone','responsavel'=>'Responsável','ramal'=>'Ramal',
            'perfil_id'=>'Perfil de acesso','link'=>'Endereço da aplicação','descricao'=>'Descrição',
            'ativo'=>'Ativo','url'=>'URL','sigla'=>'Sigla','codigo'=>'Código',
        ];
        $blocked = ['id','created_at','updated_at','identity_source','identity_dn','ldap_dn','last_login_at','pre_registro_id','remember_token'];

        return collect(Schema::getColumnListing($table))
            ->reject(fn ($column) => in_array($column, $blocked, true) || str_contains($column, 'secret') || str_contains($column, 'token'))
            ->map(fn ($column) => [
                'name'=>$column,
                'label'=>$labels[$column] ?? str_replace('_', ' ', ucfirst($column)),
                'type'=>$column === 'senha' ? 'password' : (in_array($column, ['email'], true) ? 'email' : 'text'),
                'required'=>in_array($column, ['nome','usuario'], true),
            ])->values()->all();
    }

    private function description(string $page): string
    {
        return match ($page) {
            'dashboard' => 'Visão executiva de pessoas, identidades, acessos, integrações e governança.',
            'colaboradores' => 'Gestão 360º do ciclo de vida do colaborador e das identidades corporativas.',
            'unidades' => 'Estrutura corporativa, parâmetros e modelos por unidade.',
            'setores' => 'Organização dos setores e vínculos funcionais.',
            'cargos' => 'Cargos, funções e políticas relacionadas.',
            'badge-studio' => 'Designer visual de crachás, credenciais e modelos por unidade.',
            'identity-directory' => 'LDAP, Active Directory, usuários, grupos e sincronização corporativa.',
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
