<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CloudIntegrationService;

class PortalApiController extends Controller {
    
    // Autenticação via JSON (Pronto para JWT/Keycloak no futuro)
    public function login(Request $request) {
        $usuario = preg_replace('/[^0-9]/', '', $request->usuario) ?: $request->usuario;
        $colab = DB::table('colaboradores')->where('cpf', $usuario)->orWhere('username_criado', $usuario)->first();

        if (!$colab) {
            return response()->json(['status' => 'error', 'message' => 'Credenciais não localizadas no RH.'], 401);
        }

        // Regras da Conecta Suite (IAM)
        $horaAtual = (int)date('H');
        if($colab->ferias_status !== 'trabalhando') {
            CloudIntegrationService::log($colab->nome_completo, 'IAM Control', "Bloqueio de férias via API.");
            return response()->json(['status' => 'error', 'message' => 'Acesso bloqueado por status de RH (Férias/Desligado).'], 403);
        }

        // Geração de Token Simulado para o Frontend React
        $token = base64_encode(json_encode(['id' => $colab->id, 'nome' => $colab->nome_completo, 'exp' => time() + 3600]));

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'colaborador' => [
                'id' => $colab->id,
                'nome' => $colab->nome_completo,
                'cpf' => $colab->cpf,
                'status_m365' => $colab->status_m365,
                'setup_pendente' => empty($colab->username_criado)
            ]
        ], 200);
    }

    // Provisionamento Cloud via API
    public function provisionarConta(Request $request) {
        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
        $colab = DB::table('colaboradores')->where('cpf', $cpf)->first();
        
        if(!$colab || !empty($colab->username_criado)) {
            return response()->json(['status' => 'error', 'message' => 'CPF inválido ou conta já existente.'], 400);
        }

        $dominio = DB::table('configuracoes')->where('chave', 'gw_domain')->value('valor') ?? 'enfas.com.br';
        $email = strtolower($request->prefixo) . '@' . $dominio;
        $senha = "Enfas@" . rand(1000, 9999);

        // Atualiza banco e dispara integrações
        DB::table('colaboradores')->where('id', $colab->id)->update(['username_criado' => $email, 'status_m365' => 'ativo']);
        CloudIntegrationService::provisionarM365($colab->nome_completo, $email, $senha, 'criar');
        if(!empty($colab->telefone)) CloudIntegrationService::enviarWhatsAppMeta($colab->telefone, $colab->nome_completo, $senha);

        return response()->json([
            'status' => 'success',
            'message' => 'Identidade Cloud provisionada.',
            'credenciais' => ['email' => $email, 'senha' => $senha]
        ], 201);
    }
}
