<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CloudIntegrationService
{
    public static function log($usuario,$acao,$detalhes)
    {
        $letras=strlen(preg_replace('/[^a-zA-Z]/','',$usuario));
        $soma=(int)date('Y')+(int)date('m')+(int)date('d')+(int)date('H')+(int)date('i')+(int)date('s')+$letras;
        DB::table('logs_auditoria')->insert(['usuario_admin'=>$usuario,'acao'=>$acao,'detalhes'=>$detalhes,'codigo_controle'=>'ICP-BR.'.date('YmdHis').'.'.$soma,'data_hora'=>now()]);
    }

    public static function erro($modulo,$mensagem)
    {
        DB::table('logs_erros')->insert(['modulo'=>$modulo,'mensagem'=>$mensagem,'data_hora'=>now()]);
    }

    public static function gerenciarM365($email,$acao,$senha=null,$dados=[],$cfg=[])
    {
        return Microsoft365Service::manage($email,$acao,$senha,$dados,$cfg);
    }

    public static function diagnosticoM365($email,$cfg=[])
    {
        return Microsoft365Service::diagnose($email,$cfg);
    }

    public static function gerenciarGoogle($email,$acao,$senha=null,$dados=[],$cfg=[])
    {
        return GoogleWorkspaceService::manage($email,$acao,$senha,$dados,$cfg);
    }

    public static function diagnosticoGoogle($email,$cfg=[])
    {
        return GoogleWorkspaceService::diagnose($email,$cfg);
    }

    public static function enviarSMTP($to,$subject,$message,$cfg)
    {
        if(empty($cfg['smtp_host'])||empty($cfg['smtp_user'])) return false;
        $host=$cfg['smtp_host'];$port=(int)($cfg['smtp_port']?:587);$user=$cfg['smtp_user'];$pass=$cfg['smtp_pass'];$from=!empty($cfg['mail_from'])?$cfg['mail_from']:$user;
        $context=stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]]);
        $socket=@stream_socket_client(($port==465?'ssl://':'tcp://').$host.':'.$port,$errno,$errstr,15,STREAM_CLIENT_CONNECT,$context);if(!$socket)return false;stream_set_timeout($socket,15);
        $read=function($s){$d='';while($str=@fgets($s,515)){$d.=$str;if(substr($str,3,1)==' ')break;}return $d;};
        $read($socket);fwrite($socket,"EHLO $host\r\n");$read($socket);if($port==587||$port==25){fwrite($socket,"STARTTLS\r\n");$read($socket);@stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT);fwrite($socket,"EHLO $host\r\n");$read($socket);}fwrite($socket,"AUTH LOGIN\r\n");$read($socket);fwrite($socket,base64_encode($user)."\r\n");$read($socket);fwrite($socket,base64_encode($pass)."\r\n");$auth=$read($socket);if(strpos($auth,'235')===false)return false;
        fwrite($socket,"MAIL FROM: <$from>\r\n");$read($socket);fwrite($socket,"RCPT TO: <$to>\r\n");$read($socket);fwrite($socket,"DATA\r\n");$read($socket);$h="Date: ".date('r')."\r\nFrom: =?UTF-8?B?".base64_encode('Clínica ENFAS')."?= <$from>\r\nTo: <$to>\r\nSubject: =?UTF-8?B?".base64_encode($subject)."?=\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n";fwrite($socket,$h.$message."\r\n.\r\n");$last=$read($socket);fwrite($socket,"QUIT\r\n");fclose($socket);return strpos($last,'250')!==false;
    }

    public static function dispararWhatsApp($tel,$tpl,$vars,$cfg)
    {
        return app(WhatsAppService::class)->sendTemplate($tel,$tpl,$vars,$cfg);
    }

    public static function sincronizarAssinaturaM365($email,$html,$cfg)
    {
        return ['sucesso'=>false,'erro'=>'A API de assinatura do Outlook não é suportada pelo Microsoft Graph atual.'];
    }
}
