<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModelosEmailController extends Controller
{
    private function authorizeTI(Request $request): void
    {
        abort_unless($request->session()->get('admin_logado') && $request->session()->get('admin_perfil') === 'TI', 403);
    }
    private function defaults(): array
    {
        return [
            'mail_from_name' => 'AutoAtendimento',
            'mail_from' => 'naoresponda@enfas.com.br',
            'reset_email_assunto' => 'Solicitação de alteração de senha do email corporativo ENFAS',
            'reset_email_titulo' => 'Solicitação de alteração de senha do email corporativo ENFAS',
            'reset_email_subtitulo' => 'Esqueci minha senha - AutoAtendimento @enfas.com.br',
            'reset_email_aviso' => '*** Este é um E-mail automático não é necessário respondê-lo ***',
            'reset_email_instrucao' => 'Clique no botão abaixo para ser redirecionado para nosso sistema e alterar sua senha:',
            'reset_email_botao' => 'Clique aqui',
            'reset_email_orientacao' => 'Caso não tenha solicitado a alteração de senha entre em contato com a informática local.',
            'reset_email_assinatura' => 'Tecnologia da Informação — ENFAS',
            'reset_email_validade' => '30',
        ];
    }
    public function index(Request $request)
    {
        $this->authorizeTI($request);
        $defaults = $this->defaults();
        $values = array_replace($defaults, DB::table('configuracoes')->whereIn('chave',array_keys($defaults))->pluck('valor','chave')->all());
        return view('configuracoes.modelos-email',compact('values'));
    }
    public function save(Request $request)
    {
        $this->authorizeTI($request);
        $rules = array_fill_keys(array_keys($this->defaults()), 'required|string|max:2000');
        $rules['mail_from'] = 'required|email|max:254';
        $rules['mail_from_name'] = 'required|string|max:150|not_regex:/[\\r\\n]/';
        $rules['reset_email_assunto'] = 'required|string|max:200|not_regex:/[\\r\\n]/';
        $rules['reset_email_validade'] = 'required|integer|min:5|max:60';
        $data = $request->validate($rules);
        DB::transaction(function () use ($data,$request) {
            foreach ($data as $key => $value) {
                DB::table('configuracoes')->updateOrInsert(['chave'=>$key],['valor'=>(string)$value,'updated_at'=>now()]);
            }
            DB::table('logs_auditoria')->insert(['usuario_admin'=>(string)$request->session()->get('admin_nome','TI'),'acao'=>'Atualização de modelo de e-mail','detalhes'=>'Configurações de recuperação de senha','data_hora'=>now()]);
        });
        return redirect('/configuracoes/modelos-email')->with('success','Configurações salvas.');
    }
}
