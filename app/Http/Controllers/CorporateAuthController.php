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
        $cfg_global = Schema::hasTable('configuracoes') ? DB::table('configuracoes')->pluck('valor','chave')->toArray() : [];
        if ($request->isMethod('get')) return view('login', compact('cfg_global'));

        $c = $request->validate(['u'=>['required','string','max:190'],'p'=>['required','string','max:1024']]);

        if ($this->directory->isEnabled()) {
            try {
                $ldapUser = $this->directory->authenticate($c['u'],$c['p']);
                if ($ldapUser) {
                    $this->directory->persistUser($ldapUser);
                    $admin = DB::table('usuarios_admin')->where('usuario',$ldapUser['username'])->first();
                    if ($admin && ($admin->ativo ?? true)) {
                        $request->session()->regenerate();
                        $request->session()->put(['admin_logado'=>true,'admin_perfil'=>$admin->perfil,'admin_nome'=>$admin->nome,'admin_id'=>$admin->id,'identity_source'=>'ldap']);
                        return redirect('/dashboard');
                    }
                }
            } catch (\Throwable $e) {
                if (!config('identity.local_fallback')) return back()->withInput($request->only('u'))->with('erro','Não foi possível autenticar no diretório corporativo.');
            }
        }

        if (!config('identity.local_fallback')) return back()->withInput($request->only('u'))->with('erro','Credenciais inválidas.');
        $admin = DB::table('usuarios_admin')->where('usuario',$c['u'])->first();
        if ($admin && ($admin->identity_source ?? 'local')==='local' && ($admin->ativo ?? true) && Hash::check($c['p'],$admin->senha)) {
            $request->session()->regenerate();
            $request->session()->put(['admin_logado'=>true,'admin_perfil'=>$admin->perfil,'admin_nome'=>$admin->nome,'admin_id'=>$admin->id,'identity_source'=>'local']);
            return redirect('/dashboard');
        }
        return back()->withInput($request->only('u'))->with('erro','Credenciais inválidas.');
    }

    public function logout()
    {
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }
}
