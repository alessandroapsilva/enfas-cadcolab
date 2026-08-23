<?php

namespace App\Http\Controllers;

use App\Services\IdentitySettingsService;
use App\Services\LdapDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class IdentityDirectoryController extends Controller
{
    public function __construct(
        private readonly LdapDirectoryService $directory,
        private readonly IdentitySettingsService $settings,
    ) {}

    private function authorizeDirectory(): void
    {
        abort_unless(session('admin_logado'), 401);
        abort_unless(in_array(session('admin_perfil'), ['TI', 'Admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeDirectory();
        $form = $this->settings->allForForm();

        $connection = ['success' => false, 'message' => 'Configure e habilite o LDAP para testar a conexão.'];
        if ($form['enabled'] && !empty($form['ldap']['host'])) {
            $connection = $this->directory->testConnection();
        }

        $users = collect();
        $groups = collect();

        if (Schema::hasTable('identity_directory_users')) {
            $users = DB::table('identity_directory_users')
                ->orderByDesc('is_active')
                ->orderBy('display_name')
                ->limit(500)
                ->get();
        }

        if (Schema::hasTable('identity_directory_groups')) {
            $groups = DB::table('identity_directory_groups')->orderBy('name')->limit(500)->get();
        }

        $stats = [
            'users' => $users->count(),
            'active' => $users->where('is_active', 1)->count(),
            'inactive' => $users->where('is_active', 0)->count(),
            'groups' => $groups->count(),
            'last_sync' => optional($users->sortByDesc('synced_at')->first())->synced_at,
        ];

        return view('identity.directory', [
            'connection' => $connection,
            'users' => $users,
            'groups' => $groups,
            'stats' => $stats,
            'enabled' => $form['enabled'],
            'ldap' => $form['ldap'],
            'profiles' => $form['profiles'],
            'syncOnLogin' => $form['sync_on_login'],
            'localFallback' => $form['local_fallback'],
            'bindPasswordConfigured' => $form['bind_password_configured'],
        ]);
    }

    public function saveSettings(Request $request)
    {
        $this->authorizeDirectory();

        $data = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'timeout' => ['required', 'integer', 'between:1,60'],
            'base_dn' => ['required', 'string', 'max:1000'],
            'users_dn' => ['required', 'string', 'max:1000'],
            'groups_dn' => ['required', 'string', 'max:1000'],
            'bind_dn' => ['required', 'string', 'max:1000'],
            'bind_password' => ['nullable', 'string', 'max:2000'],
            'account_suffix' => ['nullable', 'string', 'max:255'],
            'username_attribute' => ['required', 'string', 'max:128'],
            'user_filter' => ['required', 'string', 'max:1000'],
            'group_filter' => ['required', 'string', 'max:1000'],
            'attr_username' => ['required', 'string', 'max:128'],
            'attr_upn' => ['required', 'string', 'max:128'],
            'attr_name' => ['required', 'string', 'max:128'],
            'attr_email' => ['required', 'string', 'max:128'],
            'attr_employee_id' => ['required', 'string', 'max:128'],
            'attr_department' => ['required', 'string', 'max:128'],
            'attr_title' => ['required', 'string', 'max:128'],
            'attr_phone' => ['required', 'string', 'max:128'],
            'attr_mobile' => ['required', 'string', 'max:128'],
            'attr_manager' => ['required', 'string', 'max:128'],
            'attr_groups' => ['required', 'string', 'max:128'],
            'attr_guid' => ['required', 'string', 'max:128'],
            'attr_account_control' => ['required', 'string', 'max:128'],
            'admin_groups' => ['nullable', 'string', 'max:1000'],
            'ti_groups' => ['nullable', 'string', 'max:1000'],
            'rh_groups' => ['nullable', 'string', 'max:1000'],
            'default_profile' => ['required', Rule::in(['Admin', 'TI', 'RH', 'Operador'])],
        ]);

        $values = [
            'identity.enabled' => $request->boolean('enabled'),
            'identity.sync_on_login' => $request->boolean('sync_on_login'),
            'identity.local_fallback' => $request->boolean('local_fallback'),
            'ldap.host' => $data['host'],
            'ldap.port' => $data['port'],
            'ldap.ssl' => $request->boolean('ssl'),
            'ldap.start_tls' => $request->boolean('start_tls'),
            'ldap.timeout' => $data['timeout'],
            'ldap.base_dn' => $data['base_dn'],
            'ldap.users_dn' => $data['users_dn'],
            'ldap.groups_dn' => $data['groups_dn'],
            'ldap.bind_dn' => $data['bind_dn'],
            'ldap.bind_password' => $data['bind_password'] ?? '',
            'ldap.account_suffix' => $data['account_suffix'] ?? '',
            'ldap.username_attribute' => $data['username_attribute'],
            'ldap.user_filter' => $data['user_filter'],
            'ldap.group_filter' => $data['group_filter'],
            'attr.username' => $data['attr_username'],
            'attr.upn' => $data['attr_upn'],
            'attr.name' => $data['attr_name'],
            'attr.email' => $data['attr_email'],
            'attr.employee_id' => $data['attr_employee_id'],
            'attr.department' => $data['attr_department'],
            'attr.title' => $data['attr_title'],
            'attr.phone' => $data['attr_phone'],
            'attr.mobile' => $data['attr_mobile'],
            'attr.manager' => $data['attr_manager'],
            'attr.groups' => $data['attr_groups'],
            'attr.guid' => $data['attr_guid'],
            'attr.account_control' => $data['attr_account_control'],
            'profiles.admin_groups' => $data['admin_groups'] ?? '',
            'profiles.ti_groups' => $data['ti_groups'] ?? '',
            'profiles.rh_groups' => $data['rh_groups'] ?? '',
            'profiles.default' => $data['default_profile'],
        ];

        if ($request->boolean('ssl') && $request->boolean('start_tls')) {
            return back()->withInput()->with('swal_error', 'Escolha LDAPS ou StartTLS. Não habilite os dois ao mesmo tempo.');
        }

        $this->settings->save($values);
        $this->audit('Configuração LDAP', 'Parâmetros do diretório atualizados pelo painel.');

        return back()->with('swal', 'Configuração LDAP salva com segurança.');
    }

    public function test()
    {
        $this->authorizeDirectory();
        $this->settings->apply();
        $result = $this->directory->testConnection();
        $this->audit('Teste LDAP', $result['success'] ? 'Conexão validada.' : 'Falha: '.$result['message']);

        return back()->with($result['success'] ? 'swal' : 'swal_error', $result['message']);
    }

    public function sync()
    {
        $this->authorizeDirectory();
        $this->settings->apply();

        try {
            $result = $this->directory->syncAll();
            $message = "Diretório sincronizado: {$result['users']} usuários e {$result['groups']} grupos.";
            $this->audit('Sincronização LDAP', $message);
            return back()->with('swal', $message);
        } catch (\Throwable $e) {
            $this->audit('Sincronização LDAP', 'Falha: '.$e->getMessage());
            return back()->with('swal_error', 'Falha na sincronização: '.$e->getMessage());
        }
    }

    private function audit(string $action, string $details): void
    {
        if (!Schema::hasTable('logs_auditoria')) return;
        DB::table('logs_auditoria')->insert([
            'usuario_admin' => session('admin_nome', 'Sistema'),
            'acao' => $action,
            'detalhes' => $details,
            'codigo_controle' => 'IAM-'.now()->format('YmdHis').'-'.random_int(1000, 9999),
            'data_hora' => now(),
        ]);
    }
}
