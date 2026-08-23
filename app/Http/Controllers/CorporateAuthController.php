<?php

namespace App\Http\Controllers;

use App\Services\LdapDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CorporateAuthController extends Controller
{
    public function __construct(private readonly LdapDirectoryService $directory) {}

    public function login(Request $request)
    {
        $cfg_global = Schema::hasTable('configuracoes') ? DB::table('configuracoes')->pluck('valor', 'chave')->toArray() : [];
        if ($request->isMethod('get')) return view('login', compact('cfg_global'));

        $credentials = $request->validate([
            'u' => ['required', 'string', 'max:190'],
            'p' => ['required', 'string', 'max:1024'],
        ]);

        if ($this->directory->isEnabled()) {
            try {
                $ldapUser = $this->directory->authenticate($credentials['u'], $credentials['p']);
                if ($ldapUser) {
                    $this->directory->persistUser($ldapUser);
                    $admin = DB::table('usuarios_admin')->where('usuario', $ldapUser['username'])->first();
                    if ($admin && ($admin->ativo ?? true)) {
                        DB::table('usuarios_admin')->where('id', $admin->id)->update(['last_login_at' => now()]);
                        session([
                            'admin_logado' => true,
                            'admin_perfil' => $admin->perfil,
                            'admin_nome' => $admin->nome,
                            'admin_id' => $admin->id,
                            'identity_source' => 'ldap',
                        ]);
                        $this->audit($admin->nome, 'Login LDAP', 'Autenticação corporativa realizada via LDAP/AD.');
                        return redirect('/dashboard');
                    }
                }
            } catch (\Throwable $e) {
                $this->audit($credentials['u'], 'Falha LDAP', $e->getMessage());
                if (!config('identity.local_fallback')) {
                    return back()->withInput($request->only('u'))->with('erro', 'Não foi possível autenticar no diretório corporativo.');
                }
            }
        }

        if (!config('identity.local_fallback')) {
            return back()->withInput($request->only('u'))->with('erro', 'Credenciais inválidas.');
        }

        $admin = DB::table('usuarios_admin')->where('usuario', $credentials['u'])->first();
        if ($admin && ($admin->identity_source ?? 'local') === 'local' && ($admin->ativo ?? true) && Hash::check($credentials['p'], $admin->senha)) {
            // Bloqueia a credencial histórica conhecida, mesmo em bases antigas.
            if ($admin->usuario === 'admin' && Hash::check('Enfas@2026', $admin->senha)) {
                return back()->with('erro', 'A conta administrativa legada precisa ter a senha redefinida antes do uso.');
            }

            if (Schema::hasColumn('usuarios_admin', 'last_login_at')) {
                DB::table('usuarios_admin')->where('id', $admin->id)->update(['last_login_at' => now()]);
            }
            session([
                'admin_logado' => true,
                'admin_perfil' => $admin->perfil,
                'admin_nome' => $admin->nome,
                'admin_id' => $admin->id,
                'identity_source' => 'local',
            ]);
            $this->audit($admin->nome, 'Login local', 'Autenticação administrativa local de contingência.');
            return redirect('/dashboard');
        }

        return back()->withInput($request->only('u'))->with('erro', 'Credenciais inválidas.');
    }

    public function logout()
    {
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }

    private function audit(string $user, string $action, string $details): void
    {
        if (!Schema::hasTable('logs_auditoria')) return;
        DB::table('logs_auditoria')->insert([
            'usuario_admin' => $user,
            'acao' => $action,
            'detalhes' => $details,
            'codigo_controle' => 'IAM-'.now()->format('YmdHis').'-'.random_int(1000, 9999),
            'data_hora' => now(),
        ]);
    }
}
