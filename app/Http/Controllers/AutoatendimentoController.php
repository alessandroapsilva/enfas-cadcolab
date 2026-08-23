<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};

class AutoatendimentoController extends Controller {
    public function index(Request $request) {
        $aviso = DB::table('configuracoes')->where('chave', 'aviso_login')->value('valor');
        return view('autoatendimento', ['aviso' => $aviso, 'm' => 'home']);
    }

    public function handle(Request $request) {
        if($request->has('login_colaborador')) {
            $u = trim($request->usuario); $p = trim($request->senha);
            $res = DB::table('pre_registros')->where('username_criado', $u)->orWhere('cpf', $u)->first();
            if($res && (Hash::check($p, $res->senha_criada) || $p === $res->senha_criada)) {
                if($res->status != 'ativo') return back()->with('erro', "Conta inativa ou bloqueada.");
                session(['colab' => (array)$res]);
                return redirect($res->termo_aceito == 1 ? "/Conta/Perfil" : "/Conta/Termos");
            } return back()->with('erro', "Credenciais incorretas.");
        }
        return back();
    }
}
