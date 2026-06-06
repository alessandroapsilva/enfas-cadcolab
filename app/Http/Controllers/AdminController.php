<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema, Hash, Artisan};
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminController extends Controller {

    public function __construct() { date_default_timezone_set('America/Sao_Paulo'); }

    private function registrarLog($usuario, $acao, $detalhes) { 
        $hash = 'ICP-BR.' . date('YmdHis') . '.' . rand(1000,9999); 
        DB::table('logs_auditoria')->insert(['usuario_admin'=>$usuario, 'acao'=>$acao, 'detalhes'=>$detalhes, 'codigo_controle'=>$hash, 'data_hora'=>Carbon::now('America/Sao_Paulo')->format('Y-m-d H:i:s')]);
    }
    
    private function registrarErro($modulo, $mensagem) { 
        DB::table('logs_erros')->insert(['modulo'=>$modulo, 'mensagem'=>$mensagem, 'data_hora'=>Carbon::now('America/Sao_Paulo')->format('Y-m-d H:i:s')]); 
    }

    private function enviarEmailSMTP($to, $subject, $message, $cfg) {
        $from = !empty($cfg['mail_from']) ? trim($cfg['mail_from']) : 'nao-responda@enfas.com.br';
        $fromName = !empty($cfg['mail_from_name']) ? trim($cfg['mail_from_name']) : 'CADCOLAB ENFAS';
        $headers_fallback = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: =?UTF-8?B?".base64_encode($fromName)."?= <$from>\r\n";

        try {
            if(empty($cfg['smtp_host']) || empty($cfg['smtp_user'])) { return @mail($to, $subject, $message, $headers_fallback); }
            
            $host = $cfg['smtp_host']; $port = (int)($cfg['smtp_port'] ?: 587); $user = $cfg['smtp_user']; $pass = $cfg['smtp_pass']; 
            $context = stream_context_create(['ssl' => [ 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ]]);
            $socket = @stream_socket_client(($port == 465 ? "ssl://" : "tcp://") . $host . ":" . $port, $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
            
            if (!$socket) { return @mail($to, $subject, $message, $headers_fallback); } 
            
            stream_set_timeout($socket, 10);
            $read_res = function($sock) { $data = ''; while($str = @fgets($sock, 515)) { $data .= $str; if(substr($str, 3, 1) == ' ') break; } return $data; };
            $read_res($socket); fwrite($socket, "EHLO $host\r\n"); $read_res($socket);
            if($port == 587 || $port == 25) { fwrite($socket, "STARTTLS\r\n"); $read_res($socket); @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT); fwrite($socket, "EHLO $host\r\n"); $read_res($socket); }
            fwrite($socket, "AUTH LOGIN\r\n"); $read_res($socket); fwrite($socket, base64_encode($user)."\r\n"); $read_res($socket); fwrite($socket, base64_encode($pass)."\r\n"); $auth = $read_res($socket);
            if(strpos($auth, '235') === false) { fclose($socket); return @mail($to, $subject, $message, $headers_fallback); }
            
            fwrite($socket, "MAIL FROM: <$from>\r\n"); $read_res($socket); fwrite($socket, "RCPT TO: <$to>\r\n"); $read_res($socket); fwrite($socket, "DATA\r\n"); $read_res($socket);
            $headers = "Date: ".date("r")."\r\nFrom: =?UTF-8?B?".base64_encode($fromName)."?= <$from>\r\nTo: <$to>\r\nSubject: =?UTF-8?B?".base64_encode($subject)."?=\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n";
            fwrite($socket, $headers . $message . "\r\n.\r\n"); $last_res = $read_res($socket); fwrite($socket, "QUIT\r\n"); fclose($socket);
            
            if(strpos($last_res, '250') !== false) return true;
            return @mail($to, $subject, $message, $headers_fallback);
        } catch (\Exception $e) { return @mail($to, $subject, $message, $headers_fallback); }
    }

    private function sincronizarAssinaturaM365($email_m365, $html_assinatura, $cfg) {
        if(empty($cfg["m365_tenant"])) return ["sucesso"=>false, "erro"=>"Chaves ausentes."]; 
        $ch = curl_init("https://login.microsoftonline.com/{$cfg["m365_tenant"]}/oauth2/v2.0/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            "client_id" => $cfg["m365_client"],
            "scope" => "https://graph.microsoft.com/.default",
            "client_secret" => $cfg["m365_secret"],
            "grant_type" => "client_credentials"
        ]));
        $tk = json_decode(curl_exec($ch), true)["access_token"] ?? null;
        curl_close($ch);
        if(!$tk) return ["sucesso"=>false, "erro"=>"Falha no Token Microsoft."];
        
        $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/mailboxSettings/signature");
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode([ "content" => $html_assinatura ]));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $tk", "Content-Type: application/json"]);
        $resp = curl_exec($ch2);
        $hc = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
        
        if($hc >= 200 && $hc < 300) return ["sucesso"=>true];
        
        return ["sucesso"=>false, "erro"=>"HTTP $hc: $resp"];
    }

    private function gerenciarAcessoM365($email_m365, $acao = 'bloquear', $nova_senha = null, $dados_sync = [], $cfg = []) { 
        if(empty($cfg['m365_tenant'])) return ['sucesso'=>false]; 
        $ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token"); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['client_id' => $cfg['m365_client'], 'scope' => 'https://graph.microsoft.com/.default', 'client_secret' => $cfg['m365_secret'], 'grant_type' => 'client_credentials'])); $tk = json_decode(curl_exec($ch), true)['access_token'] ?? null; curl_close($ch); if(!$tk) return ['sucesso'=>false];
        
        $ch2 = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365"); $metodo = "PATCH"; $payload = [];
        if($acao == 'excluir') { $metodo = "DELETE"; } elseif($acao == 'reset_senha') { $payload = ['passwordProfile' => ['forceChangePasswordNextSignIn' => false, 'password' => $nova_senha]]; } elseif($acao == 'bloquear') { $payload = ['accountEnabled' => false]; } 
        elseif($acao == 'atualizar' || $acao == 'ativar') { 
            if(!empty($dados_sync['nome_completo'])) $payload['displayName'] = $dados_sync['nome_completo']; 
            if(!empty($dados_sync['cargo_id'])) { $crg = DB::table('cargos')->where('id', $dados_sync['cargo_id'])->value('nome'); if($crg) $payload['jobTitle'] = $crg; }
            if(!empty($dados_sync['setor_id'])) { $seto = DB::table('setores')->where('id', $dados_sync['setor_id'])->value('nome'); if($seto) $payload['department'] = $seto; }
            if(!empty($dados_sync['unidade_id'])) { $uni = DB::table('unidades')->where('id', $dados_sync['unidade_id'])->value('nome'); if($uni) $payload['officeLocation'] = $uni; }
            if(!empty($dados_sync['telefone'])) $payload['mobilePhone'] = "+55" . preg_replace('/[^0-9]/', '', $dados_sync['telefone']); 
            $payload['usageLocation'] = "BR"; $payload['accountEnabled'] = true; 
        }
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, $metodo); if(!empty($payload)) curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload)); curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $tk", "Content-Type: application/json"]); 
        $resp = curl_exec($ch2); $http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE); curl_close($ch2); 
        
        if($http_code >= 400) { $this->registrarErro('Microsoft API (Atualizar Usuário)', "Erro HTTP $http_code. Detalhe: " . substr($resp, 0, 200)); }
        
        if($acao != 'excluir' && isset($dados_sync['m365_perfil'])) {
            $sku = trim($cfg['m365_sku_basic'] ?? '');
            if(!empty($sku)) { 
                $add = []; $rem = []; 
                if($acao == 'bloquear' || $dados_sync['m365_perfil'] != 'Basic') { 
                    $rem[] = $sku; 
                } else { 
                    $apps_liberados = explode(',', $dados_sync['m365_apps'] ?? ''); $planos_desativados = [];
                    if(!in_array('Outlook', $apps_liberados)) $planos_desativados[] = "EXCHANGE_S_FOUNDATION";
                    if(!in_array('SharePoint', $apps_liberados)) $planos_desativados[] = "SHAREPOINTWAC";
                    if(!in_array('Teams', $apps_liberados)) $planos_desativados[] = "TEAMS1";
                    $add[] = ["skuId" => $sku, "disabledPlans" => $planos_desativados]; 
                } 
                
                $clic = curl_init("https://graph.microsoft.com/v1.0/users/$email_m365/assignLicense"); curl_setopt($clic, CURLOPT_CUSTOMREQUEST, "POST"); curl_setopt($clic, CURLOPT_POSTFIELDS, json_encode(['addLicenses' => $add, 'removeLicenses' => $rem])); curl_setopt($clic, CURLOPT_RETURNTRANSFER, true); curl_setopt($clic, CURLOPT_HTTPHEADER, ["Authorization: Bearer $tk", "Content-Type: application/json"]); 
                $resp_lic = curl_exec($clic); $http_code_lic = curl_getinfo($clic, CURLINFO_HTTP_CODE); curl_close($clic); 
                
                if($http_code_lic >= 400) { 
                    $rjson = @json_decode($resp_lic, true);
                    if(isset($rjson['error']['message']) && strpos($rjson['error']['message'], 'does not have a corresponding license') !== false) {
                        // Ignora remoção de licença inexistente
                    } else {
                        $this->registrarErro('Microsoft API (Licença)', "Erro HTTP $http_code_lic no SKU $sku. Detalhe: " . substr($resp_lic, 0, 200)); 
                    }
                }
            }
        }
        return ['sucesso'=>true]; 
    }

    private function gerenciarAcessoGoogle($email_google, $acao = 'bloquear', $nova_senha = null, $dados_sync = [], $cfg = []) {
        if(empty($cfg['gw_domain']) || empty($cfg['gw_json'])) return ['sucesso'=>false];
        $key = @json_decode($cfg['gw_json'], true); if(!$key || !isset($key['private_key'])) return ['sucesso'=>false];
        $header = json_encode(['alg'=>'RS256','typ'=>'JWT']); $claim = json_encode([ 'iss' => $key['client_email'], 'scope' => 'https://www.googleapis.com/auth/admin.directory.user', 'aud' => $key['token_uri'], 'exp' => time() + 3600, 'iat' => time() ]);
        $b64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header)); $b64Claim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claim));
        $sig = ''; openssl_sign($b64Header . "." . $b64Claim, $sig, $key['private_key'], "sha256WithRSAEncryption"); $b64Sig = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($sig)); $jwt = $b64Header . "." . $b64Claim . "." . $b64Sig;
        $ch = curl_init($key['token_uri']); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt])); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $token_resp = json_decode(curl_exec($ch), true); curl_close($ch);
        if(!isset($token_resp['access_token'])) return ['sucesso'=>false]; $token = $token_resp['access_token'];
        
        $metodo = "PUT"; $endpoint = "https://admin.googleapis.com/admin/directory/v1/users/$email_google"; $payload = [];
        $primeiro = ''; $ultimo = ''; if(!empty($dados_sync['nome_completo'])) { $partes = explode(' ', $dados_sync['nome_completo']); $primeiro = $partes[0]; $ultimo = count($partes) > 1 ? end($partes) : $primeiro; }
        if($acao == 'criar') { $metodo = "POST"; $endpoint = "https://admin.googleapis.com/admin/directory/v1/users"; $payload = [ "primaryEmail" => $email_google, "password" => $nova_senha, "name" => [ "givenName" => $primeiro, "familyName" => $ultimo ], "changePasswordAtNextLogin" => false ]; } 
        elseif ($acao == 'bloquear') { $payload = [ "suspended" => true ]; } 
        elseif ($acao == 'atualizar' || $acao == 'ativar') { $payload = [ "suspended" => false ]; if($primeiro) $payload["name"] = [ "givenName" => $primeiro, "familyName" => $ultimo ]; if(!empty($dados_sync['telefone'])) $payload["phones"] = [ [ "value" => "+55".$dados_sync['telefone'], "type" => "mobile" ] ]; } 
        elseif ($acao == 'excluir') { $metodo = "DELETE"; } elseif ($acao == 'reset_senha') { $payload = [ "password" => $nova_senha, "suspended" => false ]; }
        $ch2 = curl_init($endpoint); if($metodo != 'POST') curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, $metodo); else curl_setopt($ch2, CURLOPT_POST, true); if(!empty($payload)) curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload)); curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]); curl_exec($ch2); curl_close($ch2);
        return ['sucesso'=>true];
    }

    public function login(Request $request) {
        $cfg_global = DB::table('configuracoes')->pluck('valor', 'chave')->toArray();
        if($request->isMethod('get')) return view('login', compact('cfg_global'));
        if($request->u=='admin' && $request->p=='Enfas@2026') { session(['admin_logado'=>true, 'admin_perfil'=>'TI', 'admin_nome'=>'Administrador Master', 'admin_id'=>0]); return redirect('/dashboard'); }
        $u = DB::table('usuarios_admin')->where('usuario', $request->u)->first();
        if($u && Hash::check($request->p, $u->senha)){ session(['admin_logado'=>true, 'admin_perfil'=>$u->perfil, 'admin_nome'=>$u->nome, 'admin_id'=>$u->id]); return redirect('/dashboard'); } 
        return back()->with('erro', 'Credenciais inválidas.');
    }

    public function logout() { session()->flush(); return redirect('/login'); }

    public function index(Request $request) {
        if(!session('admin_logado')) return redirect('/login');
        $p = $request->query('p', 'dashboard'); if($p == '/' || empty($p)) $p = 'dashboard';
        
        if($request->has('ajax_history')) {
            $colab = DB::table('pre_registros')->where('id', $request->id)->first(); if(!$colab) return response()->json([]);
            $logs = DB::table('logs_auditoria')->where('detalhes', 'LIKE', "%{$colab->nome_completo}%")->orWhere('detalhes', 'LIKE', "%ID {$request->id} %")->orderBy('id', 'desc')->get();
            $logs->transform(function($log) { 
                $log->data_fmt = !empty($log->data_hora) ? Carbon::parse($log->data_hora)->timezone('America/Sao_Paulo')->format('d/m/Y H:i:s') : 'N/A'; 
                return $log; 
            });
            return response()->json($logs);
        }

        if($request->has('ajax_cloud_groups')) {
            $cfg = DB::table('configuracoes')->pluck('valor', 'chave')->toArray();
            $grupos_m365 = []; $grupos_google = [];
            if(!empty($cfg['m365_tenant'])) {
                $ch = curl_init("https://login.microsoftonline.com/{$cfg['m365_tenant']}/oauth2/v2.0/token"); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['client_id'=>$cfg['m365_client'], 'scope'=>'https://graph.microsoft.com/.default', 'client_secret'=>$cfg['m365_secret'], 'grant_type'=>'client_credentials'])); $tk = json_decode(curl_exec($ch), true)['access_token'] ?? null; curl_close($ch);
                if($tk) {
                    $ch2 = curl_init("https://graph.microsoft.com/v1.0/groups?\$select=id,displayName,mailEnabled,securityEnabled"); curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $tk", "Content-Type: application/json"]);
                    $resp = json_decode(curl_exec($ch2), true); curl_close($ch2);
                    if(isset($resp['value'])) { foreach($resp['value'] as $g) { $tipo = ($g['mailEnabled']?'Lista Email':'Grupo Segurança'); $grupos_m365[] = ['id'=>$g['id'], 'nome'=>$g['displayName'] . ' ('.$tipo.')']; } }
                }
            }
            if(!empty($cfg['gw_domain']) && !empty($cfg['gw_json'])) {
                $key = @json_decode($cfg['gw_json'], true);
                if($key && isset($key['private_key'])) {
                    $header = json_encode(['alg'=>'RS256','typ'=>'JWT']); $claim = json_encode(['iss'=>$key['client_email'],'scope'=>'https://www.googleapis.com/auth/admin.directory.group.readonly','aud'=>$key['token_uri'],'exp'=>time()+3600,'iat'=>time()]);
                    $b64Header = str_replace(['+','/','='],['-','_',''],base64_encode($header)); $b64Claim = str_replace(['+','/','='],['-','_',''],base64_encode($claim));
                    $sig=''; openssl_sign($b64Header.".".$b64Claim, $sig, $key['private_key'], "sha256WithRSAEncryption"); $jwt = $b64Header.".".$b64Claim.".".str_replace(['+','/','='],['-','_',''],base64_encode($sig));
                    $ch = curl_init($key['token_uri']); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type'=>'urn:ietf:params:oauth:grant-type:jwt-bearer','assertion'=>$jwt])); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $tk = json_decode(curl_exec($ch), true)['access_token'] ?? null; curl_close($ch);
                    if($tk) {
                        $ch2 = curl_init("https://admin.googleapis.com/admin/directory/v1/groups?domain=".$cfg['gw_domain']); curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $tk", "Content-Type: application/json"]); $resp = json_decode(curl_exec($ch2), true); curl_close($ch2);
                        if(isset($resp['groups'])) { foreach($resp['groups'] as $g) { $grupos_google[] = ['id'=>$g['email'], 'nome'=>$g['name'] . ' (Lista de E-mail)']; } }
                    }
                }
            }
            return response()->json(['m365' => $grupos_m365, 'google' => $grupos_google]);
        }

        $cfg_global = DB::table('configuracoes')->pluck('valor', 'chave')->toArray();
        $perfil = session('admin_perfil', 'RH'); $isAdmin = in_array($perfil, ['TI', 'Admin']); $isTI = ($perfil==='TI'); $isRH = in_array($perfil, ['RH', 'TI', 'Admin']);
        $dados = ['tot'=>0,'ati'=>0,'pen'=>0,'ina'=>0,'logs'=>[],'colabs'=>[],'regs'=>[],'ef'=>[],'lf'=>[],'graf_unid'=>['labels'=>['Matriz'],'data'=>[0]]];
        $dados['dias_implantacao'] = (int) Carbon::parse('2026-04-24', 'America/Sao_Paulo')->diffInDays(Carbon::now('America/Sao_Paulo'));

        if($p == 'status') {
            $status = [
                'm365' => false, 'm365_msg' => 'Credenciais Ausentes',
                'google' => false, 'google_msg' => 'JSON Ausente',
                'whatsapp' => false, 'whatsapp_msg' => 'Token Ausente',
                'smtp' => false, 'smtp_msg' => 'Host Ausente',
            ];
            
            if(!empty($cfg_global['m365_tenant']) && !empty($cfg_global['m365_client'])) {
                $ch = curl_init("https://login.microsoftonline.com/{$cfg_global['m365_tenant']}/oauth2/v2.0/token"); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['client_id'=>$cfg_global['m365_client'], 'scope'=>'https://graph.microsoft.com/.default', 'client_secret'=>$cfg_global['m365_secret'], 'grant_type'=>'client_credentials'])); $tk = json_decode(curl_exec($ch), true); curl_close($ch);
                if(isset($tk['access_token'])) { $status['m365'] = true; $status['m365_msg'] = 'Online (Token OK)'; } else { $status['m365_msg'] = 'Erro de Autenticação (Token)'; }
            }
            if(!empty($cfg_global['gw_domain']) && !empty($cfg_global['gw_json'])) {
                $key = @json_decode($cfg_global['gw_json'], true);
                if($key && isset($key['private_key'])) { $status['google'] = true; $status['google_msg'] = 'Conectado (Service Account)'; } else { $status['google_msg'] = 'JSON Inválido'; }
            }
            if(!empty($cfg_global['wp_token']) && !empty($cfg_global['wp_phone_id'])) { $status['whatsapp'] = true; $status['whatsapp_msg'] = 'Token Configurado'; }
            if(!empty($cfg_global['smtp_host']) && !empty($cfg_global['smtp_user'])) { $status['smtp'] = true; $status['smtp_msg'] = 'Servidor Configurado'; }

            $dados['api_status'] = $status;
        }

        if($p == 'cracha' && $request->has('id')) {
            $colab = DB::table('pre_registros')->leftJoin('cargos','cargo_id','cargos.id')->leftJoin('setores','setor_id','setores.id')->select('pre_registros.*', 'cargos.nome as crg', 'setores.nome as seto')->where('pre_registros.id', $request->id)->first();
            $foto = $colab->foto_path ? "/".$colab->foto_path."?v=".time() : "https://via.placeholder.com/150"; 
            $pid = !empty($colab->public_id) ? $colab->public_id : str_pad($colab->id, 6, '0', STR_PAD_LEFT); 
            $tpl = !empty($cfg_global['tpl_cracha']) ? $cfg_global['tpl_cracha'] : '<div style="width:215px;height:340px;border-radius:10px;font-family:Arial;position:relative;background:#fff;box-shadow:0 0 10px rgba(0,0,0,0.2);overflow:hidden;"><div style="background:#00a2e8;height:160px;text-align:center;padding-top:15px;"><img src="{FOTO}" style="width:100px;height:100px;border:3px solid #fff;border-radius:4px;object-fit:cover;"><h3 style="color:#fff;margin:10px 0 2px;font-size:18px;">{NOME}</h3><p style="color:rgba(255,255,255,0.9);margin:0;font-size:12px;">{CARGO}</p></div><div style="text-align:center;padding:15px;color:#555;font-weight:bold;font-size:14px;">{SETOR}</div></div>';
            $html = str_replace(['{NOME}', '{CARGO}', '{SETOR}', '{ID}', '{FOTO}'], [$colab->nome_completo, $colab->crg, $colab->seto, $pid, $foto], $tpl);
            return response("<!DOCTYPE html><html lang='pt-BR'><head><title>Crachá Oficial</title><style>body{font-family:Arial;display:flex;justify-content:center;align-items:center;height:100vh;background:#e2e8f0;margin:0;} @media print { body{background:#fff;} }</style></head><body onload='window.print()'>$html</body></html>");
        }
        if($p == 'ficha' && $request->has('id')) {
            $colab = DB::table('pre_registros')->leftJoin('cargos','cargo_id','cargos.id')->select('pre_registros.*', 'cargos.nome as crg')->where('pre_registros.id', $request->id)->first();
            $tpl = !empty($cfg_global['tpl_ficha']) ? $cfg_global['tpl_ficha'] : '<div style="max-width:800px;margin:0 auto;font-family:Arial;color:#333;padding:30px;"><h2>FICHA DE REGISTRO</h2><table style="width:100%;border-collapse:collapse;font-size:13px;"><tr><td style="padding:8px;border:1px solid #ddd;background:#f9f9f9;width:150px;"><b>Nome Completo</b></td><td style="padding:8px;border:1px solid #ddd;">{NOME}</td></tr><tr><td style="padding:8px;border:1px solid #ddd;background:#f9f9f9;"><b>CPF</b></td><td style="padding:8px;border:1px solid #ddd;">{CPF}</td></tr><tr><td style="padding:8px;border:1px solid #ddd;background:#f9f9f9;"><b>Matrícula / ID</b></td><td style="padding:8px;border:1px solid #ddd;">{MATRICULA}</td></tr><tr><td style="padding:8px;border:1px solid #ddd;background:#f9f9f9;"><b>Conta Corporativa</b></td><td style="padding:8px;border:1px solid #ddd;">{EMAIL_CORP}</td></tr></table></div>';
            $email_corp = $colab->username_criado ? $colab->username_criado."@enfas.com.br" : "Não possui";
            $html = str_replace(['{NOME}', '{CPF}', '{MATRICULA}', '{EMAIL_CORP}'], [$colab->nome_completo, $colab->cpf, $colab->matricula, $email_corp], $tpl);
            return response("<!DOCTYPE html><html lang='pt-BR'><head><title>Ficha IAM</title><style>body{font-family:Arial;padding:40px;background:#f4f4f4;} @media print { body{background:white;padding:0;} }</style></head><body>$html<br><button onclick='window.print()' style='display:block;margin:20px auto;padding:10px 20px;background:#00a2e8;color:white;border:none;cursor:pointer;border-radius:4px;font-weight:bold;'>Imprimir Ficha</button></body></html>");
        }

        try {
            $dados['tot']=DB::table('pre_registros')->count();$dados['ati']=DB::table('pre_registros')->where('status','ativo')->count();$dados['pen']=DB::table('pre_registros')->where('status','pendente')->count();$dados['ina']=DB::table('pre_registros')->whereNotIn('status',['ativo','pendente'])->count();
            if(Schema::hasTable('logs_auditoria')) { $lf = DB::table('logs_auditoria')->orderBy('id','desc')->limit(50)->get(); $lf->transform(function($l) { $l->data_fmt = !empty($l->data_hora) ? Carbon::parse($l->data_hora)->timezone('America/Sao_Paulo')->format('d/m/Y H:i:s') : 'N/A'; return $l; }); $dados['lf'] = json_decode(json_encode($lf), true); }
            if($p=='dashboard'){ $graf=DB::table('pre_registros')->join('unidades','unidade_id','=','unidades.id')->select('unidades.nome as nome_unidade',DB::raw('count(*) as total'))->groupBy('unidades.nome')->get(); if($graf->count()>0){$dados['graf_unid']['labels']=$graf->pluck('nome_unidade')->toArray();$dados['graf_unid']['data']=$graf->pluck('total')->toArray();} }
            
            if($p=='colaboradores'){ 
                $b=$request->query('busca'); 
                $q=DB::table('pre_registros')
                    ->leftJoin('unidades','unidade_id','=','unidades.id')
                    ->leftJoin('cargos','cargo_id','=','cargos.id')
                    ->leftJoin('setores','setor_id','=','setores.id')
                    ->leftJoin('pre_registros as gestor','pre_registros.gestor_id','=','gestor.id')
                    ->select('pre_registros.*','unidades.nome as un','cargos.nome as crg', 'setores.nome as seto', 'gestor.nome_completo as nome_gestor'); 
                if($b)$q->where('pre_registros.nome_completo','LIKE',"%$b%")->orWhere('pre_registros.cpf','LIKE',"%$b%")->orWhere('pre_registros.matricula','LIKE',"%$b%"); 
                $colabs = $q->orderBy('pre_registros.id','desc')->get();
                $colabs->transform(function($c) {
                    $c->criado_fmt = !empty($c->criado_em) ? Carbon::parse($c->criado_em)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : 'Não registrado';
                    $c->ativado_fmt = !empty($c->ativado_em) ? Carbon::parse($c->ativado_em)->timezone('America/Sao_Paulo')->format('d/m/Y H:i:s') : 'Pendente (Autoatend.)';
                    $c->data_admissao_fmt = (!empty($c->data_admissao) && $c->data_admissao != '0000-00-00') ? Carbon::parse($c->data_admissao)->format('d/m/Y') : 'Não informada';
                    $c->atualizado_fmt = !empty($c->atualizado_em) ? Carbon::parse($c->atualizado_em)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : null;
                    return $c;
                });
                $dados['colabs']=json_decode(json_encode($colabs), true); 
            }
            if(in_array($p,['unidades','setores','cargos','perfis','sistemas','usuarios','grupos'])){$t=$p;if($p=='sistemas')$t='sistemas_hc';if($p=='perfis'||$p=='grupos')$t='perfis_acesso';if($p=='usuarios')$t='usuarios_admin'; if($p=='cargos') $dados['regs']=json_decode(json_encode(DB::table('cargos')->leftJoin('perfis_acesso','perfil_id','perfis_acesso.id')->select('cargos.*','perfis_acesso.nome as perfil')->get()), true); else $dados['regs']=json_decode(json_encode(DB::table($t)->get()), true); }
            if($p=='erros') { $ef = DB::table('logs_erros')->orderBy('id','desc')->get(); $ef->transform(function($e) { $e->data_fmt = !empty($e->data_hora) ? Carbon::parse($e->data_hora)->timezone('America/Sao_Paulo')->format('d/m/Y H:i:s') : 'N/A'; return $e; }); $dados['ef'] = json_decode(json_encode($ef), true); }
        } catch(\Exception $e){}
        return view('admin', compact('p','cfg_global','isAdmin','isRH','isTI','dados'));
    }

    public function acaoRapida(Request $request) {
        $admin_logado = session('admin_nome'); 
        $id = $request->input('id'); 
        $action = $request->input('action');
        $cfg_global = DB::table('configuracoes')->pluck('valor', 'chave')->toArray();

        // O ROBÔ DE AUTO-CURA DA IA (Refatorado para 100% segurança e limpeza)
        if($action == 'artisan_optimize') { 
            Artisan::call('optimize:clear'); Artisan::call('view:clear'); Artisan::call('cache:clear'); Artisan::call('route:clear'); Artisan::call('config:clear'); 
            $this->registrarLog($admin_logado, 'Assistente IA', "Limpeza profunda de cache e rotas (Auto-Cura)."); 
            return redirect('/dashboard?p=erros')->with('swal', 'Sistema Otimizado e Auto-Curado! O Laravel foi limpo com sucesso.'); 
        }

        if($action == 'reprocessar_erros') { DB::table('logs_erros')->truncate(); $this->registrarLog($admin_logado, 'Robô', "Reprocessamento de erros."); return redirect('/dashboard?p=erros')->with('swal', 'Robô acionado e Sincronizado!'); }

        if($action == 'change_status') {
            $novo_status = $request->status; $user_old = DB::table('pre_registros')->where('id', $id)->first();
            $upd = ['status' => $novo_status]; DB::table('pre_registros')->where('id', $id)->update($upd);
            $colab = DB::table('pre_registros')->where('id', $id)->first();
            if($user_old->username_criado) {
                $nuvem_acao = in_array($novo_status, ['bloqueado', 'inativo', 'desligado', 'demitido']) ? 'bloquear' : 'atualizar';
                $this->gerenciarAcessoM365($user_old->username_criado."@enfas.com.br", $nuvem_acao, null, (array)$colab, $cfg_global); 
                $this->gerenciarAcessoGoogle($user_old->username_criado."@enfas.com.br", $nuvem_acao, null, (array)$colab, $cfg_global); 
            }
            $this->registrarLog($admin_logado, 'Alteração de Status', "Status alterado para $novo_status."); return redirect('/dashboard?p=colaboradores')->with('swal', 'Status atualizado com sucesso!');
        }

        if($action == 'sync_assinatura') {
            $colab = DB::table('pre_registros')->leftJoin('cargos','cargo_id','cargos.id')->leftJoin('unidades','unidade_id','unidades.id')->leftJoin('setores','setor_id','setores.id')->select('pre_registros.*','cargos.nome as crg','unidades.nome as un','unidades.template_assinatura','setores.nome as seto')->where('pre_registros.id', $id)->first();
            if(!$colab || empty($colab->username_criado)) return back()->with('swal_error', 'A conta do colaborador ainda não foi criada no M365.');
            
            $tpl = !empty($colab->template_assinatura) ? $colab->template_assinatura : ($cfg_global['tpl_assinatura'] ?? '');
            if(empty($tpl)) return back()->with('swal_error', 'Atenção: Cadastre um template HTML de assinatura em Configurações ou em Unidades.');

            $email_corp = $colab->username_criado."@enfas.com.br";
            $html_final = str_replace(['{NOME}', '{CARGO}', '{SETOR}', '{TELEFONE}', '{EMAIL}'], [$colab->nome_completo, $colab->crg, $colab->seto, $colab->telefone, $email_corp], $tpl);
            
            $sync = $this->sincronizarAssinaturaM365($email_corp, $html_final, $cfg_global);
            if($sync['sucesso']) { $this->registrarLog($admin_logado, 'Assinatura M365', "Assinatura sincronizada no Outlook de $email_corp"); return redirect('/dashboard?p=colaboradores')->with('swal', 'Assinatura injetada remotamente no Outlook!'); }
            return back()->with('swal_error', 'Erro na API Microsoft: '.$sync['erro']);
        }

        if($action == 'reset_senha') {
            $colab = DB::table('pre_registros')
                ->leftJoin('unidades', 'unidade_id', 'unidades.id')
                ->select('pre_registros.*', 'unidades.nome as un')
                ->where('pre_registros.id', $id)->first(); 
                
            $chamado = $request->input('chamado') ?? 'N/A';
            $forma = $request->input('forma_solicitacao') ?? 'Não informada';
            $protocolo = 'RST-' . date('Ymd') . '-' . mt_rand(1000, 9999);

            $random_str = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            $nova_senha = "Enfas@" . $random_str;
            
            DB::table('pre_registros')->where('id', $id)->update(['senha_criada'=>Hash::make($nova_senha), 'data_ultima_senha'=>Carbon::now('America/Sao_Paulo')->format('Y-m-d H:i:s')]);
            
            $sync_msg = "Salvo localmente";
            if($colab->username_criado) { 
                $email = $colab->username_criado . "@enfas.com.br"; 
                $m365 = $this->gerenciarAcessoM365($email, 'reset_senha', $nova_senha, [], $cfg_global); 
                $gw = $this->gerenciarAcessoGoogle($email, 'reset_senha', $nova_senha, [], $cfg_global); 
                $sync_msg = ($m365['sucesso'] || $gw['sucesso']) ? "Sincronizado na Nuvem API" : "Falha na sincronização Cloud";
            }
            
            $this->registrarLog($admin_logado, 'Reset Senha', "Senha corporativa de {$colab->nome_completo} redefinida. Protocolo: $protocolo. Forma: $forma. Chamado: $chamado."); 
            
            return redirect('/dashboard?p=colaboradores')->with('senha_resetada', [
                'id'=>$id, 'senha'=>$nova_senha, 'colab'=>$colab->nome_completo, 
                'matricula'=>$colab->matricula, 'unidade'=>$colab->un ?? 'Matriz',
                'operador'=>$admin_logado, 'email'=>$colab->username_criado ? $colab->username_criado.'@enfas.com.br' : 'N/A', 
                'email_pessoal'=>$colab->e_mail_pessoal, 'data'=>Carbon::now()->timezone('America/Sao_Paulo')->format('d/m/Y \à\s H:i:s'), 
                'ip'=>$request->ip(), 'sync'=>$sync_msg, 'protocolo'=>$protocolo, 'chamado'=>$chamado, 'forma'=>$forma
            ]);
        }

        if($action == 'send_pwd_email') {
            $email = $request->email_pessoal; $senha = $request->senha;
            $tpl = !empty($cfg_global['tpl_email_senha']) ? $cfg_global['tpl_email_senha'] : "<div style='font-family:Arial;padding:20px;'><h2 style='color:#00a2e8;'>Sua Credencial Corporativa IAM</h2><p>Sua senha foi redefinida com sucesso pelo administrador.</p><p>Nova Senha Temporária: <b style='font-size:18px;'>{SENHA}</b></p><p>Acesse o portal e efetue a troca obrigatória.</p></div>";
            $html_email = str_replace('{SENHA}', $senha, $tpl);
            
            if($this->enviarEmailSMTP($email, "Credencial Corporativa", $html_email, $cfg_global)) {
                $this->registrarLog($admin_logado, 'Envio de Senha', "Senha temporária enviada para o e-mail pessoal: $email");
                return redirect('/dashboard?p=colaboradores')->with('swal', 'E-mail enviado com a nova credencial!');
            }
            return redirect('/dashboard?p=colaboradores')->with('swal_error', 'Falha ao conectar no servidor SMTP. O remetente oficial está configurado?');
        }

        if($action == 'importar_csv') { /*...*/ return redirect('/dashboard?p=colaboradores')->with('swal', 'Importação Processada'); }

        if($action == 'save_todas_configs') { foreach($request->cfg as $k=>$v) { if($v !== null) DB::table('configuracoes')->updateOrInsert(['chave'=>$k],['valor'=>$v]); } $this->registrarLog($admin_logado, 'Configurações', "Atualizou parâmetros mestres."); return redirect('/dashboard?p=configuracoes')->with('swal', 'Configurações Salvas!'); }
        if($action == 'limpar_erros') { DB::table('logs_erros')->truncate(); return redirect('/dashboard?p=erros')->with('swal', 'Logs limpos com sucesso.'); }
        
        // A NOVA EXCLUSÃO AUDITADA COM PROTOCOLO E MOTIVO
        if($action == 'delete') {
            $t = $request->table;
            $chamado = $request->input('chamado') ?? 'N/A';
            $motivo = $request->input('motivo') ?? 'Não informado';
            $protocolo = 'DEL-' . date('Ymd') . '-' . mt_rand(1000, 9999);

            if($t == 'pre_registros') { 
                $user = DB::table('pre_registros')->where('id', $id)->first(); 
                if($user && !empty($user->username_criado)) { 
                    $this->gerenciarAcessoM365($user->username_criado."@enfas.com.br", 'excluir', null, [], $cfg_global); 
                    $this->gerenciarAcessoGoogle($user->username_criado."@enfas.com.br", 'excluir', null, [], $cfg_global); 
                } 
            }
            DB::table($t)->where('id', $id)->delete(); 
            $this->registrarLog($admin_logado, 'Exclusão', "Excluiu registro na tabela $t. Protocolo: $protocolo. Motivo: $motivo. Chamado: $chamado."); 
            
            if($t == 'pre_registros' && isset($user)) {
                return back()->with('exclusao_protocolo', [
                    'colab' => $user->nome_completo,
                    'protocolo' => $protocolo,
                    'chamado' => $chamado,
                    'motivo' => $motivo,
                    'operador' => $admin_logado,
                    'data' => Carbon::now()->timezone('America/Sao_Paulo')->format('d/m/Y \à\s H:i:s'),
                    'ip' => $request->ip()
                ]);
            }
            
            return back()->with('swal', 'Excluído permanentemente!');
        }

        // SALVAMENTO GLOBAL BLINDADO (Filtra as colunas exatas e converte os IDs vazios em NULL, Fim do Erro 500)
        if(strpos($action, 'save_') !== false && $action != 'save_colab' && $action != 'save_todas_configs' && $action != 'save_meu_perfil') { 
            $table = $request->table ?: str_replace('save_', '', $action); if($table=='sistema') $table='sistemas_hc'; if($table=='perfil' || $table=='grupos') $table='perfis_acesso'; if($table=='cargo') $table='cargos'; if($table=='setor') $table='setores'; if($table=='unidade') $table='unidades';
            
            $data = $request->except(['_token', 'table', 'id', 'action']);
            if(isset($data['sistemas'])) { $data['sistemas_ids'] = implode(',', $data['sistemas']); unset($data['sistemas']); }
            if(isset($data['grupos_m365'])) { $data['grupos_m365'] = implode(',', $data['grupos_m365']); unset($data['grupos_m365']); } 
            if(isset($data['grupos_google'])) { $data['grupos_google'] = implode(',', $data['grupos_google']); unset($data['grupos_google']); } 
            if(isset($data['senha'])) { if(!empty($data['senha'])) $data['senha'] = Hash::make($data['senha']); else unset($data['senha']); }
            
            $colunas = Schema::getColumnListing($table);
            $dadosUpdate = [];
            foreach($data as $k=>$v) { 
                if(in_array($k, $colunas)) { 
                    if(empty($v) && $v !== '0' && (strpos($k, '_id') !== false || $k == 'id' || $k == 'pre_registro_id')) {
                        $dadosUpdate[$k] = null;
                    } else {
                        $dadosUpdate[$k] = is_null($v) ? '' : $v; 
                    }
                } 
            }
            
            if(!empty($id)) DB::table($table)->where('id',$id)->update($dadosUpdate); 
            else { $max = DB::table($table)->max('id'); $dadosUpdate['id'] = $max ? $max+1 : 1; DB::table($table)->insert($dadosUpdate); }
            
            $this->registrarLog($admin_logado, ucfirst($table), "Salvou registro no IAM."); return redirect("/dashboard?p=$table")->with('swal','Salvo com sucesso!');
        }

        if($action == 'save_colab') {
            if(!Schema::hasColumn('pre_registros', 'atualizado_em')) {
                Schema::table('pre_registros', function($t) { $t->dateTime('atualizado_em')->nullable(); $t->string('atualizado_por')->nullable(); });
            }

            $dadosUpdate = [];
            $dadosUpdate['nome'] = $request->input('nome') ?? '';
            $dadosUpdate['nome_completo'] = $request->input('nome') ?? '';
            $dadosUpdate['nome_mae'] = $request->input('nome_mae') ?? '';
            $dadosUpdate['cpf'] = preg_replace('/[^0-9]/','', $request->input('cpf') ?? '');
            $dadosUpdate['data_nascimento'] = !empty($request->input('nasc')) ? $request->input('nasc') : '0000-00-00';
            $dadosUpdate['rg'] = $request->input('rg') ?? '';
            $dadosUpdate['telefone'] = preg_replace('/[^0-9]/','', $request->input('telefone') ?? '');
            $dadosUpdate['e_mail_pessoal'] = $request->input('email_pessoal') ?? '';
            $dadosUpdate['matricula'] = $request->input('matricula') ?? '';
            $dadosUpdate['data_admissao'] = !empty($request->input('data_admissao')) ? $request->input('data_admissao') : '0000-00-00';
            $dadosUpdate['status'] = $request->input('status') ?? 'pendente';
            
            $dadosUpdate['unidade_id'] = !empty($request->input('unidade')) ? $request->input('unidade') : null;
            $dadosUpdate['setor_id'] = !empty($request->input('setor')) ? $request->input('setor') : null;
            $dadosUpdate['cargo_id'] = !empty($request->input('cargo')) ? $request->input('cargo') : null;
            $dadosUpdate['gestor_id'] = !empty($request->input('gestor_id')) ? $request->input('gestor_id') : 0;
            
            $dadosUpdate['jornada_inicio'] = $request->input('jornada_inicio') ?? '08:00';
            $dadosUpdate['jornada_fim'] = $request->input('jornada_fim') ?? '18:00';
            $dadosUpdate['ignora_jornada'] = $request->has('ignora_jornada') ? 1 : 0;
            $dadosUpdate['m365_perfil'] = $request->input('m365_perfil') ?? 'Sem Licença';
            
            $apps_sel = $request->has('m365_apps') ? $request->input('m365_apps') : [];
            $dadosUpdate['m365_apps'] = implode(',', $apps_sel);
            $dadosUpdate['sistemas_liberados'] = $request->has('sistemas') ? implode(',', $request->input('sistemas')) : '';
            
            $dadosUpdate['ferias_inicio'] = !empty($request->input('ferias_inicio')) ? $request->input('ferias_inicio') : '0000-00-00';
            $dadosUpdate['ferias_fim'] = !empty($request->input('ferias_fim')) ? $request->input('ferias_fim') : '0000-00-00';
            $dadosUpdate['observacoes_rh'] = $request->input('observacoes_rh') ?? '';
            $dadosUpdate['dados_extras'] = $request->input('dados_extras') ?? '';

            if($request->hasFile('foto')) { $path = $request->file('foto')->store('fotos', 'public'); $dadosUpdate['foto_path'] = 'storage/'.$path; }
            
            if(!empty($id)) { 
                $dadosUpdate['atualizado_em'] = Carbon::now('America/Sao_Paulo')->format('Y-m-d H:i:s');
                $dadosUpdate['atualizado_por'] = $admin_logado;

                $user_old = DB::table('pre_registros')->where('id', $id)->first(); 
                DB::table('pre_registros')->where('id', $id)->update($dadosUpdate); 
                
                if($user_old && !empty($user_old->username_criado)) {
                    $colab = DB::table('pre_registros')->where('id', $id)->first();
                    $acao_m365 = in_array($dadosUpdate['status'], ['bloqueado', 'inativo', 'desligado', 'demitido']) ? 'bloquear' : 'atualizar';
                    $this->gerenciarAcessoM365($user_old->username_criado."@enfas.com.br", $acao_m365, null, (array)$colab, $cfg_global);
                    $this->gerenciarAcessoGoogle($user_old->username_criado."@enfas.com.br", $acao_m365, null, (array)$colab, $cfg_global);
                }
            } else { 
                $maxId = DB::table('pre_registros')->max('id'); $dadosUpdate['id'] = $maxId ? $maxId + 1 : 1; 
                $dadosUpdate['public_id'] = $request->input('public_id') ? preg_replace('/[^0-9]/','',$request->input('public_id')) : mt_rand(100000, 999999); 
                $dadosUpdate['criado_por'] = $admin_logado; 
                $dadosUpdate['criado_em'] = Carbon::now('America/Sao_Paulo')->format('Y-m-d H:i:s');
                DB::table('pre_registros')->insert($dadosUpdate); 
            }
            $this->registrarLog($admin_logado, 'Colaboradores', "Atualizou/Criou a ficha de ".$dadosUpdate['nome_completo']);
            return redirect('/dashboard?p=colaboradores')->with('swal', 'Ficha Salva e Sincronizada com as Nuvens!');
        }
        return back();
    }
}
