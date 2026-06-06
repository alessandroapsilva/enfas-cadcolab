<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CloudIntegrationService {
    public static function log($usuario, $acao, $detalhes) {
        $letras = strlen(preg_replace('/[^a-zA-Z]/', '', $usuario));
        $soma = (int)date('Y') + (int)date('m') + (int)date('d') + (int)date('H') + (int)date('i') + (int)date('s') + $letras;
        $cod = 'ICP-BR.' . date('YmdHis') . '.' . $soma; 
        DB::table('logs_auditoria')->insert(['usuario_admin'=>$usuario,'acao'=>$acao,'detalhes'=>$detalhes,'codigo_controle'=>$cod,'data_hora'=>now()]);
    }
    public static function erro($modulo, $mensagem) { DB::table('logs_erros')->insert(['modulo'=>$modulo,'mensagem'=>$mensagem,'data_hora'=>now()]); }

    public static function enviarSMTP($to, $subject, $message, $cfg) {
        if(empty($cfg['smtp_host']) || empty($cfg['smtp_user'])) return false;
        $host = $cfg['smtp_host']; $port = (int)($cfg['smtp_port'] ?: 587); 
        $user = $cfg['smtp_user']; $pass = $cfg['smtp_pass']; $from = !empty($cfg['mail_from']) ? $cfg['mail_from'] : $user;
        $context = stream_context_create(['ssl' => [ 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ]]);
        $socket = @stream_socket_client(($port == 465 ? "ssl://" : "tcp://") . $host . ":" . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) return false; stream_set_timeout($socket, 15);
        $read = function($s) { $d = ''; while($str = @fgets($s, 515)) { $d .= $str; if(substr($str, 3, 1) == ' ') break; } return $d; };
        $read($socket); fwrite($socket, "EHLO $host\r\n"); $read($socket);
        if($port == 587 || $port == 25) { fwrite($socket, "STARTTLS\r\n"); $read($socket); @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT); fwrite($socket, "EHLO $host\r\n"); $read($socket); }
        fwrite($socket, "AUTH LOGIN\r\n"); $read($socket); fwrite($socket, base64_encode($user)."\r\n"); $read($socket); fwrite($socket, base64_encode($pass)."\r\n"); $auth = $read($socket);
        if(strpos($auth, '235') === false) return false;
        fwrite($socket, "MAIL FROM: <$from>\r\n"); $read($socket); fwrite($socket, "RCPT TO: <$to>\r\n"); $read($socket); fwrite($socket, "DATA\r\n"); $read($socket);
        $h = "Date: ".date("r")."\r\nFrom: =?UTF-8?B?".base64_encode("Clínica ENFAS")."?= <$from>\r\nTo: <$to>\r\nSubject: =?UTF-8?B?".base64_encode($subject)."?=\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n";
        fwrite($socket, $h . $message . "\r\n.\r\n"); $last = $read($socket); fwrite($socket, "QUIT\r\n"); fclose($socket);
        return strpos($last, '250') !== false;
    }

    public static function sincronizarAssinaturaM365($email, $html, $cfg) {
        if(empty($cfg['m365_tenant'])) return ['sucesso'=>false, 'erro'=>'Chaves ausentes.']; 
        $tk = Http::asForm()->post("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token", ['client_id'=>$cfg['m365_client'], 'scope'=>'https://graph.microsoft.com/.default', 'client_secret'=>$cfg['m365_secret'], 'grant_type'=>'client_credentials']);
        if(!$tk->successful()) return ['sucesso'=>false, 'erro'=>'Falha no Token.'];
        $resp = Http::withToken($tk->json()['access_token'])->patch("https://graph.microsoft.com/v1.0/users/$email/mailboxSettings", [ "signature" => [ "messageQuoteText" => "", "messageQuoteTextLocation" => "bottom", "signature" => mb_convert_encoding($html, 'UTF-8', 'auto'), "type" => "html" ] ]);
        if($resp->successful()) return ['sucesso'=>true]; self::erro('M365 Assinatura', $resp->body()); return ['sucesso'=>false];
    }

    public static function gerenciarM365($email, $acao, $senha=null, $dados=[], $cfg=[]) {
        if(empty($cfg['m365_tenant'])) return ['sucesso'=>false];
        $tk = Http::asForm()->post("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token", ['client_id'=>$cfg['m365_client'], 'scope'=>'https://graph.microsoft.com/.default', 'client_secret'=>$cfg['m365_secret'], 'grant_type'=>'client_credentials']);
        if(!$tk->successful()) return ['sucesso'=>false]; $token = $tk->json()['access_token'];
        $ep = "https://graph.microsoft.com/v1.0/users/$email"; $met = "patch"; $p = [];
        if($acao == 'criar') {
            $met = "post"; $ep = "https://graph.microsoft.com/v1.0/users";
            $p = ["accountEnabled"=>true, "displayName"=>$dados['nome']??'', "mailNickname"=>explode('@', $email)[0], "userPrincipalName"=>$email, "passwordProfile"=>["forceChangePasswordNextSignIn"=>false, "password"=>$senha], "usageLocation"=>"BR"];
            if(!empty($dados['cargo'])) $p['jobTitle'] = $dados['cargo']; if(!empty($dados['setor'])) $p['department'] = $dados['setor']; if(!empty($dados['telefone'])) $p['mobilePhone'] = "+55".preg_replace('/[^0-9]/','',$dados['telefone']); $p['companyName'] = "Clínica ENFAS";
        } elseif($acao == 'bloquear') { $p = ['accountEnabled'=>false]; }
        elseif($acao == 'atualizar') { if(!empty($dados['nome'])) $p['displayName']=$dados['nome']; if(!empty($dados['telefone'])) $p['mobilePhone']="+55".preg_replace('/[^0-9]/','',$dados['telefone']); $p['accountEnabled']=true; $p['usageLocation']="BR"; }
        elseif($acao == 'reset_senha') { $p = ['passwordProfile'=>['forceChangePasswordNextSignIn'=>false, 'password'=>$senha]]; }
        elseif($acao == 'excluir') { $met = "delete"; }
        $r = Http::withToken($token)->$met($ep, $p);
        if($r->successful()) {
            if($acao == 'bloquear') {
                if(!empty($dados['gestor_email'])) Http::withToken($token)->patch("$ep/mailboxSettings", ["forwarding"=>["emailAddress"=>["address"=>$dados['gestor_email']],"deliverToMailboxAndForward"=>false,"status"=>"enabled"]]);
                if(!empty($cfg['m365_sku_basic'])) Http::withToken($token)->post("$ep/assignLicense", ['addLicenses'=>[], 'removeLicenses'=>[$cfg['m365_sku_basic']]]);
            }
            if($acao == 'atualizar') Http::withToken($token)->patch("$ep/mailboxSettings", ["forwarding"=>["status"=>"disabled"]]);
            if(isset($dados['m365_perfil']) && in_array($acao,['atualizar','bloquear','criar']) && !empty($cfg['m365_sku_basic'])) {
                $sku = $cfg['m365_sku_basic']; $add = []; $rem = [];
                if($acao == 'bloquear' || $dados['m365_perfil'] != 'Basic') $rem[] = $sku; else $add[] = ["skuId"=>$sku];
                Http::withToken($token)->post("$ep/assignLicense", ['addLicenses'=>$add, 'removeLicenses'=>$rem]);
            } return ['sucesso'=>true];
        } self::erro('M365', $r->body()); return ['sucesso'=>false];
    }

    public static function gerenciarGoogle($email, $acao, $senha=null, $dados=[], $cfg=[]) {
        if(empty($cfg['gw_json'])) return ['sucesso'=>false]; $k = @json_decode($cfg['gw_json'], true); if(!$k) return ['sucesso'=>false];
        $h = str_replace(['+','/','='],['-','_',''],base64_encode(json_encode(['alg'=>'RS256','typ'=>'JWT'])));
        $c = str_replace(['+','/','='],['-','_',''],base64_encode(json_encode(['iss'=>$k['client_email'],'scope'=>'https://www.googleapis.com/auth/admin.directory.user','aud'=>$k['token_uri'],'exp'=>time()+3600,'iat'=>time()])));
        openssl_sign("$h.$c", $sig, $k['private_key'], "sha256WithRSAEncryption"); $jwt = "$h.$c.".str_replace(['+','/','='],['-','_',''],base64_encode($sig));
        $tk = Http::asForm()->post($k['token_uri'], ['grant_type'=>'urn:ietf:params:oauth:grant-type:jwt-bearer','assertion'=>$jwt]);
        if(!$tk->successful()) return ['sucesso'=>false]; $token = $tk->json()['access_token'];
        $ep = "https://admin.googleapis.com/admin/directory/v1/users/$email"; $met = "put"; $p = [];
        if($acao == 'criar') { $met = "post"; $ep = "https://admin.googleapis.com/admin/directory/v1/users"; $pts = explode(' ', $dados['nome']??''); $p = ["primaryEmail"=>$email,"password"=>$senha,"name"=>["givenName"=>$pts[0],"familyName"=>end($pts)],"changePasswordAtNextLogin"=>false]; }
        elseif($acao == 'bloquear') { $p = ["suspended"=>true]; } elseif(in_array($acao,['atualizar','ativar'])) { $p = ["suspended"=>false]; }
        elseif($acao == 'reset_senha') { $p = ["password"=>$senha,"suspended"=>false]; } elseif($acao == 'excluir') { $met = "delete"; }
        $r = Http::withToken($token)->$met($ep, $p); return ['sucesso'=>$r->successful()];
    }

    public static function dispararWhatsApp($tel, $tpl, $vars, $cfg) {
        if(empty($cfg['wp_token']) || empty($cfg['wp_phone_id'])) return false;
        $comp = [["type"=>"body","parameters"=>[["type"=>"text","text"=>$vars[0]]]]];
        if(isset($vars[1])) $comp[] = ["type"=>"button","sub_type"=>"url","index"=>"0","parameters"=>[["type"=>"text","text"=>$vars[1]]]];
        $p = ["messaging_product"=>"whatsapp","to"=>preg_replace('/[^0-9]/','',$tel),"type"=>"template","template"=>["name"=>$tpl,"language"=>["code"=>"pt_BR"],"components"=>$comp]];
        return Http::withToken($cfg['wp_token'])->post("https://graph.facebook.com/v18.0/{$cfg['wp_phone_id']}/messages", $p)->successful();
    }
}
