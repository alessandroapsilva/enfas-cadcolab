<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function testarAssinatura(Request $request)
    {
        $email = $request->email;
        return response()->json([
            'sucesso' => false,
            'mensagem' => "⚠️ A Microsoft descontinuou a API de assinatura de e-mail.\n\n📧 Como configurar sua assinatura manualmente no Outlook:\n\n1. Acesse outlook.office.com\n2. Clique em Configurações ⚙️ > Ver todas as configurações\n3. E-mail > Layout > Assinatura de e-mail\n4. Cole o HTML da sua assinatura e salve\n\n🆘 Precisa de ajuda? Contate o TI da ENFAS"
        ]);
    }
}
