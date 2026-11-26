<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class TesteEmailController extends Controller
{
    public function enviar(Request $request)
    {
        if (!session('admin_logado') || session('admin_perfil') !== 'TI' || !session('admin_nome')) {
            return response()->json(['message' => 'Acesso restrito ao administrador de TI.'], 403);
        }

        $request->validate(['destinatario' => ['required', 'email:rfc', 'max:254']]);
        $chave = 'teste-email:'.hash('sha256', (string) $request->ip().'|'.session()->getId());
        if (RateLimiter::tooManyAttempts($chave, 3)) {
            return response()->json(['message' => 'Limite de testes atingido. Aguarde um minuto.'], 429);
        }
        RateLimiter::hit($chave, 60);

        $cfg = DB::table('configuracoes')->whereIn('chave', [
            'smtp_host','smtp_port','smtp_user','smtp_pass','mail_from','mail_from_name'
        ])->pluck('valor','chave')->toArray();

        if (empty($cfg['smtp_host']) || empty($cfg['smtp_user']) || empty($cfg['smtp_pass']) || empty($cfg['mail_from'])) {
            return response()->json(['message' => 'Salve primeiro a configuração SMTP completa.'], 422);
        }

        $port = (int) ($cfg['smtp_port'] ?? 587);
        if (!in_array($port, [465, 587], true)) {
            return response()->json(['message' => 'Configure a porta SMTP 465 ou 587.'], 422);
        }

        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $cfg['smtp_host'],
                    'port' => $port,
                    'encryption' => $port === 465 ? 'ssl' : 'tls',
                    'username' => $cfg['smtp_user'],
                    'password' => $cfg['smtp_pass'],
                    'timeout' => 15,
                ],
                'mail.from.address' => $cfg['mail_from'],
                'mail.from.name' => $cfg['mail_from_name'] ?? 'CadColab ENFAS',
            ]);
            Mail::purge('smtp');
            Mail::raw('Este e-mail confirma o teste de envio SMTP do CadColab. Data: '.now('America/Sao_Paulo')->format('d/m/Y H:i:s'), function ($message) use ($request) {
                $message->to($request->input('destinatario'))->subject('CadColab - Teste de e-mail SMTP');
            });
            return response()->json(['message' => 'E-mail de teste enviado. Verifique a caixa de entrada e o spam.']);
        } catch (Throwable $e) {
            Log::warning('Falha no teste SMTP do CadColab', ['tipo' => get_class($e)]);
            return response()->json(['message' => 'Falha no envio SMTP. Confira host, porta, credenciais e permissões do servidor.'], 502);
        } finally {
            Mail::purge('smtp');
        }
    }
}
