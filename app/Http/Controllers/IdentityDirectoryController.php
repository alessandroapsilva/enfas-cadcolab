<?php

namespace App\Http\Controllers;

use App\Services\LdapDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IdentityDirectoryController extends Controller
{
    public function __construct(private readonly LdapDirectoryService $directory) {}

    private function authorizeDirectory(): void
    {
        abort_unless(session('admin_logado'), 401);
        abort_unless(in_array(session('admin_perfil'), ['TI', 'Admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeDirectory();

        $connection = $this->directory->testConnection();
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
            'enabled' => $this->directory->isEnabled(),
            'ldap' => config('identity.ldap'),
            'profiles' => config('identity.profiles'),
        ]);
    }

    public function test()
    {
        $this->authorizeDirectory();
        $result = $this->directory->testConnection();
        $this->audit('Teste LDAP', $result['success'] ? 'Conexão validada.' : 'Falha: '.$result['message']);

        return back()->with($result['success'] ? 'swal' : 'swal_error', $result['message']);
    }

    public function sync()
    {
        $this->authorizeDirectory();

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
